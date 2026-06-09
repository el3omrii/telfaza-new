import { revalidateTag } from 'next/cache'
import { NextRequest, NextResponse } from 'next/server'

const REVALIDATE_SECRET = process.env.REVALIDATE_SECRET

function normalizeTags(value: unknown): string[] {
  if (typeof value === 'string') return [value]
  if (!Array.isArray(value)) return []
  return value.filter((tag): tag is string => typeof tag === 'string' && tag.length > 0)
}

export async function POST(request: NextRequest) {
  if (REVALIDATE_SECRET) {
    const providedSecret = request.headers.get('x-revalidate-secret') ?? request.nextUrl.searchParams.get('secret')
    if (providedSecret !== REVALIDATE_SECRET) {
      return NextResponse.json({ message: 'Unauthorized' }, { status: 401 })
    }
  }

  const body = await request.json().catch(() => ({}))
  const tags = Array.from(new Set([
    ...normalizeTags((body as { tags?: unknown }).tags),
    ...normalizeTags((body as { tag?: unknown }).tag),
  ]))

  if (tags.length === 0) {
    return NextResponse.json({ message: 'No tags provided' }, { status: 400 })
  }

  tags.forEach(tag => revalidateTag(tag, 'max'))

  return NextResponse.json({ revalidated: true, tags })
}
