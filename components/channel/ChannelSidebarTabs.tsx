"use client"

import Image from 'next/image'
import Link from 'next/link'
import { useState } from 'react'
import type { Channel } from '@/types'
import { storageUrl } from '@/lib/api'
import { fmtViews } from '@/lib/utils'

interface Props {
  related: Channel[]
  topWatched: Channel[]
}

export default function ChannelSidebarTabs({ related, topWatched }: Props) {
  const [activeTab, setActiveTab] = useState<'related' | 'topWatched'>('related')
  const channels = activeTab === 'related' ? related : topWatched

  return (
    <div>
      <div className="mb-4 flex border-b border-white/[0.07]" role="tablist" aria-label="Channel lists">
        <TabButton
          active={activeTab === 'related'}
          label="Related"
          onClick={() => setActiveTab('related')}
        />
        <TabButton
          active={activeTab === 'topWatched'}
          label="Top Watched"
          onClick={() => setActiveTab('topWatched')}
        />
      </div>

      <div role="tabpanel" aria-live="polite">
        {channels.length > 0 ? (
          <div className="space-y-2">
            {channels.map(channel => (
              <Link
                key={channel.id}
                href={`/channels/${channel.slug}`}
                className="flex items-center gap-3 rounded-lg p-2 transition-all hover:bg-zinc-800"
              >
                <div className="relative h-18 w-24 shrink-0 overflow-hidden rounded-lg border border-white/25">
                  <Image
                    src={storageUrl(channel.image) || '/placeholder-channel.jpg'}
                    alt={channel.name}
                    fill
                    className="rounded-lg object-cover"
                  />
                </div>
                <div className="min-w-0">
                  <p className="truncate text-xs font-medium text-zinc-100">{channel.name}</p>
                  <p className="text-[11px] text-zinc-500">{fmtViews(channel.views)} views</p>
                </div>
              </Link>
            ))}
          </div>
        ) : (
          <p className="py-4 text-sm text-zinc-500">No channels available.</p>
        )}
      </div>
    </div>
  )
}

function TabButton({
  active,
  label,
  onClick,
}: {
  active: boolean
  label: string
  onClick: () => void
}) {
  return (
    <button
      type="button"
      role="tab"
      aria-selected={active}
      onClick={onClick}
      className={`border-b-2 px-2 pb-2 text-left text-xs font-bold uppercase tracking-widest transition-colors ${
        active
          ? 'border-lime-400 text-lime-400'
          : 'border-transparent text-zinc-500 hover:text-zinc-300'
      }`}
    >
      {label}
    </button>
  )
}