// app/components/ClientChannelPlayer.tsx
'use client'; // ← Mark as Client Component

import dynamic from 'next/dynamic';

// Dynamically import the actual player with SSR disabled
const ChannelPlayer = dynamic(
  () => import('@/components/channel/ChannelPlayer'), // path to your Shaka player component
  {
    ssr: false,
    loading: () => (<div role="status" className="flex justify-center items-center w-full h-96 bg-gray-300 rounded-lg animate-pulse dark:bg-gray-700">
                      <svg className="w-12 h-12 text-gray-200 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" fill="currentColor" viewBox="0 0 384 512"><path d="M361 215C375.3 223.8 384 239.3 384 256C384 272.7 375.3 288.2 361 296.1L73.03 472.1C58.21 482 39.66 482.4 24.52 473.9C9.377 465.4 0 449.4 0 432V80C0 62.64 9.377 46.63 24.52 38.13C39.66 29.64 58.21 29.99 73.03 39.04L361 215z"></path></svg>
                      <span className="sr-only">Loading...</span>
                    </div>),
  }
);

export default function ClientChannelPlayer({ channel }) {
  return <ChannelPlayer channel={channel} />;
}