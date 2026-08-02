import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import { getCategoryChannels } from '@/lib/api'
import { paramsToFilters } from '@/lib/utils'
import { ChannelGrid } from '@/components/channel/ChannelGrid'
import { FilterBar } from '@/components/ui/FilterBar'
import { Pagination } from '@/components/ui/Pagination'
import { TagChips } from '@/components/tag/TagChips'
import { buildMetadata } from '@/lib/seo'

interface Props {
  params: { id: string }
  searchParams: Record<string, string | string[] | undefined>
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { id } = await params
  try {
    const { category } = await getCategoryChannels(id)
    return buildMetadata({
      title: category.name,
      description: category.description ?? `Browse live channels in ${category.name}.`,
      path: `/categories/${category.slug}`,
    })
  } catch {
    return buildMetadata({
      title: 'Category',
      description: 'Browse channels by category.',
      path: '/categories',
    })
  }
}

export default async function CategoryPage({ params, searchParams }: Props) {
  const [resolvedParams, resolvedSearchParams] = await Promise.all([
    params,
    searchParams
  ])
  const filters = paramsToFilters(resolvedSearchParams)

  let data
  try {
    data = await getCategoryChannels(resolvedParams.id, filters)
  } catch {
    notFound()
  }

  const { category, channels } = data

  // Collect unique tags from channel results for quick-filter chips
  const tagMap = new Map<number, { id: number; name: string; slug: string }>()
  channels.data.forEach(ch =>
    ch.tags?.forEach(t => tagMap.set(t.id, t))
  )
  const tags = Array.from(tagMap.values())

  return (
    <main className="max-w-7xl w-full mx-auto mt-16 px-6 md:px-12">
      {/* Header */}
      <div className="border-b border-white/[0.07] px-5 py-8">
        <div className="flex items-end gap-4">
          <div
            className="h-3 w-3 shrink-0 rounded-full"
            style={{ background: category.color, marginBottom: 6 }}
          />
          <div>
            <h1
              className="font-head text-3xl font-extrabold tracking-tight"
              style={{ color: category.color }}
            >
              {category.name}
            </h1>
            {category.description && (
              <p className="mt-1 text-sm text-zinc-500">{category.description}</p>
            )}
            <p className="mt-1 text-sm text-zinc-500">
              {channels.meta.total.toLocaleString()} channels
            </p>
          </div>
        </div>
      </div>

      {/* Filters */}
      <FilterBar total={channels.meta.total} />

      {/* Tag quick filters */}
      {tags.length > 0 && (
        <div className="flex flex-wrap items-center gap-2 border-b border-white/[0.07] px-5 py-3">
          <span className="text-[11px] uppercase tracking-wide text-zinc-500">Tags:</span>
          <TagChips tags={tags} />
        </div>
      )}

      {/* Grid */}
      <div className="px-5 py-6">
        <ChannelGrid channels={channels.data} />
        <Pagination meta={channels.meta} />
      </div>
    </main>
  )
}
