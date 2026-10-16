// app/components/ClientChannelPlayer.js
'use client'; // ← Mark as Client Component
import { createContext, useContext } from 'react';
import { storageUrl } from '@/lib'
import dynamic from 'next/dynamic';

// `next/dynamic` with `ssr: false` only renders the `loading` option on the
// server (and while the Shaka chunk loads on the client). The channel is passed
// through context so the fallback can show its poster without loading a stream.
const ChannelContext = createContext(null);

const PlayerFallback = () => {
  const channel = useContext(ChannelContext);

  return (
    <div className="w-full h-full bg-black flex items-center justify-center">
      <video
        className="w-full h-full object-cover"
        poster={storageUrl(channel?.image)} // Accessibilité immédiate pour Googlebot
        preload="none"
        playsInline
      />
    </div>
  );
};

// Dynamically import the actual player with SSR disabled
const ChannelPlayer = dynamic(
  () => import('@/components/channel/ChannelPlayer'), // path to your Shaka player component
  {
    ssr: false,
    loading: () => <PlayerFallback />,
  }
);

export default function ClientChannelPlayer({ channel }) {
  return (
    <ChannelContext.Provider value={channel}>
      <ChannelPlayer channel={channel} />
    </ChannelContext.Provider>
  );
}