"use client";

import { useReportWebVitals } from "next/web-vitals";
import { useEffect } from "react";

declare global {
  interface Window {
    dataLayer: unknown[];
    gtag: (...args: unknown[]) => void;
  }
}

export function GoogleAnalytics({ trackingId }: { trackingId: string }) {
  useEffect(() => {
    // Stub gtag immediately so web-vitals events queue in dataLayer
    // even before the real script arrives — nothing is lost.
    // @ts-ignore
    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() {
      // @ts-ignore
      window.dataLayer.push(arguments);
    };
    window.gtag("js", new Date());
    window.gtag("config", trackingId, {
      page_path: window.location.pathname,
    });
    // ...but inject the heavy gtag.js only when the browser is idle.
    const inject = () => {
      if (document.querySelector('script[src*="googletagmanager.com/gtag/js"]')) return;
      const script = document.createElement("script");
      script.async = true;
      script.src = `https://www.googletagmanager.com/gtag/js?id=${trackingId}`;
      document.head.appendChild(script);
    };
    let idleId: number | undefined;
    let onLoad: (() => void) | undefined;
    const ric = (window as unknown as { requestIdleCallback?: Function }).requestIdleCallback;
    if (typeof ric === "function") {
      idleId = ric.call(window, inject, { timeout: 4000 });
    } else if (document.readyState === "complete") {
      inject();
    } else {
      onLoad = inject;
      window.addEventListener("load", onLoad);
    }
    return () => {
      if (idleId !== undefined) window.cancelIdleCallback?.(idleId);
      if (onLoad) window.removeEventListener("load", onLoad);
    };
  }, [trackingId]);

  useReportWebVitals((metric) => {
    if (window.gtag) {
      window.gtag("event", metric.name, {
        value: Math.round(
          metric.name === "CLS" ? metric.value * 1000 : metric.value
        ),
        event_label: metric.id,
        non_interaction: true,
      });
    }
  });

  return null;
}