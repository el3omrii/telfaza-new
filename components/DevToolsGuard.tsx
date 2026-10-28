'use client';

import { useEffect } from 'react';
import type { DevToolsEvent } from 'devtools-detect';

export default function DevToolsGuard() {
  useEffect(() => {
    if (process.env.NODE_ENV !== 'production') return;

    let active = true;
    let isRedirecting = false;
    let devtools: { isOpen: boolean } | undefined;
    let redirectTimeout: number | undefined;

    const isMobile = () =>
      /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent) ||
      (navigator.maxTouchPoints > 1 && /Macintosh/i.test(navigator.userAgent));

    const isFullscreen = () => Boolean(document.fullscreenElement);

    const scheduleRedirect = () => {
      if (!active || isRedirecting || isMobile() || isFullscreen()) return;

      window.clearTimeout(redirectTimeout);
      redirectTimeout = window.setTimeout(() => {
        if (!active || !devtools?.isOpen || isMobile() || isFullscreen()) return;

        isRedirecting = true;
        debugger;
        window.location.replace('https://google.com');
      }, 750);
    };

    const handleChange = (event: DevToolsEvent) => {
      if (event.detail.isOpen) {
        scheduleRedirect();
      } else {
        window.clearTimeout(redirectTimeout);
      }
    };

    window.addEventListener('devtoolschange', handleChange);

    void import('devtools-detect')
      .then(({ default: detectedDevtools }) => {
        if (!active) return;

        devtools = detectedDevtools;
        if (devtools.isOpen) scheduleRedirect();
      })
      .catch((error: unknown) => {
        console.error('Unable to initialize DevTools detection.', error);
      });

    return () => {
      active = false;
      window.clearTimeout(redirectTimeout);
      window.removeEventListener('devtoolschange', handleChange);
    };
  }, []);

  return null;
}
