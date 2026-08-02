"use client";
import Link from "next/link";
import { usePathname, useRouter } from 'next/navigation'
import { useDebounce } from '@/hooks/useDebounce'
import React, { useEffect, useState } from "react"
import { searchAll, storageUrl } from '@/lib/api'
import type { Channel } from '@/types'
import { readFavorites } from "@/lib";

const NAV_LINKS = [
  { href: '/', label: 'Home' },
  { href: '/channels', label: 'Channels' },
  { href: '/categories', label: 'Categories' },
  { href: '/countries', label: 'Countries' },
  { href: '/tags', label: 'Tags' },
];

function isActivePath(pathname: string, href: string) {
  if (href === '/') return pathname === '/'
  return pathname === href || pathname.startsWith(`${href}/`)
}

/* ─── icons ────────────────────────────────────────────────────────── */
const Icon = {
  Search: () => (
    <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
      <circle cx="11" cy="11" r="8" /><path d="m21 21-4.35-4.35" />
    </svg>
  ),
  Filter: () => (
    <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
      <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
    </svg>
  ),
  Users: () => (
    <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" />
      <path d="M23 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />
    </svg>
  ),
  Shuffle: () => (
    <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
      <polyline points="16 3 21 3 21 8" /><line x1="4" y1="20" x2="21" y2="3" />
      <polyline points="21 16 21 21 16 21" /><line x1="4" y1="4" x2="9" y2="9" />
    </svg>
  ),
  Bookmark: () => (
    <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
      <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
    </svg>
  ),
  User: () => (
    <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />
    </svg>
  ),
  Menu: () => (
    <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
      <path d="M4 6h16M4 12h16M4 18h16" />
    </svg>
  ),
  Close: () => (
    <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
      <path d="M18 6L6 18M6 6l12 12" />
    </svg>
  ),
};

function SearchBox({
  variant = 'desktop',
  onSelect,
}: {
  variant?: 'desktop' | 'mobile'
  onSelect?: () => void
}) {
  const router = useRouter()
  const pathname = usePathname()
  const [query, setQuery] = useState('')
  const [results, setResults] = useState<Channel[]>([])
  const [loading, setLoading] = useState(false)
  const [open, setOpen] = useState(false)
  const debouncedQuery = useDebounce(query, 220)
  const trimmedQuery = query.trim()
  const trimmedDebouncedQuery = debouncedQuery.trim()

  useEffect(() => {
    setQuery('')
    setResults([])
    setOpen(false)
  }, [pathname])

  useEffect(() => {
    if (!trimmedDebouncedQuery) {
      setResults([])
      setLoading(false)
      return
    }

    let ignore = false
    setLoading(true)

    searchAll(trimmedDebouncedQuery, 6)
      .then((res) => {
        if (!ignore) {
          setResults(res.channels)
        }
      })
      .catch(() => {
        if (!ignore) {
          setResults([])
        }
      })
      .finally(() => {
        if (!ignore) {
          setLoading(false)
        }
      })

    return () => {
      ignore = true
    }
  }, [trimmedDebouncedQuery])

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    if (!trimmedQuery) return
    router.push(`/search?q=${encodeURIComponent(trimmedQuery)}`)
    setQuery('')
    setOpen(false)
    setResults([])
    onSelect?.()
  }

  function handleSelect(channel: Channel) {
    router.push(`/channels/${channel.slug}`)
    setQuery('')
    setOpen(false)
    setResults([])
    onSelect?.()
  }

  const showResults = open && (trimmedQuery.length > 0 || loading || results.length > 0)
  const inputClassName = variant === 'mobile'
    ? 'bg-transparent text-sm text-white placeholder-white/40 outline-none flex-1'
    : 'bg-transparent text-sm text-white placeholder-white/40 outline-none w-full'

  return (
    <div className="relative w-full">
      <form onSubmit={handleSubmit} className="w-full">
        <div
          className={`flex items-center gap-2 rounded-xl px-3 py-2 text-white/50 transition-all duration-200 ${
            open ? 'ring-1 ring-[#e8490f]/40 shadow-lg shadow-[#e8490f]/10' : ''
          }`}
          style={{
            background: 'rgba(255,255,255,0.07)',
            border: '1px solid rgba(255,255,255,0.1)',
          }}
        >
          <Icon.Search />
          <input
            className={inputClassName}
            type="text"
            value={query}
            onChange={(e) => {
              setQuery(e.target.value)
              setOpen(Boolean(e.target.value.trim()))
            }}
            onFocus={() => {
              if (query.trim()) setOpen(true)
            }}
            onBlur={() => {
              window.setTimeout(() => setOpen(false), 120)
            }}
            placeholder="Search channels..."
          />
          {query && (
            <button
              type="button"
              className="rounded-full p-1 text-white/50 transition hover:bg-white/10 hover:text-white"
              onMouseDown={(e) => e.preventDefault()}
              onClick={() => {
                setQuery('')
                setResults([])
                setOpen(false)
              }}
              aria-label="Clear search"
            >
              <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
                <path d="M18 6L6 18M6 6l12 12" />
              </svg>
            </button>
          )}
        </div>
      </form>

      {showResults && (
        <div className="absolute left-0 right-0 top-full z-[60] mt-2 overflow-hidden rounded-2xl border border-white/10 bg-zinc-950/95 shadow-2xl shadow-black/30 backdrop-blur-xl transition-all duration-200">
          {loading ? (
            <div className="px-3 py-3 text-sm text-zinc-400">
              <div className="mb-2 h-2 w-24 animate-pulse rounded bg-white/10" />
              <div className="space-y-2">
                {[0, 1].map((i) => (
                  <div key={i} className="flex items-center gap-3 rounded-lg px-2 py-2">
                    <div className="h-9 w-9 animate-pulse rounded-lg bg-white/10" />
                    <div className="flex-1 space-y-2">
                      <div className="h-3 w-24 animate-pulse rounded bg-white/10" />
                      <div className="h-2.5 w-16 animate-pulse rounded bg-white/10" />
                    </div>
                  </div>
                ))}
              </div>
            </div>
          ) : results.length > 0 ? (
            <div className="max-h-80 overflow-auto py-2">
              {results.map((channel) => {
                const logo = storageUrl(channel.logo)
                return (
                  <button
                    key={channel.id}
                    type="button"
                    onMouseDown={(e) => e.preventDefault()}
                    onClick={() => handleSelect(channel)}
                    className="flex w-full items-center gap-3 px-3 py-2 text-left transition hover:bg-white/10"
                  >
                    <div className="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg border border-white/10 bg-zinc-900">
                      {logo ? (
                        // eslint-disable-next-line @next/next/no-img-element
                        <img src={logo} alt={channel.name} className="h-full w-full object-contain" />
                      ) : (
                        <span className="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                          {channel.name.slice(0, 2)}
                        </span>
                      )}
                    </div>
                    <div className="min-w-0">
                      <p className="truncate text-sm font-medium text-white">{channel.name}</p>
                      <p className="text-xs text-zinc-500">Channel</p>
                    </div>
                  </button>
                )
              })}
              <Link
                href={`/search?q=${encodeURIComponent(trimmedQuery)}`}
                onClick={() => {
                  setQuery('')
                  setOpen(false)
                  setResults([])
                }}
                className="block border-t border-white/10 px-3 py-2 text-sm font-medium text-[#e8490f] transition hover:bg-white/10"
              >
                View all matches
              </Link>
            </div>
          ) : (
            <div className="px-3 py-3 text-sm text-zinc-400">
              No channels found for “{trimmedQuery}”.
            </div>
          )}
        </div>
      )}
    </div>
  )
}

/* ─── Navbar ───────────────────────────────────────────────────────── */
function Navbar({ menuOpen, setMenuOpen }: {menuOpen: boolean, setMenuOpen: React.Dispatch<React.SetStateAction<boolean>>}) {
  const pathname = usePathname()
  const [favs, setFavs] = useState<Array<string | number>>([])

  useEffect(() => {
    setFavs(readFavorites())
  }, [])

  return (
    <nav
      className="fixed top-0 inset-x-0 z-50 h-16 flex items-center px-4 md:px-8 gap-4"
      style={{
        background:
          'linear-gradient(to bottom, rgba(10,10,15,0.97) 0%, rgba(10,10,15,0.75) 70%, transparent 100%)',
      }}
    >
      {/* Hamburger (mobile) */}
      <button
        className="lg:hidden p-1.5 text-white/60 hover:text-white transition-colors"
        onClick={() => setMenuOpen((o: boolean) => !o)}
      >
        {menuOpen ? <Icon.Close /> : <Icon.Menu />}
      </button>

      {/* Logo */}
      <div className="font-black text-2xl tracking-widest select-none" style={{ fontFamily: 'system-ui, sans-serif' }}>
        <span className="text-white">Telfaza</span>
        <span
          className="px-1.5 py-0.5 rounded-md text-[#e8490f]"
          style={{ background: 'rgba(232,73,15,0.15)', border: '1px solid rgba(232,73,15,0.3)' }}
        >
          LIVE
        </span>
      </div>

      {/* Desktop nav links */}
      <ul className="hidden lg:flex items-center gap-0.5 ml-2">
        {NAV_LINKS.map((item) => {
          const active = isActivePath(pathname, item.href)
          return (
            <li key={item.label}>
              <Link
                href={item.href}
                className="px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors"
                style={{
                  color: active ? '#e8490f' : 'rgba(255,255,255,0.55)',
                  fontFamily: 'system-ui, sans-serif',
                  background: active ? 'rgba(232,73,15,0.12)' : 'transparent',
                }}
                onMouseEnter={(e: React.MouseEvent<HTMLAnchorElement>) => {
                  if (!active) (e.target as HTMLElement).style.color = '#fff'
                }}
                onMouseLeave={(e: React.MouseEvent<HTMLAnchorElement>) => {
                  if (!active) (e.target as HTMLElement).style.color = 'rgba(255,255,255,0.55)'
                }}
              >
                {item.label}
              </Link>
            </li>
          )
        })}
      </ul>

      {/* Spacer */}
      <div className="flex-1" />

      {/* Search bar */}
      <div className="hidden sm:block w-full max-w-[280px] xl:max-w-[340px]">
        <SearchBox />
      </div>

      {/* Actions */}
      <div className="hidden sm:flex items-center gap-1.5">
        <Link href="/browse"
          className="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-sm text-white/60 hover:text-white transition-colors font-semibold"
          style={{ background: 'rgba(255,255,255,0.07)', border: '1px solid rgba(255,255,255,0.1)' }}
        >
          <Icon.Filter /><span className="text-xs tracking-wide">FILTER</span>
        </Link>
        <Link href="/favorites"
          className="relative p-2 rounded-xl text-white/50 hover:text-white transition-colors"
          style={{ background: 'rgba(255,255,255,0.07)', border: '1px solid rgba(255,255,255,0.1)' }}
          title="Favorites"
        >
          <Icon.Bookmark />
          { favs.length > 0 && (
            <span className="absolute -top-2 -right-2 min-w-6 justify-center rounded-full bg-teal-400/80 px-2 py-0.5 text-[0.7rem] font-semibold text-white">
              { favs.length }
            </span>
          )}
        </Link>
        {[<Icon.Users />, <Icon.Shuffle />].map((ic, i) => (
          <button
            key={i}
            className="p-2 rounded-xl text-white/50 hover:text-white transition-colors"
            style={{ background: 'rgba(255,255,255,0.07)', border: '1px solid rgba(255,255,255,0.1)' }}
          >
            {ic}
          </button>
        ))}
        {/* Language */}
        <div className="flex rounded-xl overflow-hidden" style={{ border: '1px solid rgba(255,255,255,0.1)' }}>
          <button
            className="px-3 py-1.5 text-xs font-bold text-white"
            style={{ background: '#e8490f', boxShadow: '0 0 16px rgba(232,73,15,0.45)' }}
          >
            en
          </button>
          <button className="px-3 py-1.5 text-xs font-bold text-white/40 hover:text-white hover:bg-white/5 transition-colors">
            ar
          </button>
        </div>
      </div>

      <button
        className="p-2 rounded-xl text-white/50 hover:text-white transition-colors"
        style={{ background: 'rgba(255,255,255,0.07)', border: '1px solid rgba(255,255,255,0.1)' }}
      >
        <Icon.User />
      </button>
    </nav>
  )
}

/* ─── Mobile drawer ────────────────────────────────────────────────── */
function MobileMenu({ open, onClose }: {open: boolean, onClose: () => void}) {
  const pathname = usePathname()

  return (
    <div
      className="fixed inset-0 z-40 lg:hidden transition-all duration-200"
      style={{
        pointerEvents: open ? 'auto' : 'none',
        opacity: open ? 1 : 0,
        background: 'rgba(10,10,15,0.97)',
        backdropFilter: 'blur(16px)',
      }}
    >
      <div className="pt-20 px-6 flex flex-col gap-1">
        {NAV_LINKS.map((item) => {
          const active = isActivePath(pathname, item.href)
          return (
            <Link
              key={item.label}
              href={item.href}
              onClick={onClose}
              className="block px-4 py-3 rounded-xl text-base font-semibold transition-colors"
              style={{
                fontFamily: 'system-ui, sans-serif',
                color: active ? '#e8490f' : 'rgba(255,255,255,0.7)',
                background: active ? 'rgba(232,73,15,0.1)' : 'transparent',
              }}
            >
              {item.label}
            </Link>
          )
        })}
        <div className="mt-4">
          <SearchBox variant="mobile" onSelect={onClose} />
        </div>
      </div>
    </div>
  )
}

export default function Nav() {
  const [menuOpen, setMenuOpen] = useState(false)

  return (
    <>
      <Navbar menuOpen={menuOpen} setMenuOpen={setMenuOpen} />
      <MobileMenu open={menuOpen} onClose={() => setMenuOpen(false)} />
    </>
  )
}