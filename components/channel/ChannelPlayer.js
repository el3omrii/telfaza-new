'use client';

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
  getUniqueRenditions,
  setupSnrtUrlFix,
  shouldRetrySource,
  resolveStreamUrl,
} from '@/lib/shaka-helpers';

function areRenditionsEqual(a, b) {
  if (a.length !== b.length) return false;

  return a.every((rendition, index) => {
    const next = b[index];
    return next && rendition.id === next.id && rendition.height === next.height;
  });
}

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

  const policy =
    link.includes('intigral-ott.net') ||
    link.includes('starzplayarabia.com') ||
    link.includes('playco.com') ||
    link.includes('edgenextcdn.net')
      ? 'no-referrer'
      : 'no-referrer-when-downgrade';

  const videoRef = useRef(null);
  const playerRef = useRef(null);
  const playerAttachPromiseRef = useRef(null);
  const p2pEngineRef = useRef(null);
  const currentSourceIndexRef = useRef(0);

  useEffect(() => {
    currentSourceIndexRef.current = currentSourceIndex;
  }, [currentSourceIndex]);

  useEffect(() => {
    let cancelled = false;

    resolveStreamUrl(link)
      .then((url) => {
        if (!cancelled) setResolvedSource({ link, url });
      })
      .catch((err) => {
        console.error('Failed to sign stream URL, falling back to raw link', err);
        if (!cancelled) setResolvedSource({ link, url: link });
      });

    return () => {
      cancelled = true;
    };
  }, [link]);

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
      player.configure({ abr: { enabled: true } });
      setSelectedRenditionId('auto');
      return;
    }

    const track = player.getVariantTracks().find((variant) => String(variant.id) === renditionId);
    if (!track) return;

    player.configure({ abr: { enabled: false } });
    player.selectVariantTrack(track, true);
    setSelectedRenditionId(renditionId);
  }, []);

  useEffect(() => {
    const video = videoRef.current;
    const controller = video?.closest('media-controller');
    const orientation = window.screen.orientation;

    if (!video || !controller || typeof orientation?.lock !== 'function') return;

    let active = true;
    let lockPending = false;
    let orientationLocked = false;

    const isMobile = () =>
      navigator.maxTouchPoints > 0 || window.matchMedia('(pointer: coarse)').matches;

    const isFullscreen = () => {
      const fullscreenElement = document.fullscreenElement || document.webkitFullscreenElement;

      return Boolean(
        (fullscreenElement &&
          (fullscreenElement === controller || controller.contains(fullscreenElement))) ||
          video.webkitDisplayingFullscreen ||
          video.webkitPresentationMode === 'fullscreen'
      );
    };

    const updateOrientationLock = () => {
      if (!isMobile()) return;

      if (isFullscreen()) {
        if (lockPending || orientationLocked) return;

        lockPending = true;
        orientation
          .lock('landscape')
          .then(() => {
            orientationLocked = true;
            if (!active || !isFullscreen()) {
              orientation.unlock?.();
              orientationLocked = false;
            }
          })
          .catch((error) => {
            console.warn('Unable to lock video fullscreen to landscape.', error);
          })
          .finally(() => {
            lockPending = false;
          });
      } else if (orientationLocked) {
        orientation.unlock?.();
        orientationLocked = false;
      }
    };

    document.addEventListener('fullscreenchange', updateOrientationLock);
    document.addEventListener('webkitfullscreenchange', updateOrientationLock);
    video.addEventListener('webkitbeginfullscreen', updateOrientationLock);
    video.addEventListener('webkitendfullscreen', updateOrientationLock);
    video.addEventListener('webkitpresentationmodechanged', updateOrientationLock);

    return () => {
      document.removeEventListener('fullscreenchange', updateOrientationLock);
      document.removeEventListener('webkitfullscreenchange', updateOrientationLock);
      video.removeEventListener('webkitbeginfullscreen', updateOrientationLock);
      video.removeEventListener('webkitendfullscreen', updateOrientationLock);
      video.removeEventListener('webkitpresentationmodechanged', updateOrientationLock);
      active = false;
      if (orientationLocked) orientation.unlock?.();
    };
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
      if (!player) {
        shaka.polyfill.installAll();
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

        handleTracksChanged = () => {
          const nextRenditions = getUniqueRenditions(player.getVariantTracks());
          setRenditions((previousRenditions) =>
            areRenditionsEqual(previousRenditions, nextRenditions)
              ? previousRenditions
              : nextRenditions
          );
        };
        player.addEventListener('trackschanged', handleTracksChanged);

        handleVariantChanged = (event) => {
          const nextHeight = event.newTrack?.height;
          if (!nextHeight) return;

          const nextLabel = `${nextHeight}P`;
          setMediaHeight((currentHeight) =>
            currentHeight === nextLabel ? currentHeight : nextLabel
          );
        };
        player.addEventListener('variantchanged', handleVariantChanged);

        if (link.includes('/snrt/')) {
          cleanupSnrtUrlFix = setupSnrtUrlFix(player);
        }

        if (p2penabled) {
          p2pEngineRef.current = await createP2PEngine({
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
    <div className="channel-player-frame w-full h-full aspect-video">
      <MediaController className="channel-player-controller w-full h-full bg-black" autohide="5">
        <video
          ref={videoRef}
          slot="media"
          suppressHydrationWarning
          poster={storageUrl(channel.image)}
          className="object-contain bg-black w-full h-full"
          referrerPolicy={policy}
          playsInline
        />

        <div slot="middle-chrome" className="center-overlay">
          <MediaPlayButton className="big-play-btn" />
          <MediaLoadingIndicator />
        </div>

        <MediaControlBar className="flex items-center bg-black/75 p-1 md:p-2 m-4 rounded-full">
          <MediaPlayButton className="media-control" />
          <MediaLiveButton className="media-control scale-80 sm:scale-100" />
          <MediaMuteButton className="media-control" />
          <MediaVolumeRange className="media-control hidden md:block" />

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
            <span className="text-teal-400 border border-teal-400 py-1 px-2 rounded-md bg-green-950/50 text-xs sm:text-base">
              {mediaHeight}
            </span>
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

          <MediaFullscreenButton className="media-control" />
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
