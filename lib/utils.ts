import type { ChannelFilters, SortField, SortOrder, Quality } from '@/types'

/** Format view count: 84320 → "84.3K" */
export function fmtViews(n: number): string {
  if (n >= 1_000_000) return `${(n / 1_000_000).toFixed(1)}M`
  if (n >= 1_000) return `${(n / 1_000).toFixed(1)}K`
  return String(n)
}
/** convert hex colors to rgb and add opacity */
export function hexToRgba(hex: string, opacity: number) {
  // Remove the # if present
  const cleanHex = hex.replace('#', '');
  
  // Parse RGB values
  const r = parseInt(cleanHex.substring(0, 2), 16);
  const g = parseInt(cleanHex.substring(2, 4), 16);
  const b = parseInt(cleanHex.substring(4, 6), 16);
  
  return `rgba(${r}, ${g}, ${b}, ${opacity})`;
}

/** Build a URLSearchParams string from ChannelFilters (for client-side navigation) */
export function filtersToParams(filters: ChannelFilters): URLSearchParams {
  const p = new URLSearchParams()
  for (const [key, val] of Object.entries(filters)) {
    if (val === undefined || val === null || val === '') continue
    if (Array.isArray(val)) {
      val.forEach(v => p.append(`${key}[]`, String(v)))
    } else {
      p.set(key, String(val))
    }
  }
  return p
}

/** Parse searchParams (from Next.js page props) into a ChannelFilters object */
export function paramsToFilters(
  sp: Record<string, string | string[] | undefined>
): ChannelFilters {
  return {
    search:   str(sp.search),
    country:  num(sp.country),
    language: str(sp.language),
    quality:  sp.quality as Quality | undefined,
    featured: sp.featured === '1' ? true : undefined,
    sort:     sp.sort as SortField | undefined,
    order:    sp.order as SortOrder | undefined,
    per_page: num(sp.per_page),
    page:     num(sp.page),
    category: arr(sp.category),
    tag:      arr(sp.tag),
  }
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

/** Merge an existing filter object with partial updates */
export function mergeFilters(
  base: ChannelFilters,
  updates: Partial<ChannelFilters>
): ChannelFilters {
  return { ...base, ...updates, page: 1 }  // reset page on any filter change
}

export function readFavorites(): Array<string | number> {
   const FAVORITES_KEY = 'telfaza_favorite_channels'
  if (typeof window === 'undefined') return []

  try {
    const stored = window.localStorage.getItem(FAVORITES_KEY)
    if (!stored) return []

    return JSON.parse(stored) as Array<string | number>
  } catch {
    return []
  }
}