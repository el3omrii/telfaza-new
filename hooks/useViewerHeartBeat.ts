'use client'

import { useEffect } from 'react'
import { trackChannel } from '@/lib/api'

export function useViewerHeartbeat(
  channelId: string,
  viewerToken: string,
  onCountChange?: (count: number) => void
) {
  useEffect(() => {
    let intervalId: ReturnType<typeof setInterval> | null = null

    const ping = async () => {
      try {
        const response = await trackChannel(channelId, viewerToken)
        onCountChange?.(response.viewers ?? 0)
      } catch (error) {
        console.error('Failed to update viewer count', error)
      }
    }

    const start = () => {
      void ping()
      intervalId = setInterval(() => {
        void ping()
      }, 30000)
    }

    const stop = () => {
      if (intervalId) clearInterval(intervalId)
    }

    const handleVisibility = () => {
      if (document.hidden) {
        stop()
      } else {
        start()
      }
    }

    start()
    document.addEventListener('visibilitychange', handleVisibility)

    return () => {
      stop()
      document.removeEventListener('visibilitychange', handleVisibility)
    }
  }, [channelId, viewerToken, onCountChange])
}