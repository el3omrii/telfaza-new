// cache-handler.js
const { CacheHandler } = require('@fortedigital/nextjs-cache-handler');
const { createRedisHandler } = require('@fortedigital/nextjs-cache-handler/redis-strings');
const { createClient } = require('redis');

CacheHandler.onCreation(async () => {
  // Check if a Redis URL is present, or if we are explicitly skipping it during build
  const isBuildPhase = process.env.NEXT_PHASE_BUILD === '1' || !process.env.REDIS_URL;

  if (isBuildPhase) {
    console.log('Skipping Redis connection initialization during build phase.');
    // Return an empty handlers array; Next.js will safely fallback to default local files
    return {
      handlers: [],
    };
  }

  // Production Server Runtime Logic
  try {
    const client = createClient({
      url: process.env.REDIS_URL || 'redis://localhost:6379'
    });

    client.on('error', (err) => console.error('Redis Client Error:', err));
    await client.connect();

    const redisHandler = createRedisHandler({
      client,
      keyPrefix: 'next-cache:',
    });

    return {
      handlers: [redisHandler],
    };
  } catch (error) {
    console.error('Failed to connect to Redis, falling back to disk cache:', error);
    return { handlers: [] };
  }
});

module.exports = CacheHandler;
