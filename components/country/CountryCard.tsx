import Link from 'next/link'
import Image from 'next/image'
import type { Country } from '@/types'
import { getCountries } from '@/lib'

//import { Eye } from 'lucide-react'

interface Props {
  country: Country
}

export function CountryCard({ country }: Props) {
  const logo = "https://flagcdn.com/"+country.flag.toLowerCase()+".svg"
  
   /* ─── icons ────────────────────────────────────────────────────────── */
const Icon = {
  Eye: () => (
    <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
      <polygon points="5 3 19 12 5 21 5 3" />
    </svg>
  ),
}
  return (
    <Link
      href={`/countries/${country.id}`}
      className="group relative flex flex-col overflow-hidden rounded-xl border border-white/[0.07] bg-zinc-900 transition-all duration-150 hover:-translate-y-0.5 hover:border-white/[0.12]"
    >
      {/* Thumbnail */}
      <div className="relative aspect-video w-full overflow-hidden bg-zinc-800
                      after:content[''] after:absolute after:h-full after:w-full after:bg-gradient-to-b after:from-transparent after:to-black">
        {logo ? (
          <Image src={logo} alt={country.name} fill className="object-cover" sizes="240px" />
        ) : (
          <div className="flex h-full items-center justify-center">
            <span className="font-head text-3xl font-black tracking-tighter text-white/10">
              {country.name.slice(0, 3).toUpperCase()}
            </span>
          </div>
        )}
      </div>
      {/* Body */}
      <div className="absolute bottom-0 right-0 left-0 -mx-[0.4px] rounded-b-xl px-4 py-2 opacity-80 text-gray-100 text-center font-bold text-lg">
        <div className="flex">
          <div></div>
          <div className="grow truncate text-lg font-medium text-zinc-100">{country.name}</div>
            <span className="flex items-center gap-1 text-[11px] text-zinc-500">
              # {country.channels_count}
            </span>
          </div>
          
        </div>
    </Link>
  )
}
