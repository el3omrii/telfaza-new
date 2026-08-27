import Pusher from 'pusher'
import { NextResponse } from 'next/server'

const pusher = new Pusher({
  host: process.env.NEXT_PUBLIC_PUSHER_HOST!,
  appId: process.env.PUSHER_APP_ID!,
  key: process.env.NEXT_PUBLIC_PUSHER_KEY!,
  secret: process.env.PUSHER_SECRET!,
  cluster: process.env.NEXT_PUBLIC_PUSHER_CLUSTER!,
  useTLS: true,
})

export async function POST(req: Request) {
  // 1. Get the socket_id and channel_name from Pusher's request
  const text = await req.text()
  const params = new URLSearchParams(text)
  const socketId = params.get('socket_id')!
  const channelName = params.get('channel_name')!

  // 2. Identify the user. (We'll pass the viewerToken from the frontend)
  const viewerToken = req.headers.get('Authorization') || 'anonymous-viewer'

  // 3. Authenticate the user for this channel
  const authResponse = pusher.authorizeChannel(socketId, channelName, {
    user_id: viewerToken, // Required by Pusher Presence
    // user_info: { anyExtraData: 'here' } // Optional
  })

  return NextResponse.json(authResponse)
}
