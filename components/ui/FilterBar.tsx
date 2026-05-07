'use client'

import { useRouter, usePathname, useSearchParams } from 'next/navigation'
import { useTransition } from 'react'
import type { SortField, Quality } from '@/types'

const SORT_OPTIONS: { label: string; value: SortField }[] = [
  { label: 'Most Viewed', value: 'views' },
  { label: 'Name A–Z', value: 'name' },
  { label: 'Newest', value: 'created_at' },
]

const QUALITY_OPTIONS: (Quality | 'all')[] = ['all', '4K', '1080p', '720p', '480p']

interface Props {
  total?: number
}

export function FilterBar({ total }: Props) {
  const router = useRouter()
  const pathname = usePathname()
  const searchParams = useSearchParams()
  const [isPending, startTransition] = useTransition()

  const currentSort    = (searchParams.get('sort') as SortField) ?? 'views'
  const currentOrder   = searchParams.get('order') ?? 'desc'
  const currentQuality = (searchParams.get('quality') as Quality) ?? 'all'

  function update(key: string, value: string) {
    const params = new URLSearchParams(searchParams.toString())
    if (value === 'all' || value === '') {
      params.delete(key)
    } else {
      params.set(key, value)
    }
    params.delete('page') // reset pagination
    startTransition(() => router.push(`${pathname}?${params.toString()}`))
  }

  function toggleOrder() {
    update('order', currentOrder === 'desc' ? 'asc' : 'desc')
  }

  return (
    <div
      className={`flex flex-wrap items-center gap-2 border-b border-white/[0.07] px-5 py-3 transition-opacity ${isPending ? 'opacity-50' : ''}`}
    >
      {/* Sort */}
      <span className="text-[11px] uppercase tracking-wide text-zinc-500">Sort:</span>
      {SORT_OPTIONS.map(opt => (
        <button
          key={opt.value}
          onClick={() => update('sort', opt.value)}
          className={`rounded-md border px-3 py-1 text-xs transition-all ${
            currentSort === opt.value
              ? 'border-white/15 bg-zinc-800 text-zinc-100'
              : 'border-white/[0.07] text-zinc-500 hover:border-white/15 hover:text-zinc-300'
          }`}
        >
          {opt.label}
        </button>
      ))}

      <button
        onClick={toggleOrder}
        title="Toggle order"
        className="rounded-md border border-white/[0.07] px-2 py-1 text-xs text-zinc-500 transition-all hover:border-white/15 hover:text-zinc-300"
      >
        {currentOrder === 'desc' ? '↓' : '↑'}
      </button>

      <div className="mx-1 h-5 w-px bg-white/[0.07]" />

      {/* Quality */}
      <span className="text-[11px] uppercase tracking-wide text-zinc-500">Quality:</span>
      {QUALITY_OPTIONS.map(q => (
        <button
          key={q}
          onClick={() => update('quality', q)}
          className={`rounded-md border px-3 py-1 text-xs transition-all ${
            (currentQuality === q) || (q === 'all' && !searchParams.get('quality'))
              ? 'border-white/15 bg-zinc-800 text-zinc-100'
              : 'border-white/[0.07] text-zinc-500 hover:border-white/15 hover:text-zinc-300'
          }`}
        >
          {q === 'all' ? 'All' : q}
        </button>
      ))}

      {/* Results count */}
      {total != null && (
        <span className="ml-auto text-xs text-zinc-500">
          {total.toLocaleString()} channels
        </span>
      )}
    </div>
  )
}
