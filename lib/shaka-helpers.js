import { ShakaP2PEngine } from 'p2p-media-loader-shaka';

let p2pPluginsRegistered = false;

export const RequestType = {
  MANIFEST: 0,
  SEGMENT: 1,
  LICENSE: 2,
};

export function ensureP2PPluginsRegistered(shaka) {
  if (p2pPluginsRegistered) return;

  ShakaP2PEngine.registerPlugins(shaka);
  p2pPluginsRegistered = true;
}

export function createP2PEngine({ shaka, player, channelId }) {
  const engine = new ShakaP2PEngine(
    {
      core: {
        swarmId: `telfaza-live-${channelId}-swarm`,
        announceTrackers: ['wss://tracker.webtorrent.dev', 'wss://tracker.openwebtorrent.com'],
      },
    },
    shaka,
  );

  engine.bindShakaPlayer(player);
  return engine;
}

export function shouldRetrySource(errorDetail) {
  if (!errorDetail || typeof errorDetail.code === 'undefined') {
    return false;
  }

  const isGeoBlocked = errorDetail.code === 1002 && errorDetail.data?.[1] === 403;
  const isRestricted = errorDetail.code === 4000;
  const isCritical = errorDetail.severity === 2;

  return isGeoBlocked || isRestricted || isCritical;
}

export function buildShakaConfig({ link, drm, clearkeys }) {
  const clearKeys = drm && clearkeys ? clearkeys : {};
  const config = {
    drm: {
      clearKeys,
    },
  };

  if (link?.includes('/hls2/')) {
    config.manifest = {
      retryParameters: { maxAttempts: 5, baseDelay: 2000 },
    };
  }

  return config;
}

export function getUniqueRenditions(tracks) {
  const byHeight = new Map();
  for (const track of tracks) {
    if (!track.height) continue;
    const previousTrack = byHeight.get(track.height);
    if (!previousTrack || (track.bandwidth ?? 0) > (previousTrack.bandwidth ?? 0)) {
      byHeight.set(track.height, track);
    }
  }

  return [...byHeight.values()]
    .sort((a, b) => b.height - a.height)
    .map((track) => ({ id: String(track.id), height: track.height }));
}

/* ─── Signed entry URLs for /local/ streams ─────────────────────────────── */
const signedUrlCache = new Map(); // uri -> { url, exp }
const STREAM_ORIGIN = process.env.HLS_STREAM_ORIGIN || 'https://stream.telfazalive.com';
let hlsSessionPromise;

async function ensureHlsGuestSession() {
  if (!hlsSessionPromise) {
    hlsSessionPromise = fetch('/api/hls/session', {
      method: 'POST',
      credentials: 'same-origin',
    }).then((response) => {
      if (!response.ok) {
        throw new Error(`HLS session API failed: ${response.status}`);
      }
    });
  }

  try {
    await hlsSessionPromise;
  } catch (error) {
    hlsSessionPromise = undefined;
    throw error;
  }
}

async function requestSignedStream(uri) {
  return fetch('/api/hls/sign', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ uri }),
  });
}

export async function resolveStreamUrl(link) {
  if (!link || !link.includes(STREAM_ORIGIN)) return link;

  const uri = new URL(link).pathname;
  if (!uri) return link;

  // Already signed (e.g. pre-signed by the data layer)? Use as-is.
  if (/[?&](sig|exp)=/.test(link)) return link;

  const now = Math.floor(Date.now() / 1000);
  const cached = signedUrlCache.get(uri);
  if (cached && cached.exp - now > 60) return cached.url;

  await ensureHlsGuestSession();
  let res = await requestSignedStream(uri);
  if (res.status === 401) {
    hlsSessionPromise = undefined;
    await ensureHlsGuestSession();
    res = await requestSignedStream(uri);
  }
  if (!res.ok) throw new Error(`sign API failed: ${res.status}`);
  const { url, exp } = await res.json();
  signedUrlCache.set(uri, { url, exp });
  return url;
}


export function setupSnrtUrlFix(shakaPlayer) {
  let capturedToken = null;
  const TOKEN_PARAM = 'token';
  const networkingEngine = shakaPlayer.getNetworkingEngine();
  if (!networkingEngine) {
    throw new Error('Shaka networking engine is not available');
  }

  const responseFilter = (type, response) => {
    const originalUri = response.originalUri || '';

    if (!originalUri.includes('/snrt/')) return Promise.resolve();

    if (type === RequestType.MANIFEST) {
      const finalUrl = response.uri;
      if (!finalUrl) return Promise.resolve();

      try {
        const url = new URL(finalUrl);
        url.searchParams.get('token');
        url.searchParams.get('token_path');
        url.searchParams.get('expires');
        const match = finalUrl.match(new RegExp(`[?&]${TOKEN_PARAM}=(.+)`));
        capturedToken = match ? match[1] : null;
      } catch (error) {
        console.log(error);
      }

      if (capturedToken) {
        console.log('✅ Token captured:', capturedToken);
        shakaPlayer._debugToken = capturedToken;
      }
    }

    return Promise.resolve();
  };

  const requestFilter = (type, request) => {
    if (!capturedToken || capturedToken.trim() === '') return;
    if (type === RequestType.LICENSE || type === RequestType.SEGMENT) return;

    console.log('✏️  RequestFilter:', { type, uris: request.uris });

    request.uris.forEach((uri, index) => {
      if (uri.includes(`${TOKEN_PARAM}=`)) return;

      try {
        const url = new URL(uri);
        url.searchParams.set(TOKEN_PARAM, capturedToken);
        request.uris[index] = url.toString();
      } catch {
        const separator = uri.includes('?') ? '&' : '?';
        request.uris[index] = `${uri}${separator}${TOKEN_PARAM}=${capturedToken}`;
      }
    });
  };

  networkingEngine.registerResponseFilter(responseFilter);
  networkingEngine.registerRequestFilter(requestFilter);

  return () => {
    networkingEngine.unregisterResponseFilter(responseFilter);
    networkingEngine.unregisterRequestFilter(requestFilter);
  };
}

export async function destroyShakaPlayer(player, video) {
  video?.pause();

  try {
    if (player) await player.destroy();
  } finally {
    if (video) {
      video.removeAttribute('src');
      video.srcObject = null;
      video.load();
    }
  }
}

export function destroyP2PEngine(p2pEngineRef) {
  if (!p2pEngineRef.current) return;

  try {
    p2pEngineRef.current.destroy();
  } catch (error) {
    console.warn('Failed to destroy P2P engine', error);
  } finally {
    p2pEngineRef.current = null;
  }
}

export function attachAnalyticsTracking({ video, api, channel, link, setPlayEventSent }) {
  if (!api) return () => {};

  const handlePlay = () => {
    setPlayEventSent((previousValue) => {
      if (previousValue) return previousValue;

      if (typeof window.parent !== 'undefined' && window.parent.gtag) {
        window.parent.gtag('event', 'video_play', {
          channel_name: channel.name || 'unknown',
          channel_id: channel.id || 'unknown',
          source_url: link || 'unknown',
          video_title: channel.name || 'unknown',
          event_category: 'video',
          event_label: 'play',
        });
      }

      return true;
    });
  };

  const handleError = (event) => {
    const errorDetail = event?.detail ?? event;
    if (!errorDetail || typeof errorDetail.code === 'undefined') return;

    if (typeof window.parent !== 'undefined' && window.parent.gtag) {
      window.parent.gtag('event', 'video_error', {
        channel_name: channel.name || 'unknown',
        channel_id: channel.id || 'unknown',
        source_url: link || 'unknown',
        error_code: errorDetail.code,
        error_message: errorDetail.message || 'Unknown error',
        error_severity: errorDetail.severity || 'unknown',
        event_category: 'video',
        event_label: 'error',
      });
    }
  };

  video.addEventListener('play', handlePlay);
  api.addEventListener('error', handleError);

  return () => {
    video.removeEventListener('play', handlePlay);
    api.removeEventListener('error', handleError);
  };
}
