'use client'

import { useRouter, usePathname, useSearchParams } from 'next/navigation'
import type { Country, Category } from '@/types'

export function CountrySelect({ countries }: { countries: Country[] }) {
  const router = useRouter()
  const pathname = usePathname()
  const searchParams = useSearchParams()
  const current = searchParams.get('country') ?? ''

  function onChange(e: React.ChangeEvent<HTMLSelectElement>) {
    const params = new URLSearchParams(searchParams.toString())
    if (e.target.value) {
      params.set('country', e.target.value)
    } else {
      params.delete('country')
    }
    params.delete('page')
    router.push(`${pathname}?${params.toString()}`)
  }

  return (
    <select
      value={current}
      onChange={onChange}
      className="rounded-lg border border-white/[0.07] bg-zinc-900 px-3 py-1.5 text-xs text-zinc-400 focus:outline-none"
    >
      <option value="">All Countries</option>
      {countries.map(c => (
        <option key={c.id} value={c.id}>{c.name}</option>
      ))}
    </select>
  )
}

export function LanguageSelect({ languages }: { languages: string[] }) {
  const router = useRouter()
  const pathname = usePathname()
  const searchParams = useSearchParams()
  const current = searchParams.get('language') ?? ''

  function onChange(e: React.ChangeEvent<HTMLSelectElement>) {
    const params = new URLSearchParams(searchParams.toString())
    if (e.target.value) {
      params.set('language', e.target.value)
    } else {
      params.delete('language')
    }
    params.delete('page')
    router.push(`${pathname}?${params.toString()}`)
  }

  return (
    <select
      value={current}
      onChange={onChange}
      className="rounded-lg border border-white/[0.07] bg-zinc-900 px-3 py-1.5 text-xs text-zinc-400 focus:outline-none"
    >
      <option value="">All Languages</option>
      {languages.map(lang => (
        <option key={lang} value={lang}>{lang}</option>
      ))}
    </select>
  )
}

export function CategorySelect({ categories }: { categories: Category[] }) {
  const router = useRouter()
  const pathname = usePathname()
  const searchParams = useSearchParams()
  const current = searchParams.get('category') ?? ''

  function onChange(e: React.ChangeEvent<HTMLSelectElement>) {
    const params = new URLSearchParams(searchParams.toString())
    if (e.target.value) {
      params.set('category', e.target.value)
    } else {
      params.delete('category')
    }
    params.delete('page')
    router.push(`${pathname}?${params.toString()}`)
  }

  return (
    <select
      value={current}
      onChange={onChange}
      className="rounded-lg border border-white/[0.07] bg-zinc-900 px-3 py-1.5 text-xs text-zinc-400 focus:outline-none"
    >
      <option value="">All Categories</option>
      {categories.map(c => (
        <option key={c.id} value={c.id}>{c.name}</option>
      ))}
    </select>
  )
}
