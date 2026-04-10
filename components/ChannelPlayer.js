'use client';

import { useState, useRef, useEffect } from 'react';
import {
  MediaController,
  MediaControlBar,
  MediaLoadingIndicator,
  MediaLiveButton,
  MediaVolumeRange,
  MediaPlayButton,
  MediaFullscreenButton,
} from 'media-chrome/react';
import { MediaRenditionMenu, MediaRenditionMenuButton } from 'media-chrome/react/menu';
import ShakaVideo from 'shaka-video-element/react';

export default function ChannelPlayer({ streamUrl, poster, drm = false, clearkeys = null }) {
  const [error, setError] = useState('');
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

    video.src = streamUrl;
  }, [streamUrl, drm, clearkeys]);

  return (
    <div className="relative overflow-hidden rounded-3xl bg-black shadow-xl w-full">
      <MediaController className="w-full h-full bg-black">
        <ShakaVideo
          ref={videoRef}
          slot="media"
          
          poster={poster}
          crossOrigin="anonymous"
          className="w-full h-full object-cover bg-black"
        />

        <MediaLoadingIndicator slot="centered-chrome"></MediaLoadingIndicator>

        <MediaControlBar>
          <MediaPlayButton></MediaPlayButton>
          <MediaLiveButton></MediaLiveButton>
          <MediaVolumeRange></MediaVolumeRange>
          <span className="flex-grow"></span>
          <MediaRenditionMenu hidden anchor="auto"></MediaRenditionMenu>
          <MediaRenditionMenuButton></MediaRenditionMenuButton>
          <MediaFullscreenButton></MediaFullscreenButton>
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
