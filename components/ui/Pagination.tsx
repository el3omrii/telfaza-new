'use client'

import { useRouter, usePathname, useSearchParams } from 'next/navigation'
import type { PaginationMeta } from '@/types'

interface Props {
  meta: PaginationMeta
}

export function Pagination({ meta }: Props) {
  const router = useRouter()
  const pathname = usePathname()
  const searchParams = useSearchParams()

  if (meta.last_page <= 1) return null

  function goTo(page: number) {
    const params = new URLSearchParams(searchParams.toString())
    params.set('page', String(page))
    router.push(`${pathname}?${params.toString()}`)
  }

  const { current_page: cur, last_page: last } = meta

  const pages: (number | '…')[] = []
  if (last <= 7) {
    for (let i = 1; i <= last; i++) pages.push(i)
  } else {
    pages.push(1)
    if (cur > 3) pages.push('…')
    for (let i = Math.max(2, cur - 1); i <= Math.min(last - 1, cur + 1); i++) pages.push(i)
    if (cur < last - 2) pages.push('…')
    pages.push(last)
  }

  return (
    <div className="flex items-center justify-center gap-1.5 py-8">
      <PgBtn disabled={cur === 1} onClick={() => goTo(cur - 1)}>‹</PgBtn>
      {pages.map((p, i) =>
        p === '…' ? (
          <span key={`ellipsis-${i}`} className="px-2 text-sm text-zinc-500">…</span>
        ) : (
          <PgBtn key={p} active={p === cur} onClick={() => goTo(p as number)}>
            {p}
          </PgBtn>
        )
      )}
      <PgBtn disabled={cur === last} onClick={() => goTo(cur + 1)}>›</PgBtn>
    </div>
  )
}

function PgBtn({
  children,
  active,
  disabled,
  onClick,
}: {
  children: React.ReactNode
  active?: boolean
  disabled?: boolean
  onClick: () => void
}) {
  return (
    <button
      onClick={onClick}
      disabled={disabled}
      className={`flex h-8 w-8 items-center justify-center rounded-lg border text-sm transition-all disabled:pointer-events-none disabled:opacity-30 ${
        active
          ? 'border-lime-400 bg-lime-400 font-bold text-zinc-950'
          : 'border-white/[0.07] text-zinc-400 hover:border-white/15 hover:text-zinc-100'
      }`}
    >
      {children}
    </button>
  )
}
