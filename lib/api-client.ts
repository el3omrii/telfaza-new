'use client';

import { Channel } from '@/types';

const API = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api';

export async function getWatchingNowClient(): Promise<Channel[]> {
  const res = await fetch(`${API}/channels/watching-now`, {
    headers: { Accept: 'application/json' },
    cache: 'no-store'
  });

  if (!res.ok) {
    throw new Error(`API ${res.status}: /channels/watching-now`);
  }

  return res.json();
}