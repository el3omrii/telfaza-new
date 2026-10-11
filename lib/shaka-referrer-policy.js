const REFERRER_POLICY_HEADER = 'x-telfaza-shaka-referrer-policy';
const FETCH_WRAPPER_MARKER = Symbol.for('telfaza.shaka-referrer-policy-fetch');

if (typeof window !== 'undefined' && typeof window.fetch === 'function' && !window.fetch[FETCH_WRAPPER_MARKER]) {
  const originalFetch = window.fetch.bind(window);
  const fetchWithReferrerPolicy = (input, init) => {
    const headers = init?.headers instanceof Headers
      ? init.headers
      : new Headers(init?.headers);
    const referrerPolicy = headers.get(REFERRER_POLICY_HEADER);

    if (!referrerPolicy) {
      return originalFetch(input, init);
    }

    headers.delete(REFERRER_POLICY_HEADER);
    return originalFetch(input, {
      ...init,
      headers,
      referrerPolicy,
    });
  };

  Object.defineProperty(fetchWithReferrerPolicy, FETCH_WRAPPER_MARKER, { value: true });
  window.fetch = fetchWithReferrerPolicy;
}

export function configureShakaReferrerPolicy(player, policy) {
  if (typeof window === 'undefined' || !window.fetch?.[FETCH_WRAPPER_MARKER]) {
    throw new Error('Per-request Shaka referrer policies require the Fetch API');
  }

  const networkingEngine = player?.getNetworkingEngine?.();
  if (!networkingEngine) {
    throw new Error('Shaka networking engine is not available');
  }

  const requestFilter = (_type, request) => {
    request.headers[REFERRER_POLICY_HEADER] = policy;
  };

  networkingEngine.registerRequestFilter(requestFilter);
  return () => networkingEngine.unregisterRequestFilter(requestFilter);
}
