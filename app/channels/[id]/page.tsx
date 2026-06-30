import type { Metadata } from 'next'
import Image from 'next/image'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import { getChannel, getChannels, storageUrl } from '@/lib/api'
import ClientChannelPlayer from '@/components/channel/ClientChannelPlayer'
import LiveViewerCount from '@/components/channel/LiveViewerCount'
import { fmtViews } from '@/lib/utils'
import { buildMetadata, generateVideoSchema } from '@/lib/seo'


interface Props {
  params: { id: string }
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { id } = await params
  try {
    const ch = await getChannel(id)
    return buildMetadata({
      title: ch.name,
      description: ch.description ?? `Watch ${ch.name} live on Telfaza LIVE.`,
      path: `/channels/${ch.slug}`,
    })
  } catch {
    return buildMetadata({
      title: 'Channel not found',
      description: 'The requested channel could not be found.',
      path: '/channels',
    })
  }
}

export default async function ChannelDetailPage({ params }: Props) {
  const { id } = await params
  let channel
  try {
    channel = await getChannel(id)
  } catch {
    notFound()
  }
  // Related: same first category, different channel
  const related = channel.categories?.[0]
    ? await getChannels({ category: channel.categories[0].id, per_page: 6, sort: 'views' })
        .then(r => r.data.filter(c => c.id !== channel.id).slice(0, 5))
    : []

  const logo = storageUrl(channel.logo)
  const flag = "https://flagcdn.com/"+channel.country?.flag.toLowerCase()+".svg"
  const Icon = {
    Eye: () => (
      <svg className="w-5 h-5" viewBox="0 0 24 24" stroke="currentColor"
        strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/>
      </svg>
    ),
  };
  const videoSchema = generateVideoSchema({
    name: channel.name,
    description: channel.description ?? undefined,
    thumbnailUrl: storageUrl(channel.image) ?? undefined,
    uploadDate: channel.created_at,
    interactionCount: channel.views,
  })

  return (
    <main className="max-w-7xl w-full mx-auto mt-8 md:mt-16 px-6 md:px-12">
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(videoSchema) }}
      />
      {/* ── Hero header ── */}
      <div className="border-b border-white/[0.07] bg-zinc-900 px-5 py-8">
        <div className="grid grid-cols-[1fr_auto] md:grid-cols-[auto_1fr_auto] items-start gap-2 md:gap-6">
          {/* Logo */}
          <div className="flex h-18 w-18 md:h-24 md:w-24 items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-zinc-800">
            {logo ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img src={logo} alt={channel.name} className="h-full w-full object-contain" />
            ) : (
              <span className="font-head text-xl font-black text-zinc-400">
                {channel.name.slice(0, 3).toUpperCase()}
              </span>
            )}
          </div>

          {/* Info */}
          <div>
            <h1 className="font-head mb-2 text-lg md:text-xl lg:text-3xl font-extrabold tracking-tight text-white">
              {channel.name}
            </h1>
            <div className="mb-3 flex flex-wrap gap-1.5">
              {channel.featured && (
                <Badge variant="accent">✦ Featured</Badge>
              )}
              {channel.quality && (
                <Badge variant="teal">{channel.quality}</Badge>
              )}
              {channel.country && (
                <Badge variant="muted">
                  {channel.country.name}
                </Badge>
              )}
              {channel.language && (
                <Badge variant="muted">{channel.language}</Badge>
              )}
            </div>
            
          </div>

          {/* Stats */}
          <div className="flex flex-col md: flex-row items-end gap-3">
            <div className="text-right">
              <div className="flex justify-end items-center gap-2 font-head text-2xl font-bold text-lime-400">
                <Icon.Eye />
                <span>{fmtViews(channel.views)}</span>
              </div>
              <div className="flex items-center justify-end gap-1 text-xs text-zinc-500">
                 total views
              </div>
            </div>
            <div className="text-right">
              <LiveViewerCount channelId={channel.id} initialCount={channel.active_viewers ?? 0} />
            </div>
          </div>
        </div>
      </div>

      {/* ── Main layout ── */}
      <div className="grid grid-cols-1 lg:grid-cols-[1fr_280px]">
        {/* Left — player + meta */}
        <div className="border-r border-white/[0.07]">
          <ClientChannelPlayer channel={channel} />
          {/* Description */}
          {channel.description && (
              <p className="max-w-xl text-sm font-light leading-relaxed text-zinc-400">
                {channel.description}
              </p>
            )}
          {/* Categories & Tags */}
          <div className="flex flex-col lg:flex-row space-y-5 py-6 gap-6">
            {channel.categories && channel.categories.length > 0 && (
              <div>
                <p className="mb-2 text-[11px] uppercase tracking-widest text-zinc-500">
                  Categories
                </p>
                <div className="flex flex-wrap gap-2">
                  {channel.categories.map(cat => (
                    <Link
                      key={cat.id}
                      href={`/categories/${cat.id}`}
                      className="rounded-lg border px-3 py-1.5 text-xs transition-all hover:opacity-80"
                      style={{
                        background: `${cat.color}18`,
                        color: cat.color,
                        borderColor: `${cat.color}40`,
                      }}
                    >
                      {cat.name}
                    </Link>
                  ))}
                </div>
              </div>
            )}
            <div>
                <p className="mb-2 text-[11px] uppercase tracking-widest text-zinc-500">
                  Language
                </p>
                    <Link
                      href=""
                      className="rounded-lg border border-gray-500 px-3 py-1.5 text-zinc-400 text-xs transition-all hover:opacity-80"
                    >
                      {channel.language}
                  </Link>
              </div>

            <div>
                <p className="mb-2 text-[11px] uppercase tracking-widest text-zinc-500">
                  Country
                </p>
                <div className="flex">
                    <Link
                      href={`/countries/${channel.country?.slug}`}
                      className="flex flex-row gap-2 items-center text-zinc-400 text-xs transition-all hover:opacity-80"
                    >
                      <img src={flag} alt={channel.country?.name} className="rounded-md w-6 h-6" />
                      <span className="rounded-lg border border-gray-500 bg-zinc-900 px-3 py-1.5">{channel.country?.name}</span>
                    </Link>
                </div>
              </div>
              </div>
              <div className="space-y-5">
            {channel.tags && channel.tags.length > 0 && (
              <div>
                <p className="mb-2 text-[11px] uppercase tracking-widest text-zinc-500">Tags</p>
                <div className="flex flex-wrap gap-2">
                  {channel.tags.map(tag => (
                    <Link
                      key={tag.id}
                      href={`/tags/${tag.slug}`}
                      className="rounded-full border border-white/[0.07] bg-zinc-900 px-3 py-1 text-xs text-zinc-400 transition-all hover:border-teal-400/30 hover:text-teal-400"
                    >
                      #{tag.name}
                    </Link>
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Right sidebar */}
        <aside className="px-5 py-6">

          {/* Related channels */}
          {related.length > 0 && (
            <div>
              <p className="mb-3 text-[11px] uppercase tracking-widest text-zinc-500">
                Related Channels
              </p>
              <div className="space-y-2">
                {related.map(ch => (
                  <Link
                    key={ch.id}
                    href={`/channels/${ch.slug}`}
                    className="flex items-center gap-3 rounded-lg p-2 transition-all hover:bg-zinc-800"
                  >
                    <div className="relative w-16 h-12 rounded-lg border border-white/25">
                      <Image src={storageUrl(ch.image) || "not found"} alt={ch.name} fill className="object-cover rounded-lg"/>
                    </div>
                    <div className="min-w-0">
                      <p className="truncate text-xs font-medium text-zinc-100">{ch.name}</p>
                      <p className="text-[11px] text-zinc-500">{fmtViews(ch.views)} views</p>
                    </div>
                  </Link>
                ))}
              </div>
            </div>
          )}
        </aside>
      </div>
    </main>
  )
}

// ── Badge helper ──
function Badge({
  variant,
  children,
}: {
  variant: 'accent' | 'teal' | 'muted'
  children: React.ReactNode
}) {
  const cls = {
    accent: 'bg-lime-400/10 text-lime-400 border-lime-400/20',
    teal:   'bg-teal-400/10 text-teal-400 border-teal-400/20',
    muted:  'bg-zinc-800 text-zinc-400 border-white/[0.07]',
  }[variant]

  return (
    <span className={`rounded border px-2 py-0.5 text-[10px] font-medium ${cls}`}>
      {children}
    </span>
  )
}
