"use client";

import { useEffect, useState } from "react";
import { FeaturedChannelsSlider } from "./FeaturedChannelsSlider";
import { getWatchingNowClient } from "@/lib/api-client";
import { SectionHead } from "@/components/ui/SectionHead";
import type { Channel } from "@/types";

export function WatchingNowSection() {
  const [activeChannels, setActiveChannels] = useState<Channel[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchWatchingNow = async () => {
      try {
        const channels = await getWatchingNowClient();
        setActiveChannels(channels);
      } catch (err) {
        setError("Failed to load watching now channels");
        console.error("Error fetching watching now channels:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchWatchingNow();

    // Set up polling to refresh data every 30 seconds
    const intervalId = setInterval(fetchWatchingNow, 30000);

    return () => clearInterval(intervalId);
  }, []);

  if (loading) {
    return null; // Don't show loading state for this section
  }

  if (error) {
    return null; // Don't show error state, just hide the section
  }

  if (activeChannels.length === 0) {
    return null; // Don't render if no active channels
  }

  return (
    <section className="mt-8">
      <SectionHead title="✦ Watching Now" />
      <FeaturedChannelsSlider channels={activeChannels} />
    </section>
  );
}