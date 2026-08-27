import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import { Suspense } from "react";
import "./globals.css";
import Nav from "@/components/Nav";
import Footer from "@/components/ui/Footer";
import NavigationProgress from "@/components/NavigationProgress";
import { SITE_DESCRIPTION, SITE_NAME, metadataBase } from "@/lib/seo";
import { GoogleAnalytics } from "@/components/analytics/GoogleAnalytics";
import DevToolsGuard from '@/components/DevToolsGuard';


const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  metadataBase,
  title: {
    default: SITE_NAME,
    template: `%s | ${SITE_NAME}`,
  },
  description: SITE_DESCRIPTION,
  applicationName: SITE_NAME,
  openGraph: {
    type: "website",
    siteName: SITE_NAME,
    title: SITE_NAME,
    description: SITE_DESCRIPTION,
    url: "/",
    images: [
      {
        url: "/og-image.svg",
        width: 1200,
        height: 630,
        alt: `${SITE_NAME} homepage`,
      },
    ],
  },
  twitter: {
    card: "summary_large_image",
    title: SITE_NAME,
    description: SITE_DESCRIPTION,
    images: ["/og-image.svg"],
  },
  robots: {
    index: true,
    follow: true,
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html
      lang="en"
      className={`${geistSans.variable} ${geistMono.variable} h-full antialiased`}
    >
      <body className="min-h-full flex flex-col" style={{ background: "#0a0a0f" }}>
        <DevToolsGuard />
        {process.env.NEXT_PUBLIC_GA_TRACKING_ID && (
          <GoogleAnalytics trackingId={process.env.NEXT_PUBLIC_GA_TRACKING_ID} />
        )}
        <Suspense fallback={null}>
          <NavigationProgress />
        </Suspense>
        <Nav />
        {children}
        <Footer />
      </body>
    </html>
  );
}
