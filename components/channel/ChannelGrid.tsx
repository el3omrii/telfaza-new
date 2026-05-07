import type { Channel } from '@/types'
import { ChannelCard } from './ChannelCard'

interface Props {
  channels: Channel[]
  emptyMessage?: string
}

export function ChannelGrid({ channels, emptyMessage = 'No channels found.' }: Props) {
  if (channels.length === 0) {
    return (
      <div className="flex min-h-[200px] items-center justify-center text-sm text-zinc-500">
        {emptyMessage}
      </div>
    )
  }

  return (
    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
      {channels.map(ch => (
        <ChannelCard key={ch.id} channel={ch} />
      ))}
    </div>
  )
}
