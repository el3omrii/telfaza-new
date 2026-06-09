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

export default function ChannelPlayer({ channel }) {
  const [error, setError] = useState('');
  const [mediaHeight, setMediaHeight] = useState('');
  const { sources: [{ link, drm, clearkeys }] } = channel
  const videoRef = useRef(null);

  useEffect(() => {
    const video = videoRef.current;
    if (!video) return;
    console.log(clearkeys)

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
      video.addEventListener('resize', handleResize);
      handleResize();
    }

    return () => {
      video?.removeEventListener('resize', handleResize);
      try {
        video.src = '';
        video.api?.unload?.();
      } catch (err) {
        console.error('Failed to cleanup channel player', err);
      }
    };
  }, [channel]);

  return (
    <div className="relative overflow-hidden rounded-3xl bg-black shadow-xl w-full">
      <div className="w-full aspect-video">
        <MediaController className="w-full h-full bg-black">
          <ShakaVideo
            ref={videoRef}
            slot="media"
            
            poster={storageUrl(channel.image)}
            crossOrigin="anonymous"
            className="w-full h-full object-cover bg-black"
          />

          <MediaLoadingIndicator slot="centered-chrome"></MediaLoadingIndicator>

          <MediaControlBar className="bg-black/75 p-1 mx-4 m-2 rounded-full">
            <MediaPlayButton className="media-control"></MediaPlayButton>
            <MediaLiveButton className="media-control"></MediaLiveButton>
            <MediaMuteButton className="media-control"></MediaMuteButton>
            <MediaVolumeRange className="media-control"></MediaVolumeRange>
            <div className="flex-grow flex items-center justify-center gap-2">
              <img src={storageUrl(channel.logo)} alt={channel.name} className="w-12 object-cover rounded-xl" />
              <span className="text-sm text-muted">{channel.name}</span>
            </div>
            <MediaRenditionMenuButton className="media-control">
                <span slot="icon" className="text-[#58BEC9] border border-[#58BEC9] py-1 px-2 rounded-md bg-green-950/50 text-sm">{mediaHeight}</span>
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