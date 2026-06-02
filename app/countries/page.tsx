import type { Metadata } from 'next'
import Link from 'next/link'
import { getCountries } from '@/lib/api'
import { CountryGrid } from '@/components/country/CountryGrid'

export const metadata: Metadata = { title: 'Countries' }

export default async function CountriesPage() {
  const countries = await getCountries()

  return (
    <main className="max-w-7xl w-full mx-auto md:mt-16 px-6 md:px-12">
      <div className="border-b border-white/[0.07] px-5 py-8">
        <h1 className="font-head text-3xl font-extrabold tracking-tight">Countries</h1>
        <p className="mt-1 text-sm text-zinc-500">
          {countries.length} countries · browse by country
        </p>
      </div>

      <div className="mt-8">
        <CountryGrid countries={countries} />
      </div>
    </main>
  )
}
