import Link from 'next/link'
import type { Country } from '@/types'
import { CountryCard } from './CountryCard'

interface Props {
  countries: Country[]
  emptyMessage?: string
  showViewAllButton?: boolean
}

export function HomeCountryGrid({ countries, emptyMessage = 'No Countries found.', showViewAllButton = true }: Props) {
  if (countries.length === 0) {
    return (
      <div className="flex min-h-[200px] items-center justify-center text-sm text-zinc-500">
        {emptyMessage}
      </div>
    )
  }

  // Limit to 9 countries and add view all button as 10th element
  const limitedCountries = countries.slice(0, 7)
  const hasMoreCountries = countries.length > 7

  return (
    <div className="grid sm:grid-cols-2 gap-3 md:gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
      {limitedCountries.map(c => (
        <CountryCard key={c.id} country={c} />
      ))}

      {(hasMoreCountries && showViewAllButton) && (
        <Link
          href="/countries"
          className="group relative flex flex-col overflow-hidden rounded-xl border border-white/[0.07] bg-zinc-900 transition-all duration-150 hover:-translate-y-0.5 hover:border-white/[0.12] items-center justify-center"
        >
          <div className="relative aspect-video w-full overflow-hidden bg-zinc-800 flex items-center justify-center">
            <div className="absolute inset-0 bg-gradient-to-br from-zinc-700 to-zinc-900" />
            <div className="relative z-10 flex flex-col items-center gap-2">
              <svg className="w-8 h-8 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16l-4-4m0 0l4-4m-4 4h18" />
              </svg>
              <div className="text-white/90 text-sm font-medium">View All</div>
            </div>
          </div>
        </Link>
      )}
    </div>
  )
}
