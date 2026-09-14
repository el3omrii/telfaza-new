import { revalidatePath, revalidateTag } from 'next/cache'
import { NextRequest, NextResponse } from 'next/server'

const REVALIDATE_SECRET = process.env.REVALIDATE_SECRET

function normalizeTags(value: unknown): string[] {
  if (typeof value === 'string') return [value]
  if (!Array.isArray(value)) return []
  return value.filter((tag): tag is string => typeof tag === 'string' && tag.length > 0)
}

function normalizePaths(value: unknown): string[] {
  if (typeof value === 'string') return [value]
  if (!Array.isArray(value)) return []
  return value.filter((path): path is string => typeof path === 'string' && path.startsWith('/'))
}

export async function POST(request: NextRequest) {
  if (REVALIDATE_SECRET) {
    const providedSecret = request.headers.get('x-revalidate-secret') ?? request.nextUrl.searchParams.get('secret')
    if (providedSecret !== REVALIDATE_SECRET) {
      return NextResponse.json({ message: 'Unauthorized' }, { status: 401 })
    }
  }

  const body = await request.json().catch(() => ({}))
  const channelSlugs = Array.from(new Set([
    ...normalizeTags((body as { channelSlugs?: unknown }).channelSlugs),
    ...normalizeTags((body as { oldSlug?: unknown }).oldSlug),
    ...normalizeTags((body as { newSlug?: unknown }).newSlug),
  ]))
  const paths = Array.from(new Set([
    ...normalizePaths((body as { paths?: unknown }).paths),
    ...channelSlugs.map(slug => `/channels/${slug}`),
  ]))
  const tags = Array.from(new Set([
    ...normalizeTags((body as { tags?: unknown }).tags),
    ...normalizeTags((body as { tag?: unknown }).tag),
    ...channelSlugs.map(slug => `channel:${slug}`),
  ]))

  if (paths.length === 0 && tags.length === 0) {
    return NextResponse.json({ message: 'No paths or tags provided' }, { status: 400 })
  }

  tags.forEach(tag => revalidateTag(tag, 'max'))
  paths.forEach(path => revalidatePath(path))

  return NextResponse.json({ revalidated: true, paths, tags })
}
