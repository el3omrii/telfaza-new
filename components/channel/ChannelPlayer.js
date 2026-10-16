'use client'
import { useState, useRef, useEffect, useCallback } from 'react';
import Image from 'next/image';
import { formatBytes, formatSpeed, storageUrl } from '@/lib';
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

const initialP2PStats = {
  p2pDownloadSpeed: 0,
  p2pUploadSpeed: 0,
  p2pDownloaded: 0,
  p2pUploaded: 0,
  httpDownloaded: 0,
};

function P2PStatRow({ label, speed, total }) {
  return (
    <div className="flex items-center justify-between gap-4 px-3 py-2 text-xs">
      <span className="text-white/65">{label}</span>
      <span className="text-right">
        <span className="block font-semibold text-white">{speed}</span>
        <span className="block text-xs text-white/50">{total} total</span>
      </span>
    </div>
  );
}

export default function ChannelPlayer({ channel }) {
  const [error, setError] = useState('');
  const [currentSourceIndex, setCurrentSourceIndex] = useState(0);
  const [mediaHeight, setMediaHeight] = useState('Auto');
  const [renditions, setRenditions] = useState([]);
  const [selectedRenditionId, setSelectedRenditionId] = useState('auto');
  const [p2pPeerCount, setP2pPeerCount] = useState(0);
  const [p2pStats, setP2pStats] = useState(initialP2PStats);
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
    let p2pStatsInterval = null;
    let p2pStatsListeners = [];
    let p2pEngine = null;
    const p2pTotals = { p2pDownloaded: 0, p2pUploaded: 0, httpDownloaded: 0 };
    let speedSamples = [];

    const loadSource = async () => {
      setP2pPeerCount(0);
      setP2pStats({ ...initialP2PStats });

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
          p2pEngine = createP2PEngine({
            shaka,
            player,
            channelId: channel.id,
          });
          p2pEngineRef.current = p2pEngine;

          const onChunkDownloaded = (bytesLength, downloadSource) => {
            if (downloadSource === 'p2p') p2pTotals.p2pDownloaded += bytesLength;
            if (downloadSource === 'http') p2pTotals.httpDownloaded += bytesLength;
          };
          const onChunkUploaded = (bytesLength) => {
            p2pTotals.p2pUploaded += bytesLength;
          };

          p2pEngine.addEventListener('onChunkDownloaded', onChunkDownloaded);
          p2pEngine.addEventListener('onChunkUploaded', onChunkUploaded);
          p2pStatsListeners = [
            ['onChunkDownloaded', onChunkDownloaded],
            ['onChunkUploaded', onChunkUploaded],
          ];

          const readConnectedPeerCount = () => {
            const mainLoader = p2pEngine.core?.mainStreamLoader;
            const secondaryLoader = p2pEngine.core?.secondaryStreamLoader;
            return (
              mainLoader?.p2pLoaders?.currentLoader?.connectedPeerCount ??
              secondaryLoader?.p2pLoaders?.currentLoader?.connectedPeerCount ??
              0
            );
          };

          const updateP2PStats = () => {
            const now = Date.now();
            speedSamples = [
              ...speedSamples.filter((sample) => sample.at >= now - 3000),
              { at: now, p2pDownloaded: p2pTotals.p2pDownloaded, p2pUploaded: p2pTotals.p2pUploaded },
            ];

            const firstSample = speedSamples[0];
            const lastSample = speedSamples[speedSamples.length - 1];
            const elapsedSeconds = (lastSample.at - firstSample.at) / 1000;
            const downloadSpeed = elapsedSeconds >= 1
              ? Math.max(0, (lastSample.p2pDownloaded - firstSample.p2pDownloaded) / elapsedSeconds)
              : 0;
            const uploadSpeed = elapsedSeconds >= 1
              ? Math.max(0, (lastSample.p2pUploaded - firstSample.p2pUploaded) / elapsedSeconds)
              : 0;

            setP2pPeerCount(readConnectedPeerCount());
            setP2pStats({
              p2pDownloadSpeed: downloadSpeed,
              p2pUploadSpeed: uploadSpeed,
              ...p2pTotals,
            });
          };

          updateP2PStats();
          p2pStatsInterval = setInterval(updateP2PStats, 500);
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
      if (p2pStatsInterval) clearInterval(p2pStatsInterval);
      for (const [eventName, listener] of p2pStatsListeners) {
        p2pEngine?.removeEventListener(eventName, listener);
      }
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
          {p2penabled && (
            <>
              <MediaChromeMenuButton
                id={`p2p-stats-button-${channel.id}`}
                invoketarget={`p2p-stats-menu-${channel.id}`}
                className="media-control"
                aria-label={`P2P network: ${p2pPeerCount} connected peers`}
                title={`${p2pPeerCount} connected peer${p2pPeerCount === 1 ? '' : 's'}`}
                notooltip
              >
                <span className="flex items-center gap-1 text-teal-400">
                  <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
                    <circle cx="12" cy="8" r="3" />
                    <path d="M5 20v-1a7 7 0 0 1 14 0v1M5 8a3 3 0 0 1 2-2.83M19 8a3 3 0 0 0-2-2.83" />
                  </svg>
                  <span className="text-xs font-semibold">{p2pPeerCount}</span>
                </span>
              </MediaChromeMenuButton>
              <MediaChromeMenu
                id={`p2p-stats-menu-${channel.id}`}
                hidden
                anchor={`p2p-stats-button-${channel.id}`}
                className="min-w-[180px] rounded-md p-2"
              >
                <div slot="header" className="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-teal-400">
                  P2P Network · {p2pPeerCount} peer{p2pPeerCount === 1 ? '' : 's'}
                </div>
                <P2PStatRow
                  label="P2P download"
                  speed={formatSpeed(p2pStats.p2pDownloadSpeed)}
                  total={formatBytes(p2pStats.p2pDownloaded)}
                />
                <P2PStatRow
                  label="P2P upload"
                  speed={formatSpeed(p2pStats.p2pUploadSpeed)}
                  total={formatBytes(p2pStats.p2pUploaded)}
                />
                <P2PStatRow
                  label="HTTP download"
                  speed="—"
                  total={formatBytes(p2pStats.httpDownloaded)}
                />
              </MediaChromeMenu>
            </>
          )}
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
