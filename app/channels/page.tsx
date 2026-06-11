import type { Metadata } from 'next'
import { Suspense } from 'react'
import { getChannels, getFiltersMeta, getCountries, getCategories } from '@/lib/api'
import { paramsToFilters } from '@/lib/utils'
import { ChannelGrid } from '@/components/channel/ChannelGrid'
import { FilterBar } from '@/components/ui/FilterBar'
import { Pagination } from '@/components/ui/Pagination'
import { CountrySelect, LanguageSelect } from '@/components/ui/CountrySelect'

export const metadata: Metadata = { title: 'Channels' }

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
    <main className="max-w-7xl w-full mx-auto md:mt-16 px-6 md:px-16 lg:px-24 xl:px-32">
      {/* Header */}
      <div className="border-b border-white/[0.07] px-5 py-8">
        <h1 className="font-head text-3xl font-extrabold tracking-tight">All Channels</h1>
        <p className="mt-1 text-sm text-zinc-500">
          Browse and filter {result.meta.total.toLocaleString()} channels
        </p>
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