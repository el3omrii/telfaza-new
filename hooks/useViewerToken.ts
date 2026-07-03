'use client'

import { useState } from 'react'

export function useViewerToken() {
  const [viewerToken] = useState<string>(() => {
    if (typeof window === 'undefined') return ''

    const storedToken = window.sessionStorage.getItem('viewer_token')
    if (storedToken) return storedToken

    const nextToken = crypto.randomUUID()
    window.sessionStorage.setItem('viewer_token', nextToken)
    return nextToken
  })

  return viewerToken
}
