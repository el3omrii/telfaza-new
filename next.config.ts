import type { NextConfig } from "next";

const nextConfig: NextConfig = {
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
