import { NextResponse } from 'next/server'

export async function POST(request: Request) {
  try {
    const body = await request.json()

    if (!body?.channelId || !body?.reason || !body?.viewerToken) {
      return NextResponse.json({ error: 'Invalid payload' }, { status: 400 })
    }

    console.info('Stream report received', {
      channelId: body.channelId,
      channelName: body.channelName ?? null,
      reason: body.reason,
      viewerToken: body.viewerToken,
    })

    return NextResponse.json({ ok: true })
  } catch {
    return NextResponse.json({ error: 'Failed to process report' }, { status: 500 })
  }
}
