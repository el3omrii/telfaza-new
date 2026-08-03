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
  const [mediaHeight, setMediaHeight] = useState('');
  const { sources: [{ link, drm, clearkeys }] } = channel
  const videoRef = useRef(null);
  useEffect(() => {
    const video = videoRef.current;
    if (!video) return;

    // Configure DRM if needed
    if (drm && clearkeys && video.api) {
      try {
        video.api.configure({
          drm: {
            clearKeys: clearkeys
          }
        });
      } catch (err) {
        setError('DRM configuration failed');
      }
    }
    video.src = link;

    const handleResize = () => {
      if (video) {
        setMediaHeight(`${Math.min(video.videoWidth, video.videoHeight)}P`);
      }
    };

    if (video) {
      video.api.configure({
        manifest: {
          hls: {
            // Ignore les incohérences de timestamps et force l'alignement sur le manifeste
            ignoreManifestProgramDateTime: true,
            liveSegmentsDelay: 3 
          },
          // Augmente la tolérance aux écarts de synchronisation
          availabilityWindowOverride: 60 
        }
      })
      setupSnrtUrlFix(video.api)
      video.addEventListener('resize', handleResize);
      handleResize();
      video.addEventListener('error', (event) => {
        // event.detail contains the shaka.util.Error object
        console.error('Shaka Error Code:', event.detail.code);
        console.error('Shaka Error Category:', event.detail.category);
        console.error('Shaka Error Data:', event.detail.data);
      });
    }

    return () => {
      video?.removeEventListener('resize', handleResize);
      try {
        //video.src = '';
        video.api?.unload?.();
      } catch (err) {
        console.error('Failed to cleanup channel player', err);
      }
    };
  }, [channel]);

  return (
    <div className={`relative bg-black w-full ${fullViewport ? 'h-full' : 'overflow-hidden rounded-3xl shadow-xl'}`}>
      <div className={fullViewport ? 'w-full h-full' : 'w-full aspect-video'}>
        <MediaController className="w-full h-full bg-black" autohide="-1">
          <ShakaVideo
            ref={videoRef}
            slot="media"
            poster={storageUrl(channel.image)}
            className="w-full h-full object-cover bg-black"
          />

          <MediaLoadingIndicator slot="centered-chrome"></MediaLoadingIndicator>

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
