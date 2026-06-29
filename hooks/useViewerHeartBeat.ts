'use client'

import { useEffect } from 'react'
import { trackChannel } from '@/lib/api' // Ensure this is client-safe!

export function useViewerHeartbeat(channelId: string, viewerToken: string) {
  useEffect(() => {
    let intervalId: ReturnType<typeof setInterval> | null = null

    const ping = () => trackChannel(channelId, viewerToken)

    const start = () => {
      ping() // Ping immediately
      intervalId = setInterval(ping, 30000) // Then every 30s
    }

    const stop = () => {
      if (intervalId) clearInterval(intervalId)
    }

    const handleVisibility = () => document.hidden ? stop() : start()

    start()
    document.addEventListener('visibilitychange', handleVisibility)

    // ❗ CRITICAL: Cleanup prevents memory leaks and duplicate timers
    return () => {
      stop()
      document.removeEventListener('visibilitychange', handleVisibility)
    }
  }, [channelId])
}