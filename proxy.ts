import { NextRequest, NextResponse } from 'next/server';
import {
  createHlsGuestSession,
  getHlsGuestSessionExpiry,
  HLS_SESSION_COOKIE,
  HLS_SESSION_TTL_SECONDS,
} from '@/lib/hls-session';

const SESSION_REFRESH_THRESHOLD_SECONDS = 60 * 60;

export function proxy(request: NextRequest) {
  if (!process.env.HLS_MINT_SALT) {
    return NextResponse.next();
  }

  const session = request.cookies.get(HLS_SESSION_COOKIE)?.value;
  const expiresAt = getHlsGuestSessionExpiry(session);
  const now = Math.floor(Date.now() / 1000);

  if (expiresAt && expiresAt - now > SESSION_REFRESH_THRESHOLD_SECONDS) {
    return NextResponse.next();
  }

  const response = NextResponse.next();
  response.cookies.set(HLS_SESSION_COOKIE, createHlsGuestSession(), {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    path: '/',
    maxAge: HLS_SESSION_TTL_SECONDS,
  });

  return response;
}

export const config = {
  matcher: ['/((?!api|_next/static|_next/image|favicon.ico|.*\\..*).*)'],
};
