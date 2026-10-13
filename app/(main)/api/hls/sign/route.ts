import { NextRequest, NextResponse } from 'next/server';
import crypto from 'crypto';

const SALT = process.env.HLS_MINT_SALT;
const STREAM_ORIGIN = process.env.HLS_STREAM_ORIGIN || 'https://stream.telfazalive.com';
const TTL_SECONDS = 1800; // 30 min — pragmatic middle ground from the freshness discussion

export async function GET(req: NextRequest) {
  const streamId = req.nextUrl.searchParams.get('streamId');

  if (!streamId || !/.*\.m3u8$/.test(streamId)) {
    return NextResponse.json({ error: 'invalid streamId' }, { status: 400 });
  }
  if (!SALT) {
    console.error('HLS_MINT_SALT is not configured');
    return NextResponse.json({ error: 'server misconfigured' }, { status: 500 });
  }

  const exp = Math.floor(Date.now() / 1000) + TTL_SECONDS;
  // Must mirror nginx byte-for-byte:
  //   secure_link_md5 "$secure_link_expires$stream_id SALT_MINT_ICI";
  const sig = crypto
    .createHash('md5')
    .update(`${exp}${streamId} ${SALT}`)
    .digest('base64url');

  const url = `${STREAM_ORIGIN}${streamId}?sig=${sig}&exp=${exp}`;
  return NextResponse.json(
    { url, exp },
    { headers: { 'Cache-Control': 'no-store' } }
  );
}
