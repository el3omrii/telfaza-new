'use client'; // ← Mark as Client Component
import { useState, useEffect } from 'react';
import { getChannelsByIds } from '@/lib/api'
import { Channel } from '@/types'
import { ChannelGrid } from '@/components/channel/ChannelGrid'
import { readFavorites } from '@/lib/utils';

export default function ClientFavoritesGrid() {
    const [favChannels, setFavChannels] = useState<Channel[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const favorites = readFavorites()
    useEffect(() => {
        console.log(favorites)
        const fetchFavorites = async () => {
          try {
            const channels = await getChannelsByIds(favorites);
            setFavChannels(channels);
          } catch (err) {
            setError("Failed to load watching now channels");
            console.error("Error fetching watching now channels:", err);
          } finally {
            setLoading(false);
          }
        };
        fetchFavorites()
    }, [])
    if (loading) {
        return null; // Don't show loading state for this section
    }
    
    if (error) {
        return null; // Don't show error state, just hide the section
    }
    
    if (favChannels.length === 0) {
        return null; // Don't render if no active channels
    }
    
    return (
        <section className="mt-8">
        <ChannelGrid channels={favChannels} />
        </section>
    );
};
