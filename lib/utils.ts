import type { ChannelFilters, SortField, SortOrder, Quality } from '@/types'
import type { ParsedUrlQuery } from 'querystring'

export function fmtViews(n: number): string {
  if (n >= 1_000_000) return `${(n / 1_000_000).toFixed(1)}M`
  if (n >= 1_000)     return `${(n / 1_000).toFixed(1)}K`
  return String(n)
}

/** Convert Next.js query object → ChannelFilters */
export function queryToFilters(q: ParsedUrlQuery): ChannelFilters {
  return {
    search:   str(q.search),
    country:  num(q.country),
    language: str(q.language),
    quality:  q.quality as Quality | undefined,
    featured: q.featured === '1' ? true : undefined,
    sort:     q.sort as SortField | undefined,
    order:    q.order as SortOrder | undefined,
    per_page: num(q.per_page) ?? 24,
    page:     num(q.page) ?? 1,
    category: arr(q.category),
    tag:      arr(q.tag),
  }
}

/** Convert filters → plain object for router.push / href */
export function filtersToQuery(f: ChannelFilters): Record<string, string | string[]> {
  const out: Record<string, string | string[]> = {}
  for (const [k, v] of Object.entries(f)) {
    if (v === undefined || v === null || v === '') continue
    if (Array.isArray(v)) out[k] = v.map(String)
    else out[k] = String(v)
  }
  return out
}

function str(v: unknown): string | undefined {
  return typeof v === 'string' && v ? v : undefined
}
function num(v: unknown): number | undefined {
  const n = Number(v)
  return !isNaN(n) && n > 0 ? n : undefined
}
function arr(v: unknown): number[] | number | undefined {
  if (Array.isArray(v)) return v.map(Number).filter(Boolean)
  if (typeof v === 'string' && v) return Number(v) || undefined
  return undefined
}