import type { Metadata } from "next";
import Link from "next/link";
import { buildMetadata } from "@/lib/seo";

export const metadata: Metadata = buildMetadata({
  title: "About Us",
  description:
    "Learn about Telfaza LIVE and our goal of making Arabic live TV easy to explore and watch.",
  path: "/about",
});

const features = [
  {
    number: "01",
    title: "A world of channels",
    description:
      "Explore live Arabic TV across news, sports, entertainment, drama, and more.",
  },
  {
    number: "02",
    title: "Find your favorites",
    description:
      "Browse by country, category, or tag to get to the channels you want to watch.",
  },
  {
    number: "03",
    title: "Simple, direct viewing",
    description:
      "Discover available streams and watch right in your browser, with no signup required.",
  },
];

export default function AboutPage() {
  return (
    <main className="relative flex-1 overflow-hidden px-6 py-16 md:px-16 md:py-24 lg:px-24 xl:px-32">
      <div className="pointer-events-none absolute -top-32 left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-[#e8490f]/10 blur-3xl" />

      <div className="relative mx-auto max-w-6xl">
        <section className="max-w-3xl">
          <p className="mb-4 inline-flex items-center gap-2 rounded-full border border-[#e8490f]/30 bg-[#e8490f]/10 px-4 py-2 text-xs font-semibold tracking-[0.18em] text-[#ff875f]">
            <span className="h-1.5 w-1.5 rounded-full bg-[#e8490f]" />
            ABOUT TELFAZA LIVE
          </p>
          <h1 className="text-4xl font-bold tracking-tight text-white sm:text-5xl">
            Arabic TV, all in one place.
          </h1>
          <p className="mt-5 max-w-2xl text-base leading-7 text-gray-400 sm:text-lg">
            Telfaza LIVE makes it easy to discover live TV from across the Arab
            world. Find news, sports, entertainment, and more, then watch
            directly in your browser.
          </p>
          <div className="mt-8 flex flex-wrap gap-3">
            <Link
              className="inline-flex items-center justify-center rounded-xl bg-[#e8490f] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#f15a22] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ff875f]"
              href="/browse"
            >
              Explore channels
              <span aria-hidden="true" className="ml-2">
                →
              </span>
            </Link>
            <Link
              className="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ff875f]"
              href="/contact"
            >
              Get in touch
            </Link>
          </div>
        </section>

        <section className="mt-16 border-t border-white/[0.08] pt-10 sm:mt-20 sm:pt-12">
          <div className="mb-8 max-w-xl">
            <p className="text-xs font-semibold tracking-[0.18em] text-[#ff875f]">
              WHAT WE&apos;RE ABOUT
            </p>
            <h2 className="mt-3 text-2xl font-semibold tracking-tight text-white sm:text-3xl">
              Made for finding something to watch.
            </h2>
          </div>

          <div className="grid gap-4 md:grid-cols-3">
            {features.map((feature) => (
              <article
                className="rounded-2xl border border-white/[0.08] bg-white/[0.03] p-6 sm:p-7"
                key={feature.number}
              >
                <p className="text-sm font-semibold tracking-[0.14em] text-[#ff875f]">
                  {feature.number}
                </p>
                <h3 className="mt-5 text-lg font-semibold text-white">
                  {feature.title}
                </h3>
                <p className="mt-3 text-sm leading-6 text-gray-400">
                  {feature.description}
                </p>
              </article>
            ))}
          </div>
        </section>
      </div>
    </main>
  );
}
