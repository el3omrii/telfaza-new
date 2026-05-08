// app/components/ClientChannelPlayer.tsx
'use client'; // ← Mark as Client Component

import dynamic from 'next/dynamic';

// Dynamically import the actual player with SSR disabled
const ChannelPlayer = dynamic(
  () => import('@/components/channel/ChannelPlayer'), // path to your Shaka player component
  {
    ssr: false,
    loading: () => <div className="player-loading">⏳ Loading player...</div>,
  }
);

export default function ClientChannelPlayer({ channel }) {
  return <ChannelPlayer channel={channel} />;
}