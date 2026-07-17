'use client'

import { useState } from 'react'
import { usePusherViewerCount } from '@/hooks/usePusherViewerCount'
import { useViewerToken } from '@/hooks/useViewerToken'

export default function LiveViewerCount({
  channelId,
  initialCount = 0,
}: {
  channelId: number | string
  initialCount?: number
}) {
  const [viewerCount, setViewerCount] = useState(initialCount)

  const viewerToken = useViewerToken()

  usePusherViewerCount(channelId.toString(), viewerToken, setViewerCount)
  /* ─── icons ────────────────────────────────────────────────────────── */
const Icon = {
  Users: () => (
    <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" />
      <path d="M23 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />
    </svg>
  ),
};

  return (
    <>
      <div className="flex justify-end items-center font-head text-2xl font-bold text-red-400 gap-2">
        <Icon.Users />
        <span>{viewerCount}</span>
      </div>
      <div className="text-xs text-zinc-500 text-nowrap">watching now</div>
    </>
  )
}
