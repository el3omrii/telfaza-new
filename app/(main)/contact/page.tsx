import type { Metadata } from "next";
import ContactForm from "@/components/ui/ContactForm";
import { buildMetadata } from "@/lib/seo";

export const metadata: Metadata = buildMetadata({
  title: "Contact Us",
  description: "Get in touch with the Telfaza LIVE team.",
  path: "/contact",
});

export default function ContactPage() {
  return (
    <main className="relative flex-1 overflow-hidden px-6 py-16 md:px-16 md:py-24 lg:px-24 xl:px-32">
      <div className="pointer-events-none absolute -top-32 left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-[#e8490f]/10 blur-3xl" />

      <div className="relative mx-auto max-w-6xl">
        <div className="mb-12 max-w-2xl">
          <p className="mb-4 inline-flex items-center gap-2 rounded-full border border-[#e8490f]/30 bg-[#e8490f]/10 px-4 py-2 text-xs font-semibold tracking-[0.18em] text-[#ff875f]">
            <span className="h-1.5 w-1.5 rounded-full bg-[#e8490f]" />
            WE&apos;RE HERE TO HELP
          </p>
          <h1 className="text-4xl font-bold tracking-tight text-white sm:text-5xl">
            Let&apos;s get in touch.
          </h1>
          <p className="mt-5 max-w-xl text-base leading-7 text-gray-400 sm:text-lg">
            Have a question, suggestion, or something we should know? Send us a
            message and we&apos;ll be happy to hear from you.
          </p>
        </div>

        <div className="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:gap-12">
          <aside className="flex flex-col justify-between rounded-2xl border border-white/[0.08] bg-white/[0.03] p-7 sm:p-9">
            <div>
              <div className="mb-8 flex h-14 w-14 items-center justify-center rounded-2xl border border-[#e8490f]/25 bg-[#e8490f]/10 text-[#ff875f]">
                <svg
                  aria-hidden="true"
                  className="h-7 w-7"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  strokeWidth={1.6}
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h12a2.25 2.25 0 0 1 2.25 2.25v10.5A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25V6.75Z"
                  />
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="m4.5 6 7.5 6 7.5-6"
                  />
                </svg>
              </div>
              <h2 className="text-2xl font-semibold text-white">
                We&apos;d love to hear from you
              </h2>
              <p className="mt-3 leading-7 text-gray-400">
                Share your question or feedback using the form. Your message
                goes straight to our team.
              </p>
            </div>

            <div className="mt-10 border-t border-white/[0.08] pt-6">
              <p className="text-xs font-semibold tracking-[0.16em] text-[#ff875f]">
                HERE TO HELP
              </p>
              <p className="mt-2 text-sm leading-6 text-gray-400">
                We usually reply as soon as we can.
              </p>
            </div>
          </aside>

          <ContactForm />
        </div>
      </div>
    </main>
  );
}
