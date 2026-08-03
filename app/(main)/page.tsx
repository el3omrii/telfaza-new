import Link from "next/link"
import { Suspense } from "react";
import { getFeaturedChannels, getCountries } from '@/lib/api'
import { buildMetadata } from '@/lib/seo'

import HeroCarousel from "@/components/HeroCarousel";
import { FeaturedChannelsSlider } from '@/components/channel/FeaturedChannelsSlider'
import { HomeCountryGrid } from '@/components/country/HomeCountryGrid'
import { WatchingNowSection } from '@/components/channel/WatchingNowSection'
import { SectionHead } from '@/components/ui/SectionHead'

export const metadata = buildMetadata({
  title: 'Telfaza Live',
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
      <main className="max-w-7xl mx-auto px-6 md:px-16 lg:px-24 xl:px-32">
      {/* -- Watching Now -- */}
      <WatchingNowSection />

      {/* ── Featured ── */}
        <section className="mt-8">
          <SectionHead title="✦ Featured Channels" href="/channels" />
          <FeaturedChannelsSlider channels={featured} />
        </section>

        {/* ── Countries ── */}
        <section className="mt-8">
          <SectionHead title="✦ TV By Country" href="/channels?featured=1" />
          <HomeCountryGrid countries={countries} />
        </section>
      </main>
    </div>

  );
}