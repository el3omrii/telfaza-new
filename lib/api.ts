import type {
  Slide, Channel, Category, Tag, Country,
  Paginated, SearchResults, ChannelFilters, FiltersMeta,
} from '@/types'

const API = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api'
export const STORAGE = process.env.NEXT_PUBLIC_STORAGE_URL ?? 'http://localhost:8000/storage'

// ── helpers ───────────────────────────────────────────────────────────────────

function cacheTags(...tags: Array<string | undefined>): string[] {
  return tags.filter((tag): tag is string => Boolean(tag))
}

function qs(params: Record<string, unknown>): string {
  const p = new URLSearchParams()
  for (const [k, v] of Object.entries(params)) {
    if (v === undefined || v === null || v === '') continue
    if (Array.isArray(v)) v.forEach(i => p.append(`${k}[]`, String(i)))
    else p.set(k, String(v))
  }
  const s = p.toString()
  return s ? `?${s}` : ''
}

async function get<T>(path: string, tags: string[] = [], revalidate?: number): Promise<T> {
  const next: { tags?: string[]; revalidate?: number } | undefined =
    tags.length > 0 || revalidate !== undefined
      ? {
          ...(tags.length > 0 ? { tags } : {}),
          ...(revalidate !== undefined ? { revalidate } : {}),
        }
      : undefined

  const res = await fetch(`${API}${path}`, {
    next,
    headers: { Accept: 'application/json' },
  })
  if (!res.ok) throw new Error(`API ${res.status}: ${path}`)
  return res.json()
}

async function post<T>(path: string, body?: any): Promise<T> {
  const res = await fetch(`${API}${path}`, {
    method: 'POST',
    headers: body !== null ? {Accept: 'application/json', 'Content-Type': 'application/json'} : { Accept: 'application/json' },
    body: body !== undefined ? JSON.stringify(body) : undefined,
  })
  if (!res.ok) throw new Error(`API ${res.status}: ${path}`)
  return res.json()
}

export function storageUrl(path: string | null | undefined): string | null {
  if (!path) return null
  if (path.startsWith('http')) return path
  return `${STORAGE}/${path}`
}

// ── Slides ────────────────────────────────────────────────────────────────

export const getSlides = () =>
  get<Slide[]>('/slides', [], 60)


// ── Channels ──────────────────────────────────────────────────────────────────

export const getChannels = (f: ChannelFilters = {}) =>
  get<Paginated<Channel>>(
    `/channels${qs(f as Record<string, unknown>)}`,
    cacheTags('channels')
  )

export const getFeaturedChannels = () =>
  get<Channel[]>('/channels/featured', cacheTags('channels', 'channels:featured'))

export const getTrendingChannels = () =>
  get<Channel[]>('/channels/trending', cacheTags('channels', 'channels:trending'))

export const getWatchingNow = () =>
  get<Channel[]>('/channels/watching-now', [], 60)

export const getFiltersMeta = () =>
  get<FiltersMeta>('/channels/filters/meta', cacheTags('channels', 'channels:filters-meta'))

export const getChannel = (slug: string | string) =>
  get<Channel>(`/channels/${slug}`, cacheTags('channels', `channel:${slug}`), 60)

export const getChannelsByIds = (ids: Array<string | number>) =>
  get<Channel[]>(`/channels/favorites${qs({ ids })}`)

export const trackChannel = (id: number | string, token: string) =>
  post<{ viewers: number }>(`/channels/${id}/track`, {viewer_token: token})

// ── Categories ────────────────────────────────────────────────────────────────

export const getCategories = () =>
  get<Category[]>('/categories', cacheTags('categories'))

export const getCategory = (slug: number | string) =>
  get<Category>(`/categories/${slug}`, cacheTags('categories', `category:${slug}`))

export const getCategoryChannels = (
  id: number | string,
  filters: Omit<ChannelFilters, 'category'> = {}
) => get<{ category: Category; channels: Paginated<Channel> }>(
  `/categories/${id}/channels${qs(filters as Record<string, unknown>)}`,
  cacheTags('categories', 'channels', `category:${id}`, `category:${id}:channels`)
)

// ── Tags ──────────────────────────────────────────────────────────────────────

export const getTags = () =>
  get<Tag[]>('/tags', cacheTags('tags'))

export const getTag = (id: number | string) =>
  get<Tag>(`/tags/${id}`, cacheTags('tags', `tag:${id}`))

export const getTagChannels = (
  id: number | string,
  filters: Omit<ChannelFilters, 'tag'> = {}
) => get<{ tag: Tag; channels: Paginated<Channel> }>(
  `/tags/${id}/channels${qs(filters as Record<string, unknown>)}`,
  cacheTags('tags', 'channels', `tag:${id}`, `tag:${id}:channels`)
)

// ── Countries ─────────────────────────────────────────────────────────────────

export const getCountries = () =>
  get<Country[]>('/countries', cacheTags('countries'))

export const getCountryChannels = (
  id: number | string,
  filters: Omit<ChannelFilters, 'country'> = {}
) => get<{ country: Country; channels: Paginated<Channel> }>(
  `/countries/${id}/channels${qs(filters as Record<string, unknown>)}`,
  cacheTags('countries', 'channels', `country:${id}:channels`)
)

// ── Report Video ──────────────────────────────────────────────────────────────

export const reportVideo = (body: any) =>
  post(`/video-reports`, body)

// ── Search ────────────────────────────────────────────────────────────────────

export const searchAll = (q: string, perPage = 10) =>
  fetch(`${API}/search?q=${encodeURIComponent(q)}&per_page=${perPage}`, {
    cache: 'no-store',
    headers: { Accept: 'application/json' },
  }).then(res => {
    if (!res.ok) throw new Error(`API ${res.status}: /search`)
    return res.json() as Promise<SearchResults>
  })