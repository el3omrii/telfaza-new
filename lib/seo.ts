import type { Metadata } from "next";

export const SITE_NAME = process.env.NEXT_PUBLIC_SITE_NAME ?? "Telfaza";
export const SITE_DESCRIPTION = process.env.NEXT_PUBLIC_SITE_DESCRIPTION ?? 
  "Watch live TV channels, browse by country, category, and tag, and discover what is streaming now.";

const siteUrl = process.env.NEXT_PUBLIC_APP_URL ?? "http://localhost:3000";

export const metadataBase = new URL(siteUrl);
export const defaultOpenGraphImage = "/og-image.svg";

export function absoluteUrl(path: string) {
  return new URL(path, siteUrl).toString();
}

export function buildMetadata({
  title,
  description = SITE_DESCRIPTION,
  path = "/",
  imageUrl ,
}: {
  title: string;
  description?: string;
  path?: string;
  imageUrl? : string | null
}): Metadata {
  const url = absoluteUrl(path);
  const resolvedImageUrl = imageUrl ? imageUrl : absoluteUrl(defaultOpenGraphImage);

  return {
    title,
    description,
    alternates: {
      canonical: url,
    },
    openGraph: {
      type: "website",
      siteName: SITE_NAME,
      title,
      description,
      url,
      images: [
        {
          url: resolvedImageUrl,
          width: 440,
          height: 250,
          alt: `${title} | ${SITE_NAME}`,
        },
      ],
    },
    twitter: {
      card: "summary_large_image",
      title,
      description,
      images: [resolvedImageUrl],
    },
  };
}

export function generateVideoSchema({
  name,
  description,
  thumbnailUrl,
  embedUrl,
  uploadDate,
  interactionCount,
}: {
  name: string;
  description?: string;
  thumbnailUrl?: string;
  embedUrl?: string;
  uploadDate?: string;
  interactionCount?: number;
}) {
  const date = new Date();
  let expires = date.setDate(date.getDate() + 30);
  return {
    "@context": "https://schema.org",
    "@type": "VideoObject",
    name,
    description: description || `Watch ${name} live on ${SITE_NAME}`,
    thumbnailUrl: thumbnailUrl || absoluteUrl(defaultOpenGraphImage),
    embedUrl: embedUrl || process.env.NEXT_PUBLIC_STORAGE_URL,
    uploadDate: uploadDate || new Date().toISOString(),
    isLiveBroadcast: true,
    expires: new Date(expires).toISOString(), //expires after 30 days
    interactionStatistic: {
        "@type": "InteractionCounter",
        "interactionType": { "@type": "WatchAction" },
        "userInteractionCount": interactionCount
    },
    "publication": [
        {
          "@type": "BroadcastEvent",
          "isLiveBroadcast": true,
        },
      ]
  };
}