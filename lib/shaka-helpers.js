import { ShakaP2PEngine } from 'p2p-media-loader-shaka';

let p2pPluginsRegistered = false;

export const RequestType = {
  MANIFEST: 0,
  SEGMENT: 1,
  LICENSE: 2,
};

export function ensureP2PPluginsRegistered() {
  if (p2pPluginsRegistered) return;
  if (typeof window === 'undefined' || !window.shaka) {
    console.warn('window.shaka not yet available; skipping P2P plugin registration this pass');
    return;
  }

  ShakaP2PEngine.registerPlugins(window.shaka);
  p2pPluginsRegistered = true;
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
    manifest: {
      hls: {
        defaultAudioCodec: 'mp4a.40.2',
        ignoreManifestProgramDateTime: true,
        liveSegmentsDelay: 3,
      },
      availabilityWindowOverride: 60,
    },
    preferredAudioCodecs: ['mp4a.40.2'],
    drm: {
      clearKeys,
    },
  };

  if (link?.includes('/hls2/')) {
    config.manifest.retryParameters = { maxAttempts: 5, baseDelay: 2000 };
  }

  return config;
}

export function setupSnrtUrlFix(shakaPlayer) {
  let capturedToken = null;
  const TOKEN_PARAM = 'token';

  shakaPlayer.getNetworkingEngine().registerResponseFilter((type, response) => {
    const originalUri = response.originalUri || '';

    if (!originalUri.includes('/snrt/')) {
      return Promise.resolve();
    }

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
  });

  shakaPlayer.getNetworkingEngine().registerRequestFilter((type, request) => {
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
  });
}

export function destroyP2PEngine(p2pEngineRef) {
  if (!p2pEngineRef.current) return;

  try {
    p2pEngineRef.current.destroy();
  } catch (error) {
    console.warn('Failed to destroy previous P2P engine', error);
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
