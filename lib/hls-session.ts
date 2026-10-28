import 'server-only';

import { createHmac, randomBytes, timingSafeEqual } from 'node:crypto';

export const HLS_SESSION_COOKIE = 'h_g_session';
export const HLS_SESSION_TTL_SECONDS = 12 * 60 * 60;

function getSigningKey() {
  const salt = process.env.HLS_MINT_SALT;
  if (!salt) {
    throw new Error('HLS_MINT_SALT is not configured');
  }

  return createHmac('sha256', salt)
    .update('telfaza-hls-guest-session-v1')
    .digest();
}

function sign(payload: string) {
  return createHmac('sha256', getSigningKey())
    .update(payload)
    .digest('base64url');
}

export function createHlsGuestSession() {
  const expiresAt = Math.floor(Date.now() / 1000) + HLS_SESSION_TTL_SECONDS;
  const payload = `${expiresAt}.${randomBytes(24).toString('base64url')}`;
  return `${payload}.${sign(payload)}`;
}

export function getHlsGuestSessionExpiry(token: string | undefined) {
  if (!token) return undefined;

  const match = /^(\d+)\.([A-Za-z0-9_-]{32})\.([A-Za-z0-9_-]{43})$/.exec(token);
  if (!match) return undefined;

  const [, expiresAtText, , signature] = match;
  const expiresAt = Number(expiresAtText);
  const now = Math.floor(Date.now() / 1000);

  if (
    !Number.isSafeInteger(expiresAt) ||
    expiresAt <= now ||
    expiresAt > now + HLS_SESSION_TTL_SECONDS
  ) {
    return undefined;
  }

  const payload = token.slice(0, token.lastIndexOf('.'));
  const expected = Buffer.from(sign(payload), 'base64url');
  const received = Buffer.from(signature, 'base64url');

  return received.length === expected.length && timingSafeEqual(received, expected)
    ? expiresAt
    : undefined;
}

export function isSameOriginRequest(request: Request) {
  const originHeader = request.headers.get('origin');
  const fetchSite = request.headers.get('sec-fetch-site');
  const requestUrl = new URL(request.url);
  const forwardedHost = request.headers.get('x-forwarded-host')?.split(',')[0].trim();
  const forwardedProto = request.headers.get('x-forwarded-proto')?.split(',')[0].trim();
  const host = forwardedHost || request.headers.get('host') || requestUrl.host;
  const protocol = forwardedProto || requestUrl.protocol.slice(0, -1);

  if (!originHeader || (fetchSite && fetchSite !== 'same-origin')) return false;

  try {
    const origin = new URL(originHeader);
    const requestOrigin = new URL(`${protocol}://${host}`).origin;
    return origin.origin === requestOrigin;
  } catch {
    return false;
  }
}
