import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  images: {
  remotePatterns: [
    {
      protocol: 'http',
      hostname: 'questionaire.localhost',
      port: '',
      pathname: '/api/**',
    },
  ],
},
};

export default nextConfig;
