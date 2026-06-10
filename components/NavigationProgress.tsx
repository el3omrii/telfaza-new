"use client";

import { useEffect, useRef } from "react";
import { usePathname, useSearchParams } from "next/navigation";
import NProgress from "nprogress";

const MIN_VISIBLE_MS = 250;

function isModifiedClick(event: MouseEvent) {
  return event.metaKey || event.ctrlKey || event.shiftKey || event.altKey;
}

export default function NavigationProgress() {
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const timerRef = useRef<number | null>(null);
  const startedAtRef = useRef<number | null>(null);

  useEffect(() => {
    NProgress.configure({ showSpinner: false, minimum: 0.08, trickleSpeed: 140 });
  }, []);

  useEffect(() => {
    if (timerRef.current !== null) {
      window.clearTimeout(timerRef.current);
      timerRef.current = null;
    }

    if (startedAtRef.current === null) {
      NProgress.done(true);
      return;
    }

    const elapsed = Date.now() - startedAtRef.current;
    const remaining = Math.max(MIN_VISIBLE_MS - elapsed, 0);

    timerRef.current = window.setTimeout(() => {
      NProgress.done(true);
      startedAtRef.current = null;
      timerRef.current = null;
    }, remaining);
  }, [pathname, searchParams?.toString()]);

  useEffect(() => {
    const startProgress = () => {
      if (timerRef.current !== null) {
        window.clearTimeout(timerRef.current);
        timerRef.current = null;
      }

      startedAtRef.current = Date.now();
      NProgress.start();
    };

    const handlePointerDown = (event: PointerEvent) => {
      if (event.button !== 0 || isModifiedClick(event as unknown as MouseEvent)) {
        return;
      }

      const target = event.target;
      if (!(target instanceof Element)) {
        return;
      }

      const anchor = target.closest("a[href]");
      if (!(anchor instanceof HTMLAnchorElement)) {
        return;
      }

      if (anchor.hasAttribute("download") || (anchor.target && anchor.target !== "_self")) {
        return;
      }

      const href = anchor.getAttribute("href");
      if (!href || href.startsWith("mailto:") || href.startsWith("tel:") || href.startsWith("javascript:")) {
        return;
      }

      let nextUrl: URL;
      try {
        nextUrl = new URL(anchor.href, window.location.href);
      } catch {
        return;
      }

      if (nextUrl.origin !== window.location.origin) {
        return;
      }

      const currentUrl = new URL(window.location.href);
      if (nextUrl.pathname === currentUrl.pathname && nextUrl.search === currentUrl.search) {
        return;
      }

      startProgress();
    };

    const handleClick = (event: MouseEvent) => {
      if (event.defaultPrevented || event.button !== 0 || isModifiedClick(event)) {
        return;
      }

      const target = event.target;
      if (!(target instanceof Element)) {
        return;
      }

      const anchor = target.closest("a[href]");
      if (!(anchor instanceof HTMLAnchorElement)) {
        return;
      }

      if (anchor.hasAttribute("download") || (anchor.target && anchor.target !== "_self")) {
        return;
      }

      const href = anchor.getAttribute("href");
      if (!href || href.startsWith("mailto:") || href.startsWith("tel:") || href.startsWith("javascript:")) {
        return;
      }

      let nextUrl: URL;
      try {
        nextUrl = new URL(anchor.href, window.location.href);
      } catch {
        return;
      }

      if (nextUrl.origin !== window.location.origin) {
        return;
      }

      const currentUrl = new URL(window.location.href);
      if (nextUrl.pathname === currentUrl.pathname && nextUrl.search === currentUrl.search) {
        return;
      }

      startProgress();
    };

    document.addEventListener("pointerdown", handlePointerDown, true);
    document.addEventListener("click", handleClick, true);

    return () => {
      document.removeEventListener("pointerdown", handlePointerDown, true);
      document.removeEventListener("click", handleClick, true);
    };
  }, []);

  return null;
}