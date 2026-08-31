import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import { getChannel } from '@/lib/api'
import { SITE_NAME } from '@/lib/seo'
import ClientChannelPlayer from '@/components/channel/ClientChannelPlayer'
interface Props {
  params: Promise<{ slug: string }>
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params
  try {
    const channel = await getChannel(slug + '?embed=1')
    return {
      title: `Watch ${channel.name} Live Streaming Online | ${SITE_NAME}`,
      description: channel.metadescription || `Watch ${channel.name} live on Telfaza LIVE.`,
      robots: 'index, follow',
      referrer: 'no-referrer',
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
    channel = await getChannel(slug + '?embed=1')
  } catch {
    notFound()
  }

  return (
    <div className="w-full h-full bg-black flex items-center justify-center overflow-hidden fixed inset-0">
      <ClientChannelPlayer channel={channel} />
    </div>
  )
}

// Disable the app layout for this page
export const dynamic = 'force-dynamic'
export const revalidate = 0
