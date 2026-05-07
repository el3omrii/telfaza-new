import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import { getTagChannels } from '@/lib/api'
import { paramsToFilters } from '@/lib/utils'
import { ChannelGrid } from '@/components/channel/ChannelGrid'
import { FilterBar } from '@/components/ui/FilterBar'
import { Pagination } from '@/components/ui/Pagination'

interface Props {
  params: { id: string }
  searchParams: Record<string, string | string[] | undefined>
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  try {
    const { id } = await params
    const { tag } = await getTagChannels(id)
    return { title: `#${tag.name}` }
  } catch {
    return { title: 'Tag' }
  }
}

export default async function TagPage({ params, searchParams }: Props) {
  const [resolvedParams, resolvedSearchParams] = await Promise.all([
    params,
    searchParams
  ])
  const filters = paramsToFilters(resolvedSearchParams)
  let data
  try {
    data = await getTagChannels(resolvedParams.id, filters)
  } catch {
    notFound()
  }

  const { tag, channels } = data
  console.log(channels)

  return (
    <div className="mx-auto max-w-screen-2xl">
      {/* Header */}
      <div className="border-b border-white/[0.07] px-5 py-8">
        <div className="flex items-baseline gap-3">
          <span className="font-head text-2xl font-extrabold text-teal-400">#</span>
          <h1 className="font-head text-3xl font-extrabold tracking-tight">{tag.name}</h1>
          <span className="text-sm text-zinc-500">
            {channels.meta.total.toLocaleString()} channels
          </span>
        </div>
      </div>

      {/* Filters */}
      <FilterBar total={channels.meta.total} />

      {/* Grid */}
      <div className="px-5 py-6">
        <ChannelGrid channels={channels.data} />
        <Pagination meta={channels.meta} />
      </div>
    </div>
  )
}
