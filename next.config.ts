import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  turbopack: {
    resolveAlias: {
      // Directs Turbopack to swap out the production build for the debug build
      'shaka-player/dist/shaka-player.compiled': 'shaka-player/dist/shaka-player.compiled.debug',
    },
  },
  images: {
    remotePatterns: [
      {
        // Votre stockage Laravel via variable d'environnement
        protocol: 'https',
        hostname: process.env.NEXT_PUBLIC_STORAGE_URL || "", 
      },
      {
        // Le domaine spécifique qui causait l'erreur
        protocol: 'https',
        hostname: 'cdn.telfazalive.com',
      },
      {
        // Allow images from your Laravel storage (adjust hostname as needed)
        protocol: 'https',
        hostname: 'flagcdn.com',
      },
    ],
  },
};

export default nextConfig;
