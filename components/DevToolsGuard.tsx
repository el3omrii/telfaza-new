'use client';

import { useEffect } from 'react';

export default function DevToolsGuard() {
  useEffect(() => {
    // Only run this logic in your production build
    if (process.env.NODE_ENV !== 'production') return;

    let isRedirecting = false;

    const checkDevTools = async () => {
      // Dynamically import the package so it does not break Next.js Server-Side Rendering (SSR)
      const devtools = (await import('devtools-detect')).default;

      // 1. Initial check right when the page loads
      if (devtools.isOpen && !isRedirecting) {
        isRedirecting = true;
        window.location.replace('https://google.com');
        return;
      }

      // 2. Continuous listener that fires immediately if DevTools is opened later
      const handleChange = (event: any) => {
        if (event.detail.isOpen && !isRedirecting) {
          isRedirecting = true;
          window.location.replace('https://google.com');
        }
      };

      window.addEventListener('devtoolschange', handleChange);

      // Clean up the event listener if the component unmounts
      return () => {
        window.removeEventListener('devtoolschange', handleChange);
      };
    };

    checkDevTools();
  }, []);

  return null; // This component runs silently in the background and renders nothing visually
}
