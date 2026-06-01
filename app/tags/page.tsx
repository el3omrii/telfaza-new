import type { Metadata } from 'next'
import Link from 'next/link'
import { getTags } from '@/lib/api'
import type { Tag } from '@/types'

export const metadata: Metadata = { title: 'Tags' }

export default async function TagsPage() {
  const tags = await getTags()

  const max = Math.max(...tags.map(t => t.channels_count ?? 0))

  function fontSize(count: number): string {
    const scale = max > 0 ? count / max : 0
    return `${Math.round(11 + scale * 10)}px`
  }

  return (
    <main className="max-w-7xl w-full mx-auto md:mt-16 px-6 md:px-12">
      <div className="border-b border-white/[0.07] px-5 py-8">
        <h1 className="font-head text-3xl font-extrabold tracking-tight">Tags</h1>
        <p className="mt-1 text-sm text-zinc-500">
          Browse {tags.length} tags · size indicates channel count
        </p>
      </div>

      {/* Tag cloud */}
      <div className="px-5 py-10">
        <div className="flex flex-wrap gap-3">
          {tags.map(tag => (
            <TagChip key={tag.id} tag={tag} fontSize={fontSize(tag.channels_count ?? 0)} />
          ))}
        </div>
      </div>

      {/* Top tags grid */}
      <div className="border-t border-white/[0.07] px-5 py-8">
        <h2 className="font-head mb-5 text-lg font-bold tracking-tight">Top Tags</h2>
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
          {[...tags]
            .sort((a, b) => (b.channels_count ?? 0) - (a.channels_count ?? 0))
            .slice(0, 12)
            .map(tag => (
              <Link
                key={tag.id}
                href={`/tags/${tag.id}`}
                className="rounded-xl border border-white/[0.07] bg-zinc-900 p-4 transition-all hover:-translate-y-0.5 hover:border-white/15"
              >
                <p className="font-head mb-1 text-sm font-bold text-teal-400">#{tag.name}</p>
                <p className="text-[11px] text-zinc-500">{tag.channels_count ?? 0} channels</p>
              </Link>
            ))}
        </div>
      </div>
    </main>
  )
}

function TagChip({ tag, fontSize }: { tag: Tag; fontSize: string }) {
  return (
    <Link
      href={`/tags/${tag.id}`}
      style={{ fontSize }}
      className="flex items-center gap-1.5 rounded-full border border-white/[0.07] bg-zinc-900 px-3 py-1.5 text-zinc-400 transition-all hover:border-teal-400/30 hover:text-teal-400"
    >
      <span className="text-teal-600">#</span>
      {tag.name}
      <span className="rounded bg-zinc-800 px-1.5 py-0.5 text-[10px] text-zinc-500">
        {tag.channels_count}
      </span>
    </Link>
  )
}
