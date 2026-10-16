'use client'
import { useState, useRef, useEffect, useCallback } from 'react';
import Image from 'next/image';
import { storageUrl } from '@/lib';
import {
  MediaController,
  MediaControlBar,
  MediaLoadingIndicator,
  MediaLiveButton,
  MediaMuteButton,
  MediaVolumeRange,
  MediaPlayButton,
  MediaFullscreenButton,
} from 'media-chrome/react';
import {
  MediaChromeMenu,
  MediaChromeMenuButton,
} from 'media-chrome/react/menu';
import { configureShakaReferrerPolicy } from '@/lib/shaka-referrer-policy';
import shaka from 'shaka-player';
import {
  attachAnalyticsTracking,
  buildShakaConfig,
  createP2PEngine,
  destroyP2PEngine,
  destroyShakaPlayer,
  ensureP2PPluginsRegistered,
  getUniqueRenditions,
  setupSnrtUrlFix,
  shouldRetrySource,
  resolveStreamUrl
} from '@/lib/shaka-helpers';

export default function ChannelPlayer({ channel }) {
  const [error, setError] = useState('');
  const [currentSourceIndex, setCurrentSourceIndex] = useState(0);
  const [mediaHeight, setMediaHeight] = useState('Auto');
  const [renditions, setRenditions] = useState([]);
  const [selectedRenditionId, setSelectedRenditionId] = useState('auto');
  const [, setPlayEventSent] = useState(false);
  const { sources } = channel;
  const activeSource = sources[currentSourceIndex] ?? sources[0];
  const { link, p2penabled, drm, clearkeys } = activeSource;
  const channelLogo = storageUrl(channel.logo);
  const [resolvedSource, setResolvedSource] = useState(null);
  const resolvedLink = resolvedSource?.link === link ? resolvedSource.url : null;

  // Turn /local/ links into backend-signed URLs before Shaka ever sees them.
  // Non-/local/ sources pass through untouched.
  useEffect(() => {
    let cancelled = false;
    resolveStreamUrl(link)
      .then((url) => { if (!cancelled) setResolvedSource({ link, url }); })
      .catch((err) => {
        console.error('Failed to sign stream URL, falling back to raw link', err);
        if (!cancelled) setResolvedSource({ link, url: link });
      });
    return () => { cancelled = true; };
  }, [link]);

  const policy = link.includes('intigral-ott.net') || link.includes('starzplayarabia.com') || link.includes('playco.com')
    ? 'no-referrer'
    : 'no-referrer-when-downgrade';
  const videoRef = useRef(null);
  const playerRef = useRef(null);
  const playerAttachPromiseRef = useRef(null);
  const p2pEngineRef = useRef(null);
  const manualRenditionRef = useRef(false);
  const currentSourceIndexRef = useRef(0);

  useEffect(() => {
    currentSourceIndexRef.current = currentSourceIndex;
  }, [currentSourceIndex]);

  const switchToNextSource = useCallback(() => {
    const nextIndex = currentSourceIndexRef.current + 1;

    if (nextIndex >= sources.length) {
      console.error('All available stream sources have failed or are geo-blocked.');
      return;
    }

    setCurrentSourceIndex(nextIndex);
  }, [sources]);

  const selectRendition = useCallback((renditionId) => {
    const player = playerRef.current;
    if (!player) return;

    if (renditionId === 'auto') {
      manualRenditionRef.current = false;
      player.configure({ abr: { enabled: true } });
      setSelectedRenditionId('auto');
      return;
    }

    const track = player.getVariantTracks().find((variant) => String(variant.id) === renditionId);
    if (!track) return;

    manualRenditionRef.current = true;
    player.configure({ abr: { enabled: false } });
    player.selectVariantTrack(track, true);
    setSelectedRenditionId(renditionId);
  }, []);

  useEffect(() => {
    const video = videoRef.current;
    if (!video || !resolvedLink) return;

    let cancelled = false;
    let player = playerRef.current;

    const handlePlayerError = (event) => {
      const errorDetail = event?.detail ?? event;

      if (!cancelled && shouldRetrySource(errorDetail)) {
        console.warn(`Source failed with error code ${errorDetail.code}. Trying next source...`);
        switchToNextSource();
      }
    };

    let cleanupSnrtUrlFix = () => {};
    let cleanupAnalytics = () => {};
    let cleanupReferrerPolicy = () => {};
    let handleTracksChanged;
    let handleVariantChanged;

    const loadSource = async () => {
      if (p2penabled) {
        ensureP2PPluginsRegistered(shaka);
      }

      // Create the player once, attach to the plain <video>.
      if (!player) {
        player = new shaka.Player();
        playerRef.current = player;
        playerAttachPromiseRef.current = player.attach(video);
      }
      await playerAttachPromiseRef.current;
      if (cancelled) return;

      player.addEventListener('error', handlePlayerError);

      try {
        player.resetConfiguration();
        player.configure(buildShakaConfig({ link, drm, clearkeys }));
        cleanupReferrerPolicy = configureShakaReferrerPolicy(player, policy);

        setRenditions([]);
        setSelectedRenditionId('auto');
        manualRenditionRef.current = false;

        handleTracksChanged = () => {
          setRenditions(getUniqueRenditions(player.getVariantTracks()));
        };
        player.addEventListener('trackschanged', handleTracksChanged);

        handleVariantChanged = (event) => {
          if (event.newTrack?.height) {
            setMediaHeight(`${event.newTrack.height}P`);
          }
        };
        player.addEventListener('variantchanged', handleVariantChanged);

        if (link.includes('/snrt/')) {
          cleanupSnrtUrlFix = setupSnrtUrlFix(player);
        };

        if (p2penabled) {
          p2pEngineRef.current = createP2PEngine({
            shaka,
            player,
            channelId: channel.id,
          });
        }
      } catch (error) {
        console.error('Failed to configure Shaka for source', error);
        setError('Playback setup failed');
        return;
      }

      cleanupAnalytics = attachAnalyticsTracking({
        video,
        api: player,
        channel,
        link,
        setPlayEventSent,
      });

      setPlayEventSent(false);

      // Shaka's load() unloads any previous content internally.
      try {
        if (cancelled) return;
        await player.load(resolvedLink);
      } catch (error) {
        if (cancelled) return;
        console.error('Shaka load failed', error);
        handlePlayerError(error);
      }
    };

    loadSource().catch((error) => {
      if (!cancelled) {
        console.error('Failed to initialize Shaka for source', error);
        setError('Playback initialization failed');
      }
    });

    return () => {
      cancelled = true;
      cleanupSnrtUrlFix();
      cleanupAnalytics();
      cleanupReferrerPolicy();
      destroyP2PEngine(p2pEngineRef);
      player?.removeEventListener('error', handlePlayerError);
      if (handleTracksChanged) player?.removeEventListener('trackschanged', handleTracksChanged);
      if (handleVariantChanged) player?.removeEventListener('variantchanged', handleVariantChanged);
    };
  }, [channel, currentSourceIndex, link, resolvedLink, policy, p2penabled, drm, clearkeys, switchToNextSource]);

  // Destroy the player on unmount.
  useEffect(() => {
    const video = videoRef.current;

    return () => {
      const player = playerRef.current;
      playerRef.current = null;
      playerAttachPromiseRef.current = null;
      destroyShakaPlayer(player, video).catch((error) => {
        console.error('Failed to destroy Shaka player', error);
      });
    };
  }, []);

  return (
    <div className='w-full h-full aspect-video'>
      <MediaController className="w-full h-full bg-black" autohide="5">
        <video
          ref={videoRef}
          slot="media"
          suppressHydrationWarning
          poster={storageUrl(channel.image)}
          className="object-cover bg-black w-full h-full"
          referrerPolicy={policy}
          playsInline
        />
        <div slot="middle-chrome" className="center-overlay">
          <MediaPlayButton className="big-play-btn"></MediaPlayButton>
          <MediaLoadingIndicator></MediaLoadingIndicator>
        </div>
        <MediaControlBar className="flex items-center bg-black/75 p-1 md:p-2 m-4 rounded-full">
          <MediaPlayButton className="media-control"></MediaPlayButton>
          <MediaLiveButton className="media-control scale-80 sm:scale-100"></MediaLiveButton>
          <MediaMuteButton className="media-control"></MediaMuteButton>
          <MediaVolumeRange className="media-control hidden md:block"></MediaVolumeRange>
          <div className="flex items-center justify-center gap-2 truncate flex-grow">
            {channelLogo && (
              <Image
                src={channelLogo}
                alt={channel.name}
                width={48}
                height={48}
                className="h-6 w-6 max-h-[50px] rounded-xl object-contain md:h-12 md:w-12"
                sizes="48px"
              />
            )}
            <span className="truncate text-xs sm:text-sm text-muted">{channel.name}</span>
          </div>
          <MediaChromeMenuButton
            id={`quality-button-${channel.id}`}
            invoketarget={`quality-menu-${channel.id}`}
            className="media-control"
            aria-label="Quality"
            notooltip
          >
            <span className="text-teal-400 border border-teal-400 py-1 px-2 rounded-md bg-green-950/50 text-xs sm:text-base">{mediaHeight}</span>
          </MediaChromeMenuButton>
          <MediaChromeMenu
            id={`quality-menu-${channel.id}`}
            hidden
            anchor={`quality-button-${channel.id}`}
          >
            <div
              role="menuitemradio"
              aria-checked={selectedRenditionId === 'auto'}
              onClickCapture={() => selectRendition('auto')}
              className={`cursor-pointer px-6 py-2 text-sm hover:bg-white/10 border-b border-white/10 ${selectedRenditionId === 'auto' ? 'text-teal-400' : 'text-white'}`}
            >
              Auto{selectedRenditionId === 'auto' ? ' ✓' : ''}
            </div>
            {renditions.map((rendition) => (
              <div
                key={rendition.id}
                role="menuitemradio"
                aria-checked={selectedRenditionId === rendition.id}
                onClickCapture={() => selectRendition(rendition.id)}
                className={`cursor-pointer px-6 py-2 text-sm hover:bg-white/10 ${selectedRenditionId === rendition.id ? 'text-teal-400 font-bold' : 'text-white'}`}
              >
                {rendition.height}p{selectedRenditionId === rendition.id ? ' ✓' : ''}
              </div>
            ))}
          </MediaChromeMenu>
          <MediaFullscreenButton className="media-control"></MediaFullscreenButton>
        </MediaControlBar>
      </MediaController>

      {error && (
        <div className="absolute inset-0 flex items-center justify-center bg-black/80 text-red-400 p-4 text-center">
          {error}
        </div>
      )}
    </div>
  );
}
