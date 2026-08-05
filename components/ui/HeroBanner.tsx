
import Link from "next/link";

export function HeroBanner() {
  return (
    <div className="relative isolate px-6 pt-14 lg:px-8">
    <div aria-hidden="true" className="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">      
    </div>
    <div className="mx-auto max-w-3xl py-16 sm:py-24 lg:py-32">
      <div className="hidden sm:mb-8 sm:flex sm:justify-center">
        <div className="relative rounded-full px-3 py-1 text-sm/6 text-gray-400 ring-1 ring-white/10 hover:ring-white/20">
          100+ Free Live Channels • No Signup Required 
        </div>
      </div>
      <div className="text-center">
        <h1 className="text-5xl font-semibold tracking-tight text-balance sm:text-7xl text-white">Watch Free Live Arabic TV Channels Online</h1>
        <p className="mt-8 text-lg font-medium text-pretty sm:text-xl/8 text-gray-400">Stream news, drama, sports, and movies' channels directly in your browser with no subscription required.</p>
        <div className="mt-10 flex items-center justify-center gap-x-6">
          <Link href="/browse" className="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500">Browse channels</Link>
          <Link href="/categories" className="px-4 py-2 rounded-lg border border-white/20 bg-white/5 text-white hover:bg-white/10 transition">Categories <span aria-hidden="true">→</span></Link>
        </div>
      </div>
    </div>
  </div>
  );
}
