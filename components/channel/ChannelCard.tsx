import Link from 'next/link'
import Image from 'next/image'
import type { Channel } from '@/types'
import { fmtViews, storageUrl } from '@/lib'
//import { Eye } from 'lucide-react'

interface Props {
  channel: Channel
}

export function ChannelCard({ channel }: Props) {
  const logo = storageUrl(channel.image)
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
      href={`/channels/${channel.slug}`}
      className="group relative flex flex-col overflow-hidden rounded-xl border border-white/[0.07] bg-zinc-900 transition-all duration-150 hover:-translate-y-0.5 hover:border-white/[0.12]"
    >
      {/* Thumbnail */}
      <div className="relative aspect-video w-full overflow-hidden bg-zinc-800">
        {logo ? (
          <Image src={logo} alt={channel.name} fill className="object-cover" sizes="240px" />
        ) : (
          <div className="flex h-full items-center justify-center">
            <span className="font-head text-3xl font-black tracking-tighter text-white/10">
              {channel.name.slice(0, 3).toUpperCase()}
            </span>
          </div>
        )}

        {/* Overlays */}
        {channel.featured && (
          <span className="absolute right-2 top-2 rounded bg-lime-400 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-zinc-950">
            Featured
          </span>
        )}
        {channel.quality && (
          <span className="absolute bottom-2 left-2 rounded border border-white/15 bg-black/70 px-1.5 py-0.5 text-[9px] font-medium text-zinc-400">
            {channel.quality}
          </span>
        )}
        {channel.active_viewers != null && channel.active_viewers > 0 && (
          <span className="absolute bottom-2 right-2 flex items-center gap-1 rounded border border-red-400/20 bg-black/70 px-1.5 py-0.5 text-[9px] font-medium text-red-400">
            <span className="animate-live inline-block h-1 w-1 rounded-full bg-red-400" />
            {channel.active_viewers}
          </span>
        )}
      </div>

      {/* Body */}
      <div className="px-3 py-2.5">
        <p className="truncate text-sm font-medium text-zinc-100">{channel.name}</p>
        <div className="mt-1 flex items-center justify-between">
          <span className="text-[11px] text-zinc-500">
            {channel.country?.flag} {channel.country?.name}
          </span>
          <span className="flex items-center gap-1 text-[11px] text-zinc-500">
            <Icon.Eye />
            {fmtViews(channel.views)}
          </span>
        </div>

        {/* Tags */}
        {channel.tags && channel.tags.length > 0 && (
          <div className="mt-2 flex flex-wrap gap-1">
            {channel.tags.slice(0, 2).map(t => (
              <span
                key={t.id}
                className="rounded bg-zinc-800 px-1.5 py-0.5 text-[10px] text-zinc-500"
              >
                #{t.name}
              </span>
            ))}
          </div>
        )}
      </div>
    </Link>
  )
}
