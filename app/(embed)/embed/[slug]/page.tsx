import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import { getChannel } from '@/lib/api'
import ChannelPlayer from '@/components/channel/ChannelPlayer'

interface Props {
  params: Promise<{ slug: string }>
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params
  try {
    const channel = await getChannel(slug)
    return {
      title: `${channel.name} - Embed`,
      description: `Watch ${channel.name} live on Telfaza LIVE.`,
    }
  } catch {
    return {
      title: 'Channel not found',
      description: 'The requested channel could not be found.',
    }
  }
}

export default async function EmbedPage({ params }: Props) {
  const { slug } = await params
  let channel

  try {
    channel = await getChannel(slug)
  } catch {
    notFound()
  }

  return (
    <div className="w-screen h-screen bg-black flex items-center justify-center overflow-hidden">
      <ChannelPlayer channel={channel} fullViewport={true} />
    </div>
  )
}

// Disable the app layout for this page
export const dynamic = 'force-dynamic'
export const revalidate = 0
