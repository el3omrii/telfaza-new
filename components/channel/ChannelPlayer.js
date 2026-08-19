'use client'
import { useState, useRef, useEffect } from 'react';
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

function setupSnrtUrlFix(shakaPlayer) {
  const RequestType = {
    MANIFEST: 0,
    SEGMENT: 1,
    LICENSE: 2
  }
	let capturedToken = null;
  const TOKEN_PARAM = 'token';

  // 1️⃣ ResponseFilter: Extract token from FINAL URL after redirect
  // Signature: (type, response, context)
  shakaPlayer.getNetworkingEngine().registerResponseFilter((type, response, context) => {
    const originalUri = response.originalUri || '';
    
    // 🔒 Only activate for /saudia_tv/ endpoint
    if (!originalUri.includes('/snrt/')) {
      return Promise.resolve();
    }
    if (type === RequestType.MANIFEST) {
      const finalUrl = response.uri; // ✅ Use response.uri, NOT request.uris
      if (!finalUrl) return;

      try {
        const url = new URL(finalUrl);
        const token = url.searchParams.get('token');
        const tokenPath = url.searchParams.get('token_path');
        const expires = url.searchParams.get('expires');
        const match = finalUrl.match(new RegExp(`[?&]${TOKEN_PARAM}=(.+)`));
        capturedToken = match ? match[1] : null;
      } catch (e) {
        console.log(e);
      }
      
      if (capturedToken) {
        console.log('✅ Token captured:', capturedToken);
        shakaPlayer._debugToken = capturedToken; // For console inspection
      }
    }
  });

  // 2️⃣ RequestFilter: Inject token into outgoing requests
  // Signature: (type, request, context) - this one DOES get request
  shakaPlayer.getNetworkingEngine().registerRequestFilter((type, request, context) => {
    if (!capturedToken || capturedToken.trim() === '') return;
    if (type === RequestType.LICENSE) return;
    if (type === RequestType.SEGMENT) return;

    console.log('✏️  RequestFilter:', { type, uris: request.uris });

    for (let i = 0; i < request.uris.length; i++) {
      if (request.uris[i].includes(`${TOKEN_PARAM}=`)) continue;

      try {
        const url = new URL(request.uris[i]);
        url.searchParams.set(TOKEN_PARAM, capturedToken);
        request.uris[i] = request.uris[i] + `?token=${capturedToken}`;
      } catch (e) {
        const sep = request.uris[i].includes('?') ? '&' : '?';
        request.uris[i] = `${request.uris[i]}${sep}${TOKEN_PARAM}=${capturedToken}`;
      }
    }
  });

}


export default function ChannelPlayer({ channel, fullViewport = false }) {
  const [error, setError] = useState('');
  const [currentSourceIndex, setCurrentSourceIndex] = useState(0);
  const [mediaHeight, setMediaHeight] = useState('');
  const [playEventSent, setPlayEventSent] = useState(false);
  const { sources } = channel;
  const activeSource = sources[currentSourceIndex] ?? sources[0];
  const { link, drm, clearkeys } = activeSource;
  const videoRef = useRef(null);
  const currentSourceIndexRef = useRef(0);

  useEffect(() => {
    currentSourceIndexRef.current = currentSourceIndex;
  }, [currentSourceIndex]);

  const switchToNextSource = () => {
    const nextIndex = currentSourceIndexRef.current + 1;
    if (nextIndex < sources.length) {
      setCurrentSourceIndex(nextIndex);
      return;
    }

    console.error('All available stream sources have failed or are geo-blocked.');
  };

  useEffect(() => {
    const video = videoRef.current;
    if (!video) return;

    const handlePlayerError = (event) => {
      const errorDetail = event?.detail ?? event;
      if (!errorDetail || typeof errorDetail.code === 'undefined') return;

      const isGeoBlocked = errorDetail.code === 1002 && errorDetail.data?.[1] === 403;
      const isRestricted = errorDetail.code === 4000;
      const isCritical = errorDetail.severity === 2;

      if (isGeoBlocked || isRestricted || isCritical) {
        console.warn(`Source failed with error code ${errorDetail.code}. Trying next source...`);
        switchToNextSource();
      }
    };

    const handleResize = () => {
      if (video) {
        setMediaHeight(`${Math.min(video.videoWidth, video.videoHeight)}P`);
      }
    };

    const loadSource = async () => {
      try {
        video.api?.unload?.();
      } catch (err) {
        console.warn('Failed to unload previous source', err);
      }

      if (video.api) {
        try {
          video.api.resetConfiguration?.();

          const clearKeys = drm && clearkeys ? clearkeys : {};
          const config = {
            manifest: {
              hls: {
                defaultAudioCodec: 'mp4a.40.2',
                ignoreManifestProgramDateTime: true,
                liveSegmentsDelay: 3
              },
              availabilityWindowOverride: 60
            },
            preferredAudioCodecs: ['mp4a.40.2'],
            drm: {
              clearKeys
            }
          };

          if (link.includes('/hls2/')) {
            config.manifest.retryParameters = { maxAttempts: 5, baseDelay: 2000 };
          }

          video.api.configure(config);

          if (link.includes('/snrt/')) {
            setupSnrtUrlFix(video.api);
          }
        } catch (err) {
          console.error('Failed to configure Shaka for source', err);
          setError('DRM configuration failed');
        }
      }

      // Setup analytics tracking for this source
      const setupAnalyticsTracking = () => {
        if (!video.api) return;

        // Track play event (only once per source)
        const handlePlay = () => {
          if (playEventSent) return;
          setPlayEventSent(true);

          if (typeof window !== 'undefined' && window.gtag) {
            window.gtag('event', 'video_play', {
              channel_name: channel.name || 'unknown',
              channel_id: channel.id || 'unknown',
              source_url: link || 'unknown',
              video_title: channel.name || 'unknown',
              event_category: 'video',
              event_label: 'play'
            });
          }
        };

        // Track error events
        const handleError = (event) => {
          const errorDetail = event?.detail ?? event;
          if (!errorDetail || typeof errorDetail.code === 'undefined') return;

          if (typeof window !== 'undefined' && window.gtag) {
            window.gtag('event', 'video_error', {
              channel_name: channel.name || 'unknown',
              channel_id: channel.id || 'unknown',
              source_url: link || 'unknown',
              error_code: errorDetail.code,
              error_message: errorDetail.message || 'Unknown error',
              error_severity: errorDetail.severity || 'unknown',
              event_category: 'video',
              event_label: 'error'
            });
          }
        };

        video.addEventListener('play', handlePlay);
        video.api.addEventListener('error', handleError);

        return () => {
          video.api.removeEventListener('play', handlePlay);
          video.api.removeEventListener('error', handleError);
        };
      };

      const cleanupAnalytics = setupAnalyticsTracking();

        video.addEventListener('error', handlePlayerError);

        // Reset play event flag when source changes
        setPlayEventSent(false);

        video.src = link;
        handleResize();

      return () => {
        if (cleanupAnalytics) cleanupAnalytics();
      };
    };

    loadSource();
    video.addEventListener('resize', handleResize);

    return () => {
      video.removeEventListener('resize', handleResize);
      video.removeEventListener('error', handlePlayerError);
      try {
        video.api?.unload?.();
      } catch (err) {
        console.error('Failed to cleanup channel player', err);
      }

      // Reset play event flag when unloading
      setPlayEventSent(false);
    };
  }, [channel, currentSourceIndex, link, drm, clearkeys]);

  return (
    <div className={`relative bg-black w-full ${fullViewport ? 'h-full' : 'overflow-hidden rounded-3xl shadow-xl'}`}>
      <div className={fullViewport ? 'w-full h-full' : 'w-full aspect-video'}>
        <MediaController className="w-full h-full bg-black" autohide="5">
          <ShakaVideo
            ref={videoRef}
            slot="media"
            poster={storageUrl(channel.image)}
            className="w-full h-full object-cover bg-black"
          />
          <div slot="middle-chrome" className="center-overlay">
            <MediaPlayButton className="big-play-btn"></MediaPlayButton>
            <MediaLoadingIndicator></MediaLoadingIndicator>
          </div>
          <MediaControlBar className="bg-black/75 p-1 mx-4 m-2 rounded-full">
            <MediaPlayButton className="media-control"></MediaPlayButton>
            <MediaLiveButton className="media-control scale-80 sm:scale-100"></MediaLiveButton>
            <MediaMuteButton className="media-control"></MediaMuteButton>
            <MediaVolumeRange className="media-control hidden md:block"></MediaVolumeRange>
              <div className="flex items-center justify-center gap-2 truncate md:flex-grow">
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
      </div>

      {error && (
        <div className="absolute inset-0 flex items-center justify-center bg-black/80 text-red-400 p-4 text-center">
          {error}
        </div>
      )}
    </div>
  );
}
