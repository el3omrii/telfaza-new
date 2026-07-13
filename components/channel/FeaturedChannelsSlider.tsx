"use client";

import { useState } from "react";
import { useKeenSlider } from "keen-slider/react";
import type { Channel } from "@/types";
import { ChannelCard } from "./ChannelCard";

interface Props {
  channels: Channel[];
}

function ArrowIcon({ direction }: { direction: "left" | "right" }) {
  return direction === "left" ? (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} className="h-4 w-4">
      <path d="M15 18l-6-6 6-6" />
    </svg>
  ) : (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} className="h-4 w-4">
      <path d="m9 18 6-6-6-6" />
    </svg>
  );
}

export function FeaturedChannelsSlider({ channels }: Props) {
  const [loaded, setLoaded] = useState(false);
  const [canGoPrev, setCanGoPrev] = useState(false);
  const [canGoNext, setCanGoNext] = useState(false);

  const syncState = (slider: { track: { details: { rel: number; maxIdx: number } } }) => {
    setCanGoPrev(slider.track.details.rel > 0);
    setCanGoNext(slider.track.details.rel < slider.track.details.maxIdx);
  };

  const [sliderRef, instanceRef] = useKeenSlider<HTMLDivElement>({
    loop: false,
    mode: "snap",
    renderMode: "performance",
    created(slider) {
      setLoaded(true);
      syncState(slider);
    },
    slideChanged(slider) {
      syncState(slider);
    },
    updated(slider) {
      syncState(slider);
    },
    slides: {
      perView: 1.8,
      spacing: 6,
    },
    breakpoints: {
      "(min-width: 640px)": {
        slides: { perView: 3.1, spacing: 12 },
      },
      "(min-width: 1024px)": {
        slides: { perView: 4.05, spacing: 14 },
      },
      "(min-width: 1280px)": {
        slides: { perView: 5.05, spacing: 16 },
      },
    },
  });

  // Determine if navigation should be shown based on number of channels
  // Navigation is only needed when there are more channels than can fit in the view
  const shouldShowNavigation = () => {
    if (channels.length <= 1) return false;

    // Default to showing navigation if we can't determine the current breakpoint
    // This is a simplified approach that assumes navigation is needed for more than 1 channel
    // A more precise implementation would require tracking the current breakpoint
    return channels.length > 1;
  };

  return (
    <div className="relative">
      <div ref={sliderRef} className="keen-slider -mx-1.5 pl-1.5">
        {channels.map(channel => (
          <div key={channel.id} className="keen-slider__slide px-1.5">
            <ChannelCard channel={channel} />
          </div>
        ))}
      </div>

      <div className="mt-4 flex items-center justify-between gap-3">
        {shouldShowNavigation() ? (
          <>
            <p className="text-xs text-zinc-500">
              Swipe or use the arrows to browse featured channels.
            </p>

            <div className="flex gap-2">
              <button
                type="button"
                onClick={() => instanceRef.current?.prev()}
                disabled={!loaded || !canGoPrev}
                aria-label="Previous featured channels"
                className="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-zinc-900 text-white transition hover:border-white/20 hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-40"
              >
                <ArrowIcon direction="left" />
              </button>
              <button
                type="button"
                onClick={() => instanceRef.current?.next()}
                disabled={!loaded || !canGoNext}
                aria-label="Next featured channels"
                className="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-zinc-900 text-white transition hover:border-white/20 hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-40"
              >
                <ArrowIcon direction="right" />
              </button>
            </div>
          </>
        ) : (
          <p className="text-xs text-zinc-500">
            {channels.length === 1 ? 'Featured channel' : 'Featured channels'}
          </p>
        )}
      </div>
    </div>
  );
}