import type { Metadata } from 'next'
import Link from 'next/link'
import { redirect } from 'next/navigation'
import { searchAll, storageUrl } from '@/lib/api'
import { buildMetadata } from '@/lib/seo'

interface Props {
  searchParams: Promise<Record<string, string | string[] | undefined>>
}

export async function generateMetadata({ searchParams }: Props): Promise<Metadata> {
  const params = await searchParams
  const query = typeof params.q === 'string' ? params.q : ''
  return buildMetadata({
    title: query ? `Search: ${query}` : 'Search channels',
    description: query ? `Search results for ${query}` : 'Search live TV channels',
    path: '/search',
  })
}

export default async function SearchPage({ searchParams }: Props) {
  const params = await searchParams
  const query = typeof params.q === 'string' ? params.q.trim() : ''

  if (!query) {
    redirect('/channels')
  }

  const results = await searchAll(query, 24)

  return (
    <main className="mx-auto mt-8 w-full max-w-7xl px-6 py-8 md:mt-16 md:px-12 lg:px-16">
      <div className="mb-6 border-b border-white/10 pb-6">
        <p className="text-xs uppercase tracking-[0.3em] text-zinc-500">Search</p>
        <h1 className="mt-2 text-3xl font-semibold text-white">Results for “{query}”</h1>
        <p className="mt-2 text-sm text-zinc-400">
          Showing {results.channels.length} channel{results.channels.length === 1 ? '' : 's'} matching your search.
        </p>
      </div>

      {results.channels.length > 0 ? (
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
          {results.channels.map((channel) => {
            const logo = storageUrl(channel.logo)
            return (
              <Link
                key={channel.id}
                href={`/channels/${channel.slug}`}
                className="group rounded-2xl border border-white/10 bg-zinc-900/80 p-4 transition-all duration-200 hover:-translate-y-1 hover:border-[#e8490f]/40 hover:bg-zinc-800/80"
              >
                <div className="flex items-center gap-3">
                  <div className="flex h-14 w-14 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-zinc-800">
                    {logo ? (
                      // eslint-disable-next-line @next/next/no-img-element
                      <img src={logo} alt={channel.name} className="h-full w-full object-contain" />
                    ) : (
                      <span className="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                        {channel.name.slice(0, 2)}
                      </span>
                    )}
                  </div>
                  <div className="min-w-0">
                    <h2 className="truncate text-base font-semibold text-white">{channel.name}</h2>
                    <p className="mt-1 text-sm text-zinc-500">Live channel</p>
                  </div>
                </div>
              </Link>
            )
          })}
        </div>
      ) : (
        <div className="rounded-2xl border border-dashed border-white/10 bg-zinc-900/60 p-8 text-center text-zinc-400">
          No channels matched “{query}”.
        </div>
      )}
    </main>
  )
}
