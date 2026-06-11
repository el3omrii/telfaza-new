import Link from "next/link"
import { Suspense } from "react";
import { getFeaturedChannels, getCountries } from '@/lib/api'
import { buildMetadata } from '@/lib/seo'

import HeroCarousel from "../components/HeroCarousel";
import { ChannelGrid } from '@/components/channel/ChannelGrid'
import { CountryGrid } from '@/components/country/CountryGrid'

export const metadata = buildMetadata({
  title: 'Home',
  description: 'Watch live TV channels and discover featured stations, countries, categories, and tags.',
  path: '/',
})

export default async function Home() {
  const featured = await getFeaturedChannels()
  const countries = await getCountries()

  return (
    <div>
      <Suspense fallback={<div className="h-[560px] bg-slate-900 animate-pulse" />}>
        <HeroCarousel />
      </Suspense>
      {/* ── Featured ── */}
      <main className="max-w-7xl mx-auto px-6 md:px-16 lg:px-24 xl:px-32">
        <section className="mt-8">
          <SectionHead title="✦ Featured Channels" href="/channels" />
          <ChannelGrid channels={featured} />
        </section>

        {/* ── Countries ── */}
        <section className="mt-8">
          <SectionHead title="✦ TV By Country" href="/channels?featured=1" />
          <CountryGrid countries={countries} />
        </section>
      </main>
    </div>

  );
}
function SectionHead({ title, href }: { title: string; href: string }) {
  return (
    <div className="relative mb-8 flex items-baseline justify-between">
      <h2 className="font-head text-2xl font-bold tracking-tight pb-4 border-b border-gray-700 after:content-[''] 
           after:absolute after:bottom-0 after:left-0 
           after:h-1.5 after:w-32 
           after:bg-red-500 ">{title}</h2>
      <Link href={href} className="text-xs text-zinc-500 transition-colors hover:text-teal-400">
        View all →
      </Link>
    </div>
  )
}