// app/components/ClientChannelPlayer.tsx
'use client'; // ← Mark as Client Component
import { storageUrl } from '@/lib'
import dynamic from 'next/dynamic';

const PlayerFallback = ({ channel }) => (
  <div className="w-full h-full bg-black flex items-center justify-center">
    <video 
      className="w-full h-full object-cover"
      poster={storageUrl(channel.image)} // Accessibilité immédiate pour Googlebot
      preload="metadata"
      controls
    >
      <source src={channel.sources[0].link} type={channel.sources[0].type == 'hls' ? 'application/x-mpegURL' : 'application/dash+xml'} />
    </video>
  </div>
);
// Dynamically import the actual player with SSR disabled
const ChannelPlayer = dynamic(
  () => import('@/components/channel/ChannelPlayer'), // path to your Shaka player component
  {
    ssr: false,
    loading: () => <div className="w-full h-full bg-black" />, 
  }
);

export default function ClientChannelPlayer({ channel }) {
  return (
    <ChannelPlayer channel={channel}>
      <PlayerFallback channel={channel} />
    </ChannelPlayer>
  );
}