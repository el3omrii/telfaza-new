import { Suspense } from 'react'
import { Metadata } from 'next'
import { getChannels, getFiltersMeta, getCountries, getCategories } from '@/lib/api'
import { paramsToFilters } from '@/lib/utils'
import { ChannelGrid } from '@/components/channel/ChannelGrid'
import { FilterBar } from '@/components/ui/FilterBar'
import { Pagination } from '@/components/ui/Pagination'
import { CountrySelect, LanguageSelect } from '@/components/ui/CountrySelect'
import { buildMetadata } from '@/lib/seo'

/*export const metadata = buildMetadata({
  title: 'Channels',
  description: 'Browse and filter every live TV channel available on Telfaza LIVE.',
  path: '/channels',
})*/
export async function generateMetadata({ searchParams }: Props): Promise<Metadata> {
  const resolvedSearchParams = await searchParams
  const page = Number(resolvedSearchParams.page) || 1
  
  // Base path without query params
  const baseUrl = `${process.env.NEXT_PUBLIC_APP_URL}/channels`

  // Reconstruct clean canonical query string (excluding page to avoid duplicate indexing if desired, 
  // or including page parameter as standard canonical pagination)
  const queryParams = new URLSearchParams()
  if (page > 1) queryParams.set('page', page.toString())
  if (resolvedSearchParams.country) queryParams.set('country', String(resolvedSearchParams.country))
  if (resolvedSearchParams.language) queryParams.set('language', String(resolvedSearchParams.language))
  if (resolvedSearchParams.category) queryParams.set('category', String(resolvedSearchParams.category))

  const queryString = queryParams.toString()
  const canonicalUrl = queryString ? `${baseUrl}?${queryString}` : baseUrl

  return {
    title: page > 1 ? `Channels - Page ${page} | Telfaza LIVE` : 'Watch Arabic TV Channels Live Streaming',
    description: 'Browse and filter every live TV channel available on Telfaza LIVE.',
    alternates: {
      canonical: canonicalUrl,
    },
  }
}
interface Props {
  searchParams: Record<string, string | string[] | undefined>
}

export default async function ChannelsPage({ searchParams }: Props) {
  const [resolvedSearchParams] = await Promise.all([
    searchParams
  ])
  const filters = paramsToFilters(resolvedSearchParams)

  const [result, filtersMeta, countries, categories] = await Promise.all([
    getChannels(filters),
    getFiltersMeta(),
    getCountries(),
    getCategories(),
  ])

  return (
    <main className="max-w-7xl w-full mx-auto mt-16 px-6 md:px-16 lg:px-24 xl:px-32">
      {/* Header */}
      <div className="border-b border-white/[0.07] px-5 py-8">
        <h1 className="font-head text-3xl font-extrabold tracking-tight text-white">Watch Arabic TV Channels Live Streaming</h1>
        <p className="mt-1 text-sm text-zinc-500">
          Browse, filter, and watch {result.meta.total.toLocaleString()} top Arab TV channels online for free.
        </p>
        <h2 className="sr-only">Arabic Live TV Channel Directory</h2>
      </div>

      {/* Filters row 1 — sort + quality */}
      <Suspense fallback={<div className="h-12 border-b border-white/[0.07] px-5 py-3" />}>
        <FilterBar total={result.meta.total} />
      </Suspense>

      {/* Filters row 2 — country, category, language */}
      <Suspense fallback={<div className="flex gap-3 border-b border-white/[0.07] px-5 py-3 h-10 animate-pulse" />}>
        <div className="flex flex-wrap items-center gap-3 border-b border-white/[0.07] px-5 py-3">
          <CountrySelect countries={countries} />
          

          {/* Language */}
          <LanguageSelect languages={filtersMeta.languages} />{/*currentValue={resolvedSearchParams.language as string | undefined} />*/}
        </div>
      </Suspense>

      {/* Grid */}
      <div className="px-5 py-6">
        <ChannelGrid channels={result.data} />
        <Suspense fallback={<div className="h-12" />}>
          <Pagination meta={result.meta} />
        </Suspense>
      </div>
    </main>
  )
}

// ── Inline select helpers (server components that pre-select from searchParams) ──
{/*
function LanguageSelect({
  languages,
  currentValue,
}: {
  languages: string[]
  currentValue?: string
}) {
  // Note: the onChange routing logic is handled in the FilterBar client component.
  // These server-rendered selects hand off to client-side navigation via form or
  // a lightweight client wrapper — shown here as a placeholder.
  return (
    <select
      name="language"
      defaultValue={currentValue ?? ''}
      className="rounded-lg border border-white/[0.07] bg-zinc-900 px-3 py-1.5 text-xs text-zinc-400 focus:outline-none"
    >
      <option value="">All Languages</option>
      {languages.map(l => (
        <option key={l} value={l}>{l}</option>
      ))}
    </select>
  )
}
  */}