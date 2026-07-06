'use client'

import { useEffect } from 'react'
import { Dispatch, SetStateAction } from 'react'
import Pusher from 'pusher-js'

export function usePusherViewerCount(
  channelId: string,
  viewerToken: string,
  onCountChange?: Dispatch<SetStateAction<number>>
) {
  useEffect(() => {
    // Initialize Pusher
    const pusher = new Pusher(process.env.NEXT_PUBLIC_PUSHER_KEY!, {
      cluster: process.env.NEXT_PUBLIC_PUSHER_CLUSTER!,
      authEndpoint: '/api/pusher/auth',
      auth: {
        headers: {
          // Pass the token to our API route to identify the user
          Authorization: viewerToken,
        },
      },
    })

    // Subscribe to the presence channel for this specific stream
    const channelName = `presence-stream-${channelId}`
    const channel = pusher.subscribe(channelName)

    // 1. Get the initial count when successfully connected
    channel.bind('pusher:subscription_succeeded', (data: any) => {
      onCountChange?.(data.count)
    })

    // 2. Increment count when someone else joins
    channel.bind('pusher:member_added', () => {
      onCountChange?.((current: number) => current + 1)
    })

    // 3. Decrement count when someone leaves
    channel.bind('pusher:member_removed', () => {
      onCountChange?.((current: number) => Math.max(0, current - 1))
    })

    // Cleanup on unmount
    return () => {
      pusher.unsubscribe(channelName)
      pusher.disconnect()
    }
  }, [channelId, viewerToken, onCountChange])
}
