import type { Metadata } from "next";

export const SITE_NAME = "Telfaza LIVE";
export const SITE_DESCRIPTION =
  "Watch live TV channels, browse by country, category, and tag, and discover what is streaming now.";

const siteUrl = process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000";

export const metadataBase = new URL(siteUrl);
export const defaultOpenGraphImage = "/og-image.svg";

export function absoluteUrl(path: string) {
  return new URL(path, siteUrl).toString();
}

export function buildMetadata({
  title,
  description = SITE_DESCRIPTION,
  path = "/",
}: {
  title: string;
  description?: string;
  path?: string;
}): Metadata {
  const url = absoluteUrl(path);
  const imageUrl = absoluteUrl(defaultOpenGraphImage);

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
          url: imageUrl,
          width: 1200,
          height: 630,
          alt: `${title} | ${SITE_NAME}`,
        },
      ],
    },
    twitter: {
      card: "summary_large_image",
      title,
      description,
      images: [imageUrl],
    },
  };
}