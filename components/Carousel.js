'use client';

import { useRef } from 'react';
import ChannelCard from './ChannelCard';

export default function Carousel({ channels }) {
  const carouselRef = useRef(null);

  const scroll = (direction) => {
    if (!carouselRef.current) return;
    carouselRef.current.scrollBy({ left: direction * 360, behavior: 'smooth' });
  };

  return (
    <div className="relative">
      <div
        ref={carouselRef}
        className="flex overflow-x-auto space-x-4 pb-4 scrollbar-hide"
      >
        {channels.map((channel) => (
          <div key={channel.id} className="min-w-[320px] shrink-0">
            <ChannelCard channel={channel} featured />
          </div>
        ))}
      </div>

      <button
        type="button"
        onClick={() => scroll(-1)}
        className="absolute left-0 top-1/2 -translate-y-1/2 bg-dark text-white p-2 rounded-full opacity-80 hover:opacity-100"
      >
        ‹
      </button>
      <button
        type="button"
        onClick={() => scroll(1)}
        className="absolute right-0 top-1/2 -translate-y-1/2 bg-dark text-white p-2 rounded-full opacity-80 hover:opacity-100"
      >
        ›
      </button>
    </div>
  );
}
