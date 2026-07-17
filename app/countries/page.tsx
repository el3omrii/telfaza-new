import Link from 'next/link'
import { getCountries } from '@/lib/api'
import { CountryGrid } from '@/components/country/CountryGrid'
import { buildMetadata } from '@/lib/seo'

export const metadata = buildMetadata({
  title: 'Countries',
  description: 'Browse live TV channels by country and region.',
  path: '/countries',
})

export default async function CountriesPage() {
  const countries = await getCountries()

  return (
    <main className="max-w-7xl w-full mx-auto mt-16 px-6 md:px-16 lg:px-24 xl:px-32">
      <div className="border-b border-white/[0.07] px-5 py-8">
        <h1 className="font-head text-3xl font-extrabold tracking-tight">Countries</h1>
        <p className="mt-1 text-sm text-zinc-500">
          {countries.length} countries · browse by country
        </p>
      </div>

      <div className="mt-8">
        <CountryGrid countries={countries} showViewAllButton={false} />
      </div>
    </main>
  )
}
