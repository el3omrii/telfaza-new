import type { Country } from '@/types'
import { CountryCard } from './CountryCard'

interface Props {
  countries: Country[]
  emptyMessage?: string
}

export function CountryGrid({ countries, emptyMessage = 'No Countries found.' }: Props) {
  if (countries.length === 0) {
    return (
      <div className="flex min-h-[200px] items-center justify-center text-sm text-zinc-500">
        {emptyMessage}
      </div>
    )
  }

  return (
    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5">
      {countries.map(c => (
        <CountryCard key={c.id} country={c} />
      ))}
    </div>
  )
}
