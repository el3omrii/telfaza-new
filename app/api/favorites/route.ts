import { NextResponse } from 'next/server'
import { getChannelsByIds } from '@/lib/api'

export async function POST(request: Request) {
  try {
    const { ids } = await request.json()

    if (!ids || !Array.isArray(ids) || ids.length === 0) {
      return NextResponse.json([])
    }

    // Filter out any non-string/non-number IDs
    const validIds = ids.filter(id => typeof id === 'string' || typeof id === 'number')

    if (validIds.length === 0) {
      return NextResponse.json([])
    }

    // Fetch channels by IDs
    const channels = await getChannelsByIds(validIds)

    return NextResponse.json(channels)
  } catch (error) {
    console.error('Error fetching favorite channels:', error)
    return NextResponse.json([], { status: 500 })
  }
}