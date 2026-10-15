'use client'
import { useState, useRef, useEffect, useCallback } from 'react';
import { formatBytes, formatSpeed, storageUrl } from '@/lib';
import { configureShakaReferrerPolicy } from '@/lib/shaka-referrer-policy';
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
import shaka from 'shaka-player';
import { ShakaP2PEngine } from 'p2p-media-loader-shaka';
import {
  attachAnalyticsTracking,
  buildShakaConfig,
  destroyP2PEngine,
  ensureP2PPluginsRegistered,
  setupSnrtUrlFix,
  shouldRetrySource,
  resolveStreamUrl
} from '@/lib/shaka-helpers';

// P2P plugin registration expects window.shaka — provide the direct import.
if (typeof window !== 'undefined' && !window.shaka) {
  window.shaka = shaka;
}

/* ─── P2P stats menu icons & stat row ────────────────────────────────────── */
const DownloadArrowIcon = () => (
  <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor"
    strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
    <polyline points="7 10 12 15 17 10" />
    <line x1="12" y1="15" x2="12" y2="3" />
  </svg>
);

const UploadArrowIcon = () => (
  <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor"
    strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
    <polyline points="17 8 12 3 7 8" />
    <line x1="12" y1="3" x2="12" y2="15" />
  </svg>
);

const HttpIcon = () => (
  <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor"
    strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <circle cx="12" cy="12" r="10" />
    <path d="M2 12h20" />
    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
  </svg>
);

const P2pStatRow = ({ icon, label, value, detail }) => (
  <div className="flex w-full items-center gap-2 rounded-md px-2 py-1.5">
    <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-[#58BEC9]/40 bg-[#58BEC9]/10 text-[#58BEC9]">
      {icon}
    </span>
    <span className="flex min-w-0 flex-1 flex-col leading-tight">
      <span className="truncate text-[10px] font-semibold uppercase tracking-wider text-white/60">{label}</span>
      <span className="truncate text-sm font-bold text-white">{value}</span>
    </span>
    <span className="shrink-0 text-[10px] text-white/50">{detail}</span>
  </div>
);

export default function ChannelPlayer({ channel }) {
  const [error, setError] = useState('');
  const [currentSourceIndex, setCurrentSourceIndex] = useState(0);
  const [mediaHeight, setMediaHeight] = useState('Auto');
  const [p2pPeerCount, setP2pPeerCount] = useState(0);
  const [p2pStats, setP2pStats] = useState({
    p2pDownloadSpeed: 0,
    p2pUploadSpeed: 0,
    p2pDownloaded: 0,
    p2pUploaded: 0,
    httpDownloaded: 0,
  });
  const [, setPlayEventSent] = useState(false);
  const { sources } = channel;
  const activeSource = sources[currentSourceIndex] ?? sources[0];
  const { link, p2penabled, drm, clearkeys } = activeSource;
  const [resolvedLink, setResolvedLink] = useState(null);
  // Renditions for the quality menu (populated from Shaka variant tracks).
  const [renditions, setRenditions] = useState([]);
  const [selectedRenditionId, setSelectedRenditionId] = useState('auto');

  // Turn /local/ links into backend-signed URLs before Shaka ever sees them.
  // Non-/local/ sources pass through untouched.
  useEffect(() => {
    let cancelled = false;
    setResolvedLink(null);
    resolveStreamUrl(link)
      .then((url) => { if (!cancelled) setResolvedLink(url); })
      .catch((err) => {
        console.error('Failed to sign stream URL, falling back to raw link', err);
        if (!cancelled) setResolvedLink(link);
      });
    return () => { cancelled = true; };
  }, [link]);

  const policy = link.includes('intigral-ott.net') || link.includes('starzplayarabia.com') || link.includes('playco.com')
    ? 'no-referrer'
    : 'no-referrer-when-downgrade';
  const videoRef = useRef(null);
  const playerRef = useRef(null);
  const manualRenditionRef = useRef(false);
  const currentSourceIndexRef = useRef(0);
  const p2pEngineRef = useRef(null);
  const peerCountIntervalRef = useRef(null);
  const statsIntervalRef = useRef(null);
  const p2pStatsRef = useRef({ p2pDownloaded: 0, p2pUploaded: 0, httpDownloaded: 0 });
  const speedSamplesRef = useRef([]);
  const p2pStatsListenersRef = useRef([]);

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

  // Stop polling + engine event listeners, then reset every counter so a source
  // switch (or unmount) starts from a clean slate.
  const stopP2PMonitoring = useCallback(() => {
    if (peerCountIntervalRef.current) {
      clearInterval(peerCountIntervalRef.current);
      peerCountIntervalRef.current = null;
    }

    if (statsIntervalRef.current) {
      clearInterval(statsIntervalRef.current);
      statsIntervalRef.current = null;
    }

    const currentEngine = p2pEngineRef.current;
    if (currentEngine) {
      for (const { eventName, listener } of p2pStatsListenersRef.current) {
        try {
          currentEngine.removeEventListener(eventName, listener);
        } catch (error) {
          console.warn('Failed to remove P2P stats listener', error);
        }
      }
    }

    p2pStatsListenersRef.current = [];
    p2pStatsRef.current = { p2pDownloaded: 0, p2pUploaded: 0, httpDownloaded: 0 };
    speedSamplesRef.current = [];
    setP2pStats({
      p2pDownloadSpeed: 0,
      p2pUploadSpeed: 0,
      p2pDownloaded: 0,
      p2pUploaded: 0,
      httpDownloaded: 0,
    });
  }, []);

  // Manual rendition selection (replaces shaka-video-element's media-tracks).
  const selectRendition = useCallback((renditionId) => {
    const player = playerRef.current;
    if (!player) return;

    if (renditionId === 'auto') {
      manualRenditionRef.current = false;
      player.configure({ abr: { enabled: true } });
      setSelectedRenditionId('auto');
      return;
    }
    manualRenditionRef.current = true;

    const track = player.getVariantTracks().find((t) => String(t.id) === String(renditionId));
    if (track) {
      player.configure({ abr: { enabled: false } });
      player.selectVariantTrack(track, /* clearBuffer= */ true);
      setSelectedRenditionId(String(renditionId));
    }
  }, []);

  useEffect(() => {
    const video = videoRef.current;
    if (!video || !resolvedLink) return;

    let cancelled = false;
    let player = playerRef.current;

    const handlePlayerError = (event) => {
      const errorDetail = event?.detail ?? event;

      if (shouldRetrySource(errorDetail)) {
        console.warn(`Source failed with error code ${errorDetail.code}. Trying next source...`);
        switchToNextSource();
      }
    };

    const handleResize = () => {
      if (!video.videoWidth || !video.videoHeight) return;
      setMediaHeight(`${Math.min(video.videoWidth, video.videoHeight)}P`);
    };

    const loadSource = async () => {
      let cleanupReferrerPolicy = () => {};

      // Create the player once, attach to the plain <video>.
      if (!player) {
        player = new shaka.Player();
        playerRef.current = player;
        await player.attach(video);
        player.addEventListener('error', handlePlayerError);
        video.addEventListener('resize', handleResize);
      }

      stopP2PMonitoring();
      setP2pPeerCount(0);
      destroyP2PEngine(p2pEngineRef);
      setRenditions([]);
      setSelectedRenditionId('auto');
      manualRenditionRef.current = false;

      try {
        player.resetConfiguration();
        player.configure(buildShakaConfig({ link, drm, clearkeys }));
        cleanupReferrerPolicy = configureShakaReferrerPolicy(player, policy);

        const handleTracksChanged = () => {
          const tracks = player.getVariantTracks();
          // Populate the quality menu (dedupe by height, keep highest bandwidth).
          const byHeight = new Map();
          for (const t of tracks) {
            if (!t.height) continue;
            const prev = byHeight.get(t.height);
            if (!prev || (t.bandwidth ?? 0) > (prev.bandwidth ?? 0)) byHeight.set(t.height, t);
          }
          setRenditions(
            [...byHeight.values()]
              .sort((a, b) => b.height - a.height)
              .map((t) => ({ id: String(t.id), height: t.height }))
          );
          
          // Don't override ABR if the user manually picked a quality.
          if (manualRenditionRef.current) return;

          if (tracks.length <= 1) {
            console.log('Single-variant stream detected. Disabling ABR for ultra-fast startup.');
            player.configure({
              abr: { enabled: false },
              streaming: {
                rebufferingGoal: 2,
                bufferingGoal: 5
              }
            });
          } else {
            console.log('Multi-variant stream detected. Keeping ABR enabled.');
            player.configure({
              abr: { enabled: true }
            });
          }
        };

        player.removeEventListener('trackschanged', player._onTracksChangedCleanup);
        player.addEventListener('trackschanged', handleTracksChanged);
        player._onTracksChangedCleanup = handleTracksChanged;

        // Keep the quality button label in sync when ABR switches variants.
        const handleVariantChanged = (event) => {
          const newTrack = event?.newTrack;
          if (newTrack?.height) {
            setMediaHeight(`${newTrack.height}P`);
          }
        };
        player.removeEventListener('variantchanged', player._onVariantChangedCleanup);
        player.addEventListener('variantchanged', handleVariantChanged);
        player._onVariantChangedCleanup = handleVariantChanged;

        if (p2penabled) {
          ensureP2PPluginsRegistered();

          const shakaP2PEngine = new ShakaP2PEngine(
            {
              core: {
                swarmId: `telfaza-live-${channel.id}-swarm`,
                announceTrackers: ['wss://tracker.webtorrent.dev', 'wss://tracker.openwebtorrent.com'],
              },
            },
            shaka,
          );

          shakaP2PEngine.bindShakaPlayer(player);
          p2pEngineRef.current = shakaP2PEngine;

          // Track how many bytes come from / go to the swarm so the peers
          // menu can show live P2P download & upload stats.
          const chunkDownloadedListener = (bytesLength, downloadSource) => {
            if (downloadSource === 'p2p') {
              p2pStatsRef.current.p2pDownloaded += bytesLength;
            } else if (downloadSource === 'http') {
              p2pStatsRef.current.httpDownloaded += bytesLength;
            }
          };

          const chunkUploadedListener = (bytesLength) => {
            p2pStatsRef.current.p2pUploaded += bytesLength;
          };

          shakaP2PEngine.addEventListener('onChunkDownloaded', chunkDownloadedListener);
          shakaP2PEngine.addEventListener('onChunkUploaded', chunkUploadedListener);
          p2pStatsListenersRef.current = [
            { eventName: 'onChunkDownloaded', listener: chunkDownloadedListener },
            { eventName: 'onChunkUploaded', listener: chunkUploadedListener },
          ];

          // Poll the connected peer count from the engine's internal loader
          // (p2pLoaders lives on the core's stream loaders, not on core itself)
          const readConnectedPeerCount = () => {
            const mainLoader = shakaP2PEngine?.core?.mainStreamLoader;
            const secondaryLoader = shakaP2PEngine?.core?.secondaryStreamLoader;
            return (
              mainLoader?.p2pLoaders?.currentLoader?.connectedPeerCount ??
              secondaryLoader?.p2pLoaders?.currentLoader?.connectedPeerCount ??
              0
            );
          };

          // Sliding window of (timestamp, cumulative bytes) snapshots used to
          // compute download / upload speed over roughly the last 3 seconds.
          const pushSpeedSample = () => {
            const now = Date.now();
            speedSamplesRef.current = [
              ...speedSamplesRef.current.filter((sample) => sample.at >= now - 3000),
              {
                at: now,
                p2pDownloaded: p2pStatsRef.current.p2pDownloaded,
                p2pUploaded: p2pStatsRef.current.p2pUploaded,
              },
            ];

            const samples = speedSamplesRef.current;
            if (samples.length > 1) {
              const oldest = samples[0];
              const newest = samples[samples.length - 1];
              const elapsedSeconds = (newest.at - oldest.at) / 1000;

              if (elapsedSeconds >= 1) {
                setP2pStats({
                  p2pDownloadSpeed: Math.max(0, (newest.p2pDownloaded - oldest.p2pDownloaded) / elapsedSeconds),
                  p2pUploadSpeed: Math.max(0, (newest.p2pUploaded - oldest.p2pUploaded) / elapsedSeconds),
                  p2pDownloaded: p2pStatsRef.current.p2pDownloaded,
                  p2pUploaded: p2pStatsRef.current.p2pUploaded,
                  httpDownloaded: p2pStatsRef.current.httpDownloaded,
                });
              }
            }
          };

          peerCountIntervalRef.current = setInterval(() => {
            setP2pPeerCount(readConnectedPeerCount());
            pushSpeedSample();
          }, 500);

          setP2pPeerCount(readConnectedPeerCount());
          pushSpeedSample();

          shakaP2PEngine.addEventListener('onSegmentLoaded', (details) => {
            console.log('Segment Loaded:', details);
          });
        }
        if (link.includes('/snrt/')) {
          setupSnrtUrlFix(player);
        }
      } catch (error) {
        console.error('Failed to configure Shaka for source', error);
        setError('DRM configuration failed');
      }

      const cleanupAnalytics = attachAnalyticsTracking({
        video,
        api: player,
        channel,
        link,
        setPlayEventSent,
      });

      setPlayEventSent(false);

      // Shaka's load() unloads any previous content internally.
      try {
        await player.load(resolvedLink);
      } catch (error) {
        console.error('Shaka load failed', error);
        handlePlayerError(error);
      }
      handleResize();

      return () => {
        cleanupReferrerPolicy();
        cleanupAnalytics();
      };
    };

    let cleanupSource;
    loadSource().then((cleanup) => {
      if (cancelled) {
        cleanup?.();
      } else {
        cleanupSource = cleanup;
      }
    });

    return () => {
      cancelled = true;
      cleanupSource?.();
      stopP2PMonitoring();
      destroyP2PEngine(p2pEngineRef);
      setPlayEventSent(false);
    };
  }, [channel, currentSourceIndex, resolvedLink, policy, p2penabled, drm, clearkeys, switchToNextSource, stopP2PMonitoring]);

  // Destroy the player on unmount.
  useEffect(() => {
    return () => {
      const player = playerRef.current;
      playerRef.current = null;
      if (player) {
        player.destroy().catch(() => {});
      }
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
            <img src={storageUrl(channel.logo)} alt={channel.name} className="w-6 md:w-12 max-h-[50px] object-fit rounded-xl" />
            <span className="truncate text-xs sm:text-sm text-muted">{channel.name}</span>
          </div>
          {p2penabled && (
            <>
              <MediaChromeMenuButton
                id={`p2p-stats-button-${channel.id}`}
                invoketarget={`p2p-stats-menu-${channel.id}`}
                notooltip
                aria-label="Connected P2P peers"
                title={`${p2pPeerCount} connected P2P peer${p2pPeerCount === 1 ? '' : 's'}`}
                className="media-control p-1 rounded-md border border-[#58BEC9] bg-black/40 text-[#58BEC9] hover:bg-[#58BEC9]/10"
                style={{
                  '--media-button-icon-width': '18px',
                  '--media-button-icon-height': '18px',
                  '--media-text-color': '#58BEC9',
                }}
              >
                <svg className="h-6 w-6" viewBox="0 0 24 24" fill="none" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                  <path d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1" />
                  <circle cx="10" cy="7" r="3" />
                  <path d="M20 19v-1a4 4 0 0 0-3-3.87" />
                  <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <span className="absolute -right-1.5 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#58BEC9] px-1 text-[10px] font-bold text-black leading-none">
                  {p2pPeerCount}
                </span>
              </MediaChromeMenuButton>
              <MediaChromeMenu
                id={`p2p-stats-menu-${channel.id}`}
                hidden
                anchor={`p2p-stats-menu-${channel.id}`}
                className="min-w-[240px]"
                style={{
                  '--media-menu-background': 'rgba(10, 14, 22, 0.95)',
                  '--media-menu-border': '1px solid rgba(88, 190, 201, 0.35)',
                  '--media-menu-border-radius': '12px',
                }}
              >
                <div slot="header" className="flex w-full items-center justify-between gap-2 p-1">
                  <span className="truncate text-xs font-semibold uppercase tracking-wider text-[#58BEC9]">P2P Network</span>
                  <span className="flex shrink-0 items-center gap-1 rounded-full border border-[#58BEC9]/40 bg-[#58BEC9]/10 px-2 py-0.5 text-[10px] font-bold text-[#58BEC9]">
                    {p2pPeerCount} peer{p2pPeerCount === 1 ? '' : 's'}
                  </span>
                </div>
                <P2pStatRow
                  icon={<DownloadArrowIcon />}
                  label="Download"
                  value={formatSpeed(p2pStats.p2pDownloadSpeed)}
                  detail={`${formatBytes(p2pStats.p2pDownloaded)} total`}
                />
                <P2pStatRow
                  icon={<UploadArrowIcon />}
                  label="Upload"
                  value={formatSpeed(p2pStats.p2pUploadSpeed)}
                  detail={`${formatBytes(p2pStats.p2pUploaded)} total`}
                />
                <P2pStatRow
                  icon={<HttpIcon />}
                  label="HTTP"
                  value="–"
                  detail={`${formatBytes(p2pStats.httpDownloaded)} total`}
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
            {renditions.map((r) => (
              <div
                key={r.id}
                role="menuitemradio"
                aria-checked={selectedRenditionId === r.id}
                onClickCapture={() => selectRendition(r.id)}
                className={`cursor-pointer px-6 py-2 text-sm hover:bg-white/10 ${selectedRenditionId === r.id ? 'text-teal-400 font-bold' : 'text-white'}`}
              >
                {r.height}p{selectedRenditionId === r.id ? ' ✓' : ''}
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
