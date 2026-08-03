import type { Metadata } from "next";
import "./globals.css";
export const dynamic = 'force-dynamic'
export const revalidate = 0
export const metadata: Metadata = {
  robots: {
    index: false,
    follow: false,
  },
}

export default function EmbedLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en" className="h-full antialiased">
      <body className="min-h-full flex flex-col m-0 p-0 bg-black">
        {children}
      </body>
    </html>
  )
}
