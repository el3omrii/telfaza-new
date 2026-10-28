import { NextRequest, NextResponse } from 'next/server';
import {
  createHlsGuestSession,
  HLS_SESSION_COOKIE,
  HLS_SESSION_TTL_SECONDS,
  isSameOriginRequest,
} from '@/lib/hls-session';

export async function POST(request: NextRequest) {
  if (!isSameOriginRequest(request)) {
    return NextResponse.json({ error: 'forbidden' }, { status: 403 });
  }

  if (!process.env.HLS_MINT_SALT) {
    console.error('HLS_MINT_SALT is not configured');
    return NextResponse.json({ error: 'server misconfigured' }, { status: 500 });
  }

  const response = new NextResponse(null, {
    status: 204,
    headers: { 'Cache-Control': 'no-store' },
  });

  response.cookies.set(HLS_SESSION_COOKIE, createHlsGuestSession(), {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    path: '/api/hls',
    maxAge: HLS_SESSION_TTL_SECONDS,
  });

  return response;
}
