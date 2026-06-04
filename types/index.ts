export interface Slide {
  id: number
  title: string
  type: string
  genre: string[]
  description: string
  rating: string
  release: string
  quality: string
  image: string
  accent: string
}
export interface Country {
  id: number
  name: string
  flag: string
  channels_count?: number
}

export interface Category {
  id: number
  name: string
  description: string | null
  color: string
  channels_count?: number
}

export interface Tag {
  id: number
  name: string
  channels_count?: number
}

export interface Source {
  id: number
  type: string
  link: string
  drm: boolean
  clearkeys: Record<string, string> | null
}

export interface Channel {
  id: number
  name: string
  description: string | null
  logo: string | null
  image: string | null
  language: string | null
  quality: string | null
  epgid: string | null
  featured: boolean
  views: number
  country_id: number
  sources_count?: number
  active_viewers?: number
  country?: Country
  categories?: Category[]
  tags?: Tag[]
  sources?: Source[]
  created_at: string
  updated_at: string
}

export interface PaginationMeta {
  current_page: number
  from: number | null
  last_page: number
  per_page: number
  to: number | null
  total: number
  path: string
}

export interface PaginationLinks {
  first: string | null
  last: string | null
  prev: string | null
  next: string | null
}

export interface Paginated<T> {
  data: T[]
  links: PaginationLinks
  meta: PaginationMeta
}

export interface SearchResults {
  query: string
  channels: Channel[]
  categories: Category[]
  tags: Tag[]
}

export type SortField = 'views' | 'name' | 'created_at'
export type SortOrder = 'asc' | 'desc'
export type Quality   = '4K' | '1080p' | '720p' | '480p' | '360p'

export interface ChannelFilters {
  search?: string
  category?: number | number[]
  tag?: number | number[]
  country?: number
  language?: string
  quality?: Quality
  featured?: boolean
  sort?: SortField
  order?: SortOrder
  per_page?: number
  page?: number
}

export interface FiltersMeta {
  languages: string[]
  qualities: Quality[]
}