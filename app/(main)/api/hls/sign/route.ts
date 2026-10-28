import { NextRequest, NextResponse } from 'next/server';
import crypto from 'crypto';
import {
  createHlsGuestSession,
  getHlsGuestSessionExpiry,
  HLS_SESSION_COOKIE,
  isSameOriginRequest,
  HLS_SESSION_TTL_SECONDS,
} from '@/lib/hls-session';

const SALT = process.env.HLS_MINT_SALT;
const STREAM_ORIGIN = process.env.HLS_STREAM_ORIGIN || 'https://stream.telfazalive.com';
const TTL_SECONDS = 1800; // 30 min — pragmatic middle ground from the freshness discussion

export async function POST(req: NextRequest) {
  if (!isSameOriginRequest(req)) {
    return NextResponse.json({ error: 'forbidden' }, { status: 403 });
  }

  const sessionExpiresAt = req.cookies
    .getAll(HLS_SESSION_COOKIE)
    .map(({ value }) => getHlsGuestSessionExpiry(value))
    .find((expiresAt) => expiresAt !== undefined);
  if (!sessionExpiresAt) {
    return NextResponse.json({ error: 'guest session required' }, { status: 401 });
  }

  let body: unknown;
  try {
    body = await req.json();
  } catch {
    return NextResponse.json({ error: 'invalid request body' }, { status: 400 });
  }

  const uri =
    body && typeof body === 'object' && 'uri' in body
      ? body.uri
      : undefined;

  if (
    typeof uri !== 'string' ||
    !/^\/(?:[A-Za-z0-9_-]+\/)*[A-Za-z0-9_-]+(?:\.[A-Za-z0-9_-]+)*\.m3u8$/.test(uri)
  ) {
    return NextResponse.json({ error: 'invalid uri' }, { status: 400 });
  }

  if (!SALT) {
    console.error('HLS_MINT_SALT is not configured');
    return NextResponse.json({ error: 'server misconfigured' }, { status: 500 });
  }

  const exp = Math.floor(Date.now() / 1000) + TTL_SECONDS;
  // Must mirror nginx byte-for-byte:
  //   secure_link_md5 "$secure_link_expires$uri SALT_MINT_ICI";
  const sig = crypto
    .createHash('md5')
    .update(`${exp}${uri} ${SALT}`)
    .digest('base64url');

  const url = `${STREAM_ORIGIN}${uri}?sig=${sig}&exp=${exp}`;
  const response = NextResponse.json(
    { url, exp },
    { headers: { 'Cache-Control': 'no-store' } }
  );

  if (sessionExpiresAt - Math.floor(Date.now() / 1000) < 60 * 60) {
    response.cookies.set(HLS_SESSION_COOKIE, createHlsGuestSession(), {
      httpOnly: true,
      secure: process.env.NODE_ENV === 'production',
      sameSite: 'lax',
      path: '/',
      maxAge: HLS_SESSION_TTL_SECONDS,
    });
  }

  return response;
}
