'use client'

import { useRouter, usePathname, useSearchParams } from 'next/navigation'
import type { Tag } from '@/types'

interface Props {
  tags: Tag[]
}

export function TagChips({ tags }: Props) {
  const router = useRouter()
  const pathname = usePathname()
  const searchParams = useSearchParams()
  const currentTag = searchParams.get('tag')

  function toggle(id: number) {
    const params = new URLSearchParams(searchParams.toString())
    if (currentTag === String(id)) {
      params.delete('tag')
    } else {
      params.set('tag', String(id))
    }
    params.delete('page')
    router.push(`${pathname}?${params.toString()}`)
  }

  return (
    <>
      {tags.map(tag => (
        <button
          key={tag.id}
          onClick={() => toggle(tag.id)}
          className={`rounded-full border px-3 py-1 text-xs transition-all ${
            currentTag === String(tag.id)
              ? 'border-teal-400/30 bg-teal-400/10 text-teal-400'
              : 'border-white/[0.07] text-zinc-500 hover:border-teal-400/20 hover:text-teal-400'
          }`}
        >
          #{tag.name}
        </button>
      ))}
    </>
  )
}
