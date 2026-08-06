import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  output: 'standalone',
  // Ensure Next.js transpilePackages strips out legacy JS from node_modules if needed
  transpilePackages: [], // Add third-party UI libs here if they ship ES5 code
  
  // Compiler options
  compiler: {
    // Remove console logs in production to shave off bytes
    removeConsole: process.env.NODE_ENV === 'production',
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
