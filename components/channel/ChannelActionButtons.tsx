'use client'

import { useState } from 'react'
import { useViewerToken } from '@/hooks/useViewerToken'
import { reportVideo } from '@/lib/api'

const FAVORITES_KEY = 'telfaza_favorite_channels'

function readFavorites() {
  if (typeof window === 'undefined') return [] as Array<string | number>

  try {
    const stored = window.localStorage.getItem(FAVORITES_KEY)
    if (!stored) return []

    return JSON.parse(stored) as Array<string | number>
  } catch {
    return []
  }
}
const REPORT_OPTIONS = [
  { value: 'stream_dead_offline', label: 'Stream is dead / Offline' },
  { value: 'constant_buffering', label: 'Constant buffering' },
  { value: 'wrong_video_content', label: 'Wrong video / Content mismatch' },
  { value: 'player_controls_missing', label: 'Player controls missing or unresponsive' },
]

export default function ChannelActionButtons({
  channelId,
  channelName,
}: {
  channelId: number | string
  channelName: string
}) {
  const viewerToken = useViewerToken()
  const [isFavorite, setIsFavorite] = useState(() => {
    return readFavorites().map(String).includes(String(channelId))
  })
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [reason, setReason] = useState(REPORT_OPTIONS[0].value)
  const [status, setStatus] = useState<'idle' | 'submitting' | 'success' | 'error'>('idle')
  const [message, setMessage] = useState('')

  const toggleFavorite = () => {
    if (typeof window === 'undefined') return

    const nextValue = String(channelId)
    const favorites = readFavorites()
    const exists = favorites.map(String).includes(nextValue)

    const updated = exists
      ? favorites.filter(item => String(item) !== nextValue)
      : [...favorites, nextValue]

    window.localStorage.setItem(FAVORITES_KEY, JSON.stringify(updated))
    setIsFavorite(!exists)
  }

  const closeModal = () => {
    setIsModalOpen(false)
    setStatus('idle')
    setMessage('')
    setReason(REPORT_OPTIONS[0].value)
  }

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setStatus('submitting')
    setMessage('')

    try {
      const response = await reportVideo(JSON.stringify({
          channelId: String(channelId),
          channelName,
          reason,
          viewerToken,
        })
      )

      if (!response.ok) {
        throw new Error('Unable to submit report')
      }

      setStatus('success')
      setMessage('Thanks, your report has been received.')
      setTimeout(() => {
        closeModal()
      }, 700)
    } catch {
      setStatus('error')
      setMessage('We could not submit the report right now. Please try again.')
    }
  }

  return (
    <div className="flex flex-wrap items-center gap-3 py-4">
      <button
        type="button"
        onClick={toggleFavorite}
        className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-zinc-900 px-3.5 py-2 text-sm font-medium text-zinc-200 transition hover:border-rose-500/40 hover:text-rose-400"
      >
        {isFavorite ? (
          <svg className="h-4 w-4 fill-rose-500 text-rose-500" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 21s-6.7-4.35-8.95-8.24A5.66 5.66 0 0 1 12 6.1a5.66 5.66 0 0 1 8.95 6.66C18.7 16.65 12 21 12 21Z" />
          </svg>
        ) : (
          <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
            <path d="M12 20s-6.7-4.35-8.95-8.24A5.66 5.66 0 0 1 12 6.1a5.66 5.66 0 0 1 8.95 6.66C18.7 15.65 12 20 12 20Z" />
          </svg>
        )}
        {isFavorite ? 'Favorited' : 'Favorite'}
      </button>

      <button
        type="button"
        onClick={() => setIsModalOpen(true)}
        className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-zinc-900 px-3.5 py-2 text-sm font-medium text-zinc-200 transition hover:border-amber-500/40 hover:text-amber-400"
      >
        <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
          <path d="M12 3v10" />
          <path d="M12 15v6" />
          <path d="M4 7h16" />
          <path d="M6 7c0-2.2 1.8-4 4-4h4c2.2 0 4 1.8 4 4" />
        </svg>
        Report dead stream
      </button>

      {isModalOpen ? (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
          <div className="w-full max-w-md rounded-2xl border border-white/10 bg-zinc-900 p-5 shadow-2xl">
            <div className="mb-4 flex items-start justify-between gap-3">
              <div>
                <h3 className="text-lg font-semibold text-white">Report dead stream</h3>
                <p className="mt-1 text-sm text-zinc-400">Let us know what is wrong with this stream.</p>
              </div>
              <button
                type="button"
                onClick={closeModal}
                className="rounded-full p-2 text-zinc-400 transition hover:bg-zinc-800 hover:text-white"
                aria-label="Close report dialog"
              >
                ×
              </button>
            </div>

            <form className="space-y-4" onSubmit={handleSubmit}>
              <input type="hidden" name="viewer_token" value={viewerToken} />

              <label className="block text-sm text-zinc-300">
                <span className="mb-2 block">Issue</span>
                <select
                  value={reason}
                  onChange={event => setReason(event.target.value)}
                  className="w-full rounded-lg border border-white/10 bg-zinc-800 px-3 py-2 text-sm text-white outline-none ring-0"
                >
                  {REPORT_OPTIONS.map(option => (
                    <option key={option.value} value={option.value}>
                      {option.label}
                    </option>
                  ))}
                </select>
              </label>

              <div className="flex justify-end gap-2">
                <button
                  type="button"
                  onClick={closeModal}
                  className="rounded-full border border-white/10 px-3.5 py-2 text-sm text-zinc-300 transition hover:bg-zinc-800"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={status === 'submitting'}
                  className="rounded-full bg-amber-500 px-3.5 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-70"
                >
                  {status === 'submitting' ? 'Submitting...' : 'Submit report'}
                </button>
              </div>

              {message ? (
                <p className={`text-sm ${status === 'error' ? 'text-rose-400' : 'text-emerald-400'}`}>
                  {message}
                </p>
              ) : null}
            </form>
          </div>
        </div>
      ) : null}
    </div>
  )
}
