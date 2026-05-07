import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  images: {
    remotePatterns: [
      {
        // Allow images from your Laravel storage (adjust hostname as needed)
        protocol: 'https',
        hostname: 'admin.telfazalive.com',
        port: '',
        pathname: '/uploads/**',
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
