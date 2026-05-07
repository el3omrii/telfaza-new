"use client";
import Link from "next/link";
import { useRouter } from 'next/navigation'
import { useDebounce } from '@/hooks/useDebounce'
import React, { useState } from "react"

const NAV_LINKS = [
    { href: '/',           label: 'Home'       },
    { href: '/channels',   label: 'Channels'   },
    { href: '/categories', label: 'Categories' },
    { href: '/tags',       label: 'Tags'       },
];

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

/* ─── Navbar ───────────────────────────────────────────────────────── */
function Navbar({ menuOpen, setMenuOpen }: {menuOpen: boolean, setMenuOpen: React.Dispatch<React.SetStateAction<boolean>>}) {
  const router = useRouter()
  const [q, setQ] = useState('')
  const debounced = useDebounce(q, 400)
 
  function handleKey(e: React.KeyboardEvent) {
    if (e.key === 'Enter' && q.trim()) {
      router.push(`/search?q=${encodeURIComponent(q.trim())}`)
      setQ('')
    }
  }
  return (
    <nav
      className="fixed top-0 inset-x-0 z-50 h-16 flex items-center px-4 md:px-8 gap-4"
      style={{
        background:
          "linear-gradient(to bottom, rgba(10,10,15,0.97) 0%, rgba(10,10,15,0.75) 70%, transparent 100%)",
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
      <div className="font-black text-2xl tracking-widest select-none" style={{ fontFamily: "system-ui, sans-serif" }}>
        <span className="text-white">Telfaza</span>
        <span
          className="px-1.5 py-0.5 rounded-md text-[#e8490f]"
          style={{ background: "rgba(232,73,15,0.15)", border: "1px solid rgba(232,73,15,0.3)" }}
        >
          LIVE
        </span>
      </div>

      {/* Desktop nav links */}
      <ul className="hidden lg:flex items-center gap-0.5 ml-2">
        {NAV_LINKS.map((item, i) => (
          <li key={item.label}>
            <Link
              href={item.href}
              className="px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors"
              style={{
                color: i === 0 ? "#e8490f" : "rgba(255,255,255,0.55)",
                fontFamily: "system-ui, sans-serif",
              }}
              onMouseEnter={(e: React.MouseEvent<HTMLAnchorElement>) => { if (i !== 0) (e.target as HTMLElement).style.color = "#fff"; }}
              onMouseLeave={(e: React.MouseEvent<HTMLAnchorElement>) => { if (i !== 0) (e.target as HTMLElement).style.color = "rgba(255,255,255,0.55)"; }}
            >
              {item.label}
            </Link>
          </li>
        ))}
      </ul>

      {/* Spacer */}
      <div className="flex-1" />

      {/* Search bar */}
      <div
        className="hidden sm:flex items-center gap-2 px-3 py-2 rounded-xl text-white/50"
        style={{ background: "rgba(255,255,255,0.07)", border: "1px solid rgba(255,255,255,0.1)", minWidth: 180 }}
      >
        <Icon.Search />
        <input
          className="bg-transparent text-sm text-white placeholder-white/40 outline-none w-full"
          type="text"
          value={q}
          onChange={e => setQ(e.target.value)}
          onKeyDown={handleKey}
          placeholder="Search channels…"
        />
      </div>

      {/* Actions */}
      <div className="hidden sm:flex items-center gap-1.5">
        <button
          className="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-sm text-white/60 hover:text-white transition-colors font-semibold"
          style={{ background: "rgba(255,255,255,0.07)", border: "1px solid rgba(255,255,255,0.1)" }}
        >
          <Icon.Filter /><span className="text-xs tracking-wide">FILTER</span>
        </button>
        {[<Icon.Users />, <Icon.Shuffle />].map((ic, i) => (
          <button
            key={i}
            className="p-2 rounded-xl text-white/50 hover:text-white transition-colors"
            style={{ background: "rgba(255,255,255,0.07)", border: "1px solid rgba(255,255,255,0.1)" }}
          >
            {ic}
          </button>
        ))}
        {/* Language */}
        <div className="flex rounded-xl overflow-hidden" style={{ border: "1px solid rgba(255,255,255,0.1)" }}>
          <button
            className="px-3 py-1.5 text-xs font-bold text-white"
            style={{ background: "#e8490f", boxShadow: "0 0 16px rgba(232,73,15,0.45)" }}
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
        style={{ background: "rgba(255,255,255,0.07)", border: "1px solid rgba(255,255,255,0.1)" }}
      >
        <Icon.User />
      </button>
    </nav>
  );
}

/* ─── Mobile drawer ────────────────────────────────────────────────── */
function MobileMenu({ open, onClose }: {open: boolean, onClose: () => void}) {
  return (
    <div
      className="fixed inset-0 z-40 lg:hidden transition-all duration-200"
      style={{
        pointerEvents: open ? "auto" : "none",
        opacity: open ? 1 : 0,
        background: "rgba(10,10,15,0.97)",
        backdropFilter: "blur(16px)",
      }}
    >
      <div className="pt-20 px-6 flex flex-col gap-1">
        {NAV_LINKS.map((item, i) => (
          <Link
            key={item.label}
            href={item.href}
            onClick={onClose}
            className="block px-4 py-3 rounded-xl text-base font-semibold transition-colors"
            style={{
              fontFamily: "system-ui, sans-serif",
              color: i === 0 ? "#e8490f" : "rgba(255,255,255,0.7)",
              background: i === 0 ? "rgba(232,73,15,0.1)" : "transparent",
            }}
          >
            {item.label}
          </Link>
        ))}
        <div
          className="mt-4 flex items-center gap-2 px-4 py-3 rounded-xl"
          style={{ background: "rgba(255,255,255,0.07)", border: "1px solid rgba(255,255,255,0.1)" }}
        >
          <Icon.Search />
          <input
            className="bg-transparent text-sm text-white placeholder-white/40 outline-none flex-1"
            placeholder="Search anime..."
          />
        </div>
      </div>
    </div>
  );
}

export default function Nav() {
  const [menuOpen, setMenuOpen] = useState(false);

  return (
    <>
      <Navbar menuOpen={menuOpen} setMenuOpen={setMenuOpen} />
      <MobileMenu open={menuOpen} onClose={() => setMenuOpen(false)} />
    </>
  );
}