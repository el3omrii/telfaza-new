'use client'
import { useState, useRef, useEffect, useCallback } from 'react';
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
import { MediaRenditionMenu, MediaRenditionMenuButton } from 'media-chrome/react/menu';
import ShakaVideo from 'shaka-video-element/react';
import { ShakaP2PEngine } from 'p2p-media-loader-shaka';
import {
  attachAnalyticsTracking,
  buildShakaConfig,
  destroyP2PEngine,
  ensureP2PPluginsRegistered,
  setupSnrtUrlFix,
  shouldRetrySource,
} from '@/lib/shaka-helpers';

export default function ChannelPlayer({ channel }) {
  const [error, setError] = useState('');
  const [currentSourceIndex, setCurrentSourceIndex] = useState(0);
  const [mediaHeight, setMediaHeight] = useState('Auto');
  const [, setPlayEventSent] = useState(false);
  const { sources } = channel;
  const activeSource = sources[currentSourceIndex] ?? sources[0];
  const { link, p2penabled, drm, clearkeys } = activeSource;
  const videoRef = useRef(null);
  const currentSourceIndexRef = useRef(0);
  const p2pEngineRef = useRef(null);

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

  useEffect(() => {
    const video = videoRef.current;
    if (!video) return;

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

    const loadSource = () => {
      try {
        video.api?.unload?.();
      } catch (error) {
        console.warn('Failed to unload previous source', error);
      }

      destroyP2PEngine(p2pEngineRef);

      if (video.api) {
        try {
          video.api.resetConfiguration?.();
          video.api.configure(buildShakaConfig({ link, drm, clearkeys }));
          if(p2penabled) {
            ensureP2PPluginsRegistered();

            const shakaP2PEngine = new ShakaP2PEngine(
              {
                core: {
                  swarmId: `telfaza-live-${channel.id}-swarm`,
                  announceTrackers: ['wss://tracker.webtorrent.dev', 'wss://tracker.openwebtorrent.com'],
                },
              },
              window.shaka,
            );

            shakaP2PEngine.bindShakaPlayer(video.api);
            p2pEngineRef.current = shakaP2PEngine;
            shakaP2PEngine.addEventListener('onPeerConnect', (params) => {
              console.log('Peer connected:', params);
            });
            shakaP2PEngine.addEventListener('onSegmentLoaded', (details) => {
              console.log('Segment Loaded:', details);
            });
          }

          if (link.includes('/snrt/')) {
            setupSnrtUrlFix(video.api);
          }
        } catch (error) {
          console.error('Failed to configure Shaka for source', error);
          setError('DRM configuration failed');
        }
      }

      const cleanupAnalytics = attachAnalyticsTracking({
        video,
        api: video.api,
        channel,
        link,
        setPlayEventSent,
      });

      video.addEventListener('error', handlePlayerError);
      setPlayEventSent(false);
      video.src = link;
      handleResize();

      return () => {
        cleanupAnalytics();
        video.removeEventListener('error', handlePlayerError);
      };
    };

    const cleanupSource = loadSource();
    video.addEventListener('resize', handleResize);

    return () => {
      video.removeEventListener('resize', handleResize);
      video.removeEventListener('error', handlePlayerError);
      cleanupSource?.();
      destroyP2PEngine(p2pEngineRef);

      try {
        video.api?.unload?.();
      } catch (error) {
        console.error('Failed to cleanup channel player', error);
      }

      setPlayEventSent(false);
    };
  }, [channel, currentSourceIndex, link, p2penabled, drm, clearkeys, switchToNextSource]);

  return (
    <div className='w-full h-full aspect-video'>
      <MediaController className="w-full h-full bg-black" autohide="5">
        <ShakaVideo
          ref={videoRef}
          slot="media"
          suppressHydrationWarning
          poster={storageUrl(channel.image)}
          className="object-cover bg-black"
        />
        <div slot="middle-chrome" className="center-overlay">
          <MediaPlayButton className="big-play-btn"></MediaPlayButton>
          <MediaLoadingIndicator></MediaLoadingIndicator>
        </div>
        <MediaControlBar className="bg-black/75 p-2 md:p-1 m-4 rounded-full">
          <MediaPlayButton className="media-control"></MediaPlayButton>
          <MediaLiveButton className="media-control scale-80 sm:scale-100"></MediaLiveButton>
          <MediaMuteButton className="media-control"></MediaMuteButton>
          <MediaVolumeRange className="media-control hidden md:block"></MediaVolumeRange>
          <div className="flex items-center justify-center gap-2 truncate flex-grow">
            <img src={storageUrl(channel.logo)} alt={channel.name} className="w-6 md:w-12 max-h-[50px] object-fit rounded-xl" />
            <span className="truncate text-xs sm:text-sm text-muted">{channel.name}</span>
          </div>
          <MediaRenditionMenuButton className="media-control">
            <span slot="icon" className="text-[#58BEC9] border border-[#58BEC9] py-1 px-2 rounded-md bg-green-950/50 text-xs sm:text-base">{mediaHeight}</span>
          </MediaRenditionMenuButton>
          <MediaRenditionMenu hidden anchor="auto"></MediaRenditionMenu>
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
