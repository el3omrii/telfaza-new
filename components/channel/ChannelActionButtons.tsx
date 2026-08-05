'use client'

import { useState } from 'react'
import { useViewerToken } from '@/hooks/useViewerToken'
import { reportVideo, readFavorites } from '@/lib'

const FAVORITES_KEY = 'telfaza_favorite_channels'

const REPORT_OPTIONS = [
  { value: 'stream_dead_offline', label: 'Stream is dead / Offline' },
  { value: 'constant_buffering', label: 'Constant buffering' },
  { value: 'wrong_video_content', label: 'Wrong video / Content mismatch' },
  { value: 'player_controls_missing', label: 'Player controls missing or unresponsive' },
]

export default function ChannelActionButtons({
  channelId,
  channelName,
  slug,
}: {
  channelId: number | string
  channelName: string
  slug: string
}) {
  const userToken = useViewerToken()
  const [isFavorite, setIsFavorite] = useState(() => {
    return readFavorites().map(String).includes(String(channelId))
  })
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [reason, setReason] = useState(REPORT_OPTIONS[0].value)
  const [details, setDetails] = useState('')
  const [status, setStatus] = useState<'idle' | 'submitting' | 'success' | 'error'>('idle')
  const [message, setMessage] = useState('')
  const [shareMessage, setShareMessage] = useState('')
  const [isShareDropdownOpen, setIsShareDropdownOpen] = useState(false)
  const [isEmbedModalOpen, setIsEmbedModalOpen] = useState(false);
  const [embedMessage, setEmbedMessage] = useState('');

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
    setDetails('')
  }

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setStatus('submitting')
    setMessage('')

    try {
      await reportVideo({
          channel_id: String(channelId),
          channelName,
          issue_type: reason,
          details,
          user_token: userToken,
        }
      )
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

  const handleShare = async () => {
    if (typeof window === 'undefined') return;

    try {
      const currentUrl = window.location.href;
      const shareData = {
        title: `Watch ${channelName} on Telfaza LIVE`,
        text: `Check out ${channelName} live on Telfaza!`,
        url: currentUrl,
      };

      // Try Web Share API first
      if (navigator.share) {
        await navigator.share(shareData);
        setShareMessage('Thanks for sharing!');
      } else {
        // Fallback to clipboard copy
        await navigator.clipboard.writeText(currentUrl);
        setShareMessage('Link copied to clipboard!');
      }

      // Clear message after 2 seconds
      setTimeout(() => {
        setShareMessage('');
      }, 2000);
    } catch (err) {
      console.error('Error sharing:', err);
      setShareMessage('Could not share. Please try again.');
      setTimeout(() => {
        setShareMessage('');
      }, 2000);
    }
  };

  const handleCopyEmbed = async () => {
    if (typeof window === 'undefined') return;

    try {
      const embedUrl = `${process.env.NEXT_PUBLIC_APP_URL}/embed/${slug}`;
      const embedCode = `<iframe src="${embedUrl}" frameborder="0" allowfullscreen></iframe>`;

      await navigator.clipboard.writeText(embedCode);
      setEmbedMessage('Embed code copied to clipboard!');

      // Clear message after 2 seconds
      setTimeout(() => {
        setEmbedMessage('');
      }, 2000);
    } catch (err) {
      console.error('Error copying embed code:', err);
      setEmbedMessage('Could not copy embed code. Please try again.');

      // Clear message after 2 seconds
      setTimeout(() => {
        setEmbedMessage('');
      }, 2000);
    }
  };

  const closeEmbedModal = () => {
    setIsEmbedModalOpen(false);
  };

  const handleShareEmail = () => {
    if (typeof window === 'undefined') return;

    const currentUrl = window.location.href;
    const subject = `Check out ${channelName} on Telfaza LIVE`;
    const body = `I wanted to share this live stream with you: ${currentUrl}`;

    window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
  };

  return (
    <div className="flex flex-wrap justify-end items-center gap-3 py-4">
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

      <div className="relative">
        <button
          type="button"
          onClick={() => {
            handleShare();
            setIsShareDropdownOpen(false);
          }}
          onMouseEnter={() => setIsShareDropdownOpen(true)}
          onMouseLeave={() => setIsShareDropdownOpen(false)}
          className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-zinc-900 px-3.5 py-2 text-sm font-medium text-zinc-200 transition hover:border-blue-500/40 hover:text-blue-400"
          title="Share this channel"
        >
          <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
            <polyline points="16,6 12,2 8,6" />
            <line x1="12" y1="2" x2="12" y2="15" />
          </svg>
          Share
        </button>
        {isShareDropdownOpen && (
          <div
            onMouseEnter={() => setIsShareDropdownOpen(true)}
            onMouseLeave={() => setIsShareDropdownOpen(false)}
            className="absolute right-0 mt-2 w-48 rounded-md border border-white/10 bg-zinc-900 shadow-lg z-10"
          >
            <div className="py-1">
              <button
                type="button"
                onClick={(e) => {
                  e.stopPropagation();
                  handleShare();
                  setIsShareDropdownOpen(false);
                }}
                className="w-full px-4 py-2 text-left text-sm text-zinc-200 hover:bg-zinc-800 flex items-center gap-2"
              >
                <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
                  <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                  <polyline points="16,6 12,2 8,6" />
                  <line x1="12" y1="2" x2="12" y2="15" />
                </svg>
                Social Networks
              </button>
              <button
                type="button"
                onClick={(e) => {
                  e.stopPropagation();
                  handleShareEmail();
                  setIsShareDropdownOpen(false);
                }}
                className="w-full px-4 py-2 text-left text-sm text-zinc-200 hover:bg-zinc-800 flex items-center gap-2"
              >
                <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                  <polyline points="22,6 12,13 2,6" />
                </svg>
                Email
              </button>
            </div>
          </div>
        )}
      </div>

      <button
        type="button"
        onClick={() => setIsModalOpen(true)}
        className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-zinc-900 px-3.5 py-2 text-sm font-medium text-zinc-200 transition hover:border-amber-500/40 hover:text-amber-400"
      >
        <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M4 22V4a1 1 0 0 1 .4-.8A6 6 0 0 1 8 2c3 0 5 2 7.333 2q2 0 3.067-.8A1 1 0 0 1 20 4v10a1 1 0 0 1-.4.8A6 6 0 0 1 16 16c-3 0-5-2-8-2a6 6 0 0 0-4 1.528"/>
        </svg>
        Report
      </button>

      <button
        type="button"
        onClick={() => setIsEmbedModalOpen(true)}
        className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-zinc-900 px-3.5 py-2 text-sm font-medium text-zinc-200 transition hover:border-purple-500/40 hover:text-purple-400"
      >
        <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
          <line x1="8" y1="21" x2="16" y2="21" />
          <line x1="12" y1="17" x2="12" y2="21" />
        </svg>
        Embed
      </button>

      {shareMessage && (
        <div className="fixed bottom-4 right-4 z-50 rounded-lg bg-zinc-800 px-4 py-2 text-sm text-emerald-400 shadow-lg">
          {shareMessage}
        </div>
      )}

      {embedMessage && (
        <div className="fixed bottom-4 right-4 z-50 rounded-lg bg-zinc-800 px-4 py-2 text-sm text-emerald-400 shadow-lg">
          {embedMessage}
        </div>
      )}

      {isModalOpen || isEmbedModalOpen ? (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 backdrop-blur-md">
          {isModalOpen ? (
            <div
              role="dialog"
              aria-modal="true"
              aria-labelledby="report-dialog-title"
              className="relative w-full max-w-md overflow-hidden rounded-[28px] border border-white/10 bg-[linear-gradient(180deg,rgba(24,24,27,0.98),rgba(9,9,11,0.98))] shadow-[0_24px_80px_rgba(0,0,0,0.65)]"
            >
              <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(245,158,11,0.2),transparent_36%),radial-gradient(circle_at_bottom_left,rgba(59,130,246,0.16),transparent_38%)]" />

              <div className="relative p-5">
                <div className="mb-4 flex items-start justify-between gap-3">
                  <div className="flex items-start gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/15 text-amber-400">
                      <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
                        <path d="M12 9v4" />
                        <path d="M12 17h.01" />
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                      </svg>
                    </div>
                    <div>
                      <h3 id="report-dialog-title" className="text-lg font-semibold text-white">Report dead stream</h3>
                      <p className="mt-1 text-sm text-zinc-400">Let us know what is wrong with this stream.</p>
                    </div>
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
                  <input type="hidden" name="user_token" value={userToken} />

                  <label className="block text-sm text-zinc-300">
                    <span className="mb-2 block font-medium text-zinc-200">Issue</span>
                    <div className="relative">
                      <select
                        value={reason}
                        onChange={event => setReason(event.target.value)}
                        className="w-full appearance-none rounded-xl border border-white/10 bg-zinc-900/80 px-3 py-3 pr-10 text-sm text-white outline-none transition focus:border-amber-500/50 focus:ring-2 focus:ring-amber-500/25"
                      >
                        {REPORT_OPTIONS.map(option => (
                          <option key={option.value} value={option.value}>
                            {option.label}
                          </option>
                        ))}
                      </select>
                      <svg className="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
                        <polyline points="6,9 12,15 18,9" />
                      </svg>
                    </div>
                  </label>

                  <label className="block text-sm text-zinc-300">
                    <span className="mb-2 block font-medium text-zinc-200">Details</span>
                    <textarea
                      value={details}
                      onChange={event => setDetails(event.target.value)}
                      rows={4}
                      placeholder="Tell us more about the issue..."
                      className="w-full rounded-xl border border-white/10 bg-zinc-900/80 px-3 py-3 text-sm text-white outline-none transition placeholder:text-zinc-500 focus:border-amber-500/50 focus:ring-2 focus:ring-amber-500/25"
                    />
                  </label>

                  <div className="flex justify-end gap-2 pt-1">
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
                    <p className={`rounded-xl border px-3 py-2 text-sm ${status === 'error' ? 'border-rose-500/30 bg-rose-500/10 text-rose-300' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300'}`}>
                      {message}
                    </p>
                  ) : null}
                </form>
              </div>
            </div>
          ) : isEmbedModalOpen ? (
            <div
              role="dialog"
              aria-modal="true"
              aria-labelledby="embed-dialog-title"
              className="relative w-full max-w-md overflow-hidden rounded-[28px] border border-white/10 bg-[linear-gradient(180deg,rgba(24,24,27,0.98),rgba(9,9,11,0.98))] shadow-[0_24px_80px_rgba(0,0,0,0.65)]"
            >
              <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(168,85,247,0.2),transparent_36%),radial-gradient(circle_at_bottom_left,rgba(59,130,246,0.16),transparent_38%)]" />

              <div className="relative p-5">
                <div className="mb-4 flex items-start justify-between gap-3">
                  <div className="flex items-start gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-purple-500/15 text-purple-400">
                      <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                        <line x1="8" y1="21" x2="16" y2="21" />
                        <line x1="12" y1="17" x2="12" y2="21" />
                      </svg>
                    </div>
                    <div>
                      <h3 id="embed-dialog-title" className="text-lg font-semibold text-white">Embed this channel</h3>
                      <p className="mt-1 text-sm text-zinc-400">Copy this code to embed the channel on your website.</p>
                    </div>
                  </div>
                  <button
                    type="button"
                    onClick={closeEmbedModal}
                    className="rounded-full p-2 text-zinc-400 transition hover:bg-zinc-800 hover:text-white"
                    aria-label="Close embed dialog"
                  >
                    ×
                  </button>
                </div>

                <div className="space-y-4">
                  <div className="block text-sm text-zinc-300">
                    <span className="mb-2 block font-medium text-zinc-200">Embed code</span>
                    <div className="relative">
                      <div className="w-full rounded-xl border border-white/10 bg-zinc-900/80 px-3 py-3 pr-10 text-sm text-white outline-none transition">
                        {`<iframe src="${process.env.NEXT_PUBLIC_APP_URL}/embed/${slug}" frameborder="0" allowfullscreen></iframe>`}
                      </div>
                    </div>
                  </div>

                  <div className="flex justify-end gap-2 pt-1">
                    <button
                      type="button"
                      onClick={closeEmbedModal}
                      className="rounded-full border border-white/10 px-3.5 py-2 text-sm text-zinc-300 transition hover:bg-zinc-800"
                    >
                      Cancel
                    </button>
                    <button
                      type="button"
                      onClick={handleCopyEmbed}
                      className="rounded-full bg-purple-500 px-3.5 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-purple-400"
                    >
                      Copy code
                    </button>
                  </div>
                </div>
              </div>
            </div>
          ) : null}
        </div>
      ) : null}
    </div>
  )
}