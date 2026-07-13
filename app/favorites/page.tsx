import type { Metadata } from 'next'
import  ClientFavoritesGrid  from '@/components/channel/ClientFavoritesGrid'

import { buildMetadata } from '@/lib/seo'

export const metadata: Metadata = buildMetadata({
  title: 'Favorites',
  description: 'Your favorite channels on Telfaza LIVE',
  path: '/favorites',
})

const FAVORITES_KEY = 'telfaza_favorite_channels'

function readFavorites(): Array<string | number> {
  if (typeof window === 'undefined') return []

  try {
    const stored = window.localStorage.getItem(FAVORITES_KEY)
    if (!stored) return []

    return JSON.parse(stored) as Array<string | number>
  } catch {
    return []
  }
}

export default async function FavoritesPage() {
  // Get favorites from localStorage (client-side)
  // Since this is a server component, we'll handle this on the client side
  // and fetch the channels data accordingly
  const favorites = readFavorites()
  return (
    <main className="max-w-7xl w-full mx-auto mt-8 md:mt-16 px-6 md:px-12">
      {/* ── Hero header ── */}
      <div className="border-b border-white/[0.07] bg-zinc-900 px-5 py-8">
        <div className="flex items-center gap-3">
          <h1 className="font-head text-lg md:text-xl lg:text-3xl font-extrabold tracking-tight text-white">
            My Favorites
          </h1>
        </div>
        <p className="mt-2 text-sm text-zinc-400">
          Your bookmarked channels
        </p>
      </div>

      {/* ── Main content ── */}
      <div className="py-8">
        <ClientFavoritesGrid />
      </div>
    </main>
  )
}

function ClientFavoritesContent() {
  return (
    <div className="relative">
      {/* This will be rendered on the client side */}
      <div id="favorites-content">
        <div className="flex min-h-[300px] items-center justify-center text-sm text-zinc-500">
          Loading your favorite channels...
        </div>
      </div>

      <script
        dangerouslySetInnerHTML={{
          __html: `
            // Client-side script to load favorites
            document.addEventListener('DOMContentLoaded', async function() {
              try {
                const FAVORITES_KEY = 'telfaza_favorite_channels';

                // Read favorites from localStorage
                const stored = localStorage.getItem(FAVORITES_KEY);
                let favoriteIds = stored ? JSON.parse(stored) : [];

                if (!Array.isArray(favoriteIds)) {
                  favoriteIds = [];
                }

                const favoritesContent = document.getElementById('favorites-content');

                if (favoriteIds.length === 0) {
                  favoritesContent.innerHTML = \`
                    <div class="flex min-h-[300px] items-center justify-center text-sm text-zinc-500">
                      You haven't favorited any channels yet.
                    </div>
                  \`;
                  return;
                }

                // Fetch channel data from server
                const response = await fetch('/api/favorites', {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json',
                  },
                  body: JSON.stringify({ ids: favoriteIds })
                });

                if (!response.ok) {
                  throw new Error('Failed to fetch favorite channels');
                }

                const channels = await response.json();

                if (channels.length === 0) {
                  favoritesContent.innerHTML = \`
                    <div class="flex min-h-[300px] items-center justify-center text-sm text-zinc-500">
                      No favorite channels found.
                    </div>
                  \`;
                  return;
                }

                // Render the ChannelGrid with the fetched channels
                favoritesContent.innerHTML = \`
                  <div id="favorites-grid">
                    \${getChannelGridHTML(channels)}
                  </div>
                \`;

              } catch (error) {
                console.error('Error loading favorites:', error);
                const favoritesContent = document.getElementById('favorites-content');
                favoritesContent.innerHTML = \`
                  <div class="flex min-h-[300px] items-center justify-center text-sm text-zinc-500">
                    Error loading your favorite channels. Please try again.
                  </div>
                \`;
              }
            });

            function getChannelGridHTML(channels) {
              if (!channels || channels.length === 0) {
                return \`<div class="flex min-h-[300px] items-center justify-center text-sm text-zinc-500">No channels found.</div>\`;
              }

              const gridItems = channels.map(channel => {
                const logoUrl = channel.logo ? \`${process.env.NEXT_PUBLIC_STORAGE_URL || 'http://localhost:8000/storage'}/\${channel.logo}\` : null;
                const imageUrl = channel.image ? \`${process.env.NEXT_PUBLIC_STORAGE_URL || 'http://localhost:8000/storage'}/\${channel.image}\` : null;

                return \`
                  <a href="/channels/\${channel.slug}" class="group block overflow-hidden rounded-2xl border border-white/10 bg-zinc-900 transition-all hover:border-white/20 hover:bg-zinc-800">
                    <div class="relative aspect-video bg-black">
                      \${imageUrl ? \`<img src="\${imageUrl}" alt="\${channel.name}" class="w-full h-full object-cover" />\` : ''}
                      \${channel.active_viewers > 0 ? \`
                        <div class="absolute bottom-2 right-2 flex items-center gap-1 rounded-full bg-black/60 px-2 py-0.5 text-xs text-white backdrop-blur-sm">
                          <svg class="h-2 w-2 fill-red-500" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" /></svg>
                          <span>\${formatViewers(channel.active_viewers)}</span>
                        </div>
                      \` : ''}
                    </div>
                    <div class="p-3">
                      <div class="flex items-center gap-2">
                        \${logoUrl ? \`
                          <div class="h-6 w-6 overflow-hidden rounded-md border border-white/10">
                            <img src="\${logoUrl}" alt="\${channel.name}" class="h-full w-full object-contain" />
                          </div>
                        \` : \`
                          <div class="flex h-6 w-6 items-center justify-center rounded-md border border-white/10 bg-zinc-800">
                            <span class="text-[10px] font-bold uppercase text-zinc-400">\${channel.name.slice(0, 2)}</span>
                          </div>
                        \`}
                        <h3 class="truncate flex-1 text-sm font-semibold text-white">\${channel.name}</h3>
                      </div>
                      <p class="mt-1.5 truncate text-xs text-zinc-400">\${channel.country ? channel.country.name : ''}</p>
                    </div>
                  </a>
                \`;
              }).join('');

              return \`<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">\${gridItems}</div>\`;
            }

            function formatViewers(count) {
              if (count >= 1000000) return (count / 1000000).toFixed(1) + 'M';
              if (count >= 1000) return (count / 1000).toFixed(1) + 'K';
              return count.toString();
            }
          `,
        }}
      />
    </div>
  )
}