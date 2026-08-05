"use client";

import { useState, useEffect, useCallback, useRef } from "react";
import Link from "next/link";
import Image from "next/image";
import { storageUrl } from "@/lib/api";
import { hexToRgba } from "@/lib/utils";

/* ─── icons ────────────────────────────────────────────────────────── */
const Icon = {
  Play: () => (
    <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
      <polygon points="5 3 19 12 5 21 5 3" />
    </svg>
  ),
  Bookmark: () => (
    <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
      <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
    </svg>
  ),
  ChevL: () => (
    <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2.5}>
      <path d="M15 18l-6-6 6-6" />
    </svg>
  ),
  ChevR: () => (
    <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2.5}>
      <path d="M9 18l6-6-6-6" />
    </svg>
  ),
};

/* ─── Hero Carousel Client ────────────────────────────────────────────────── */
export default function HeroCarouselClient({ slides }) {
  const [idx, setIdx] = useState(0);
  const [phase, setPhase] = useState("in");
  const timerRef = useRef(null);
  const preloadedLogosRef = useRef(new Set());

  useEffect(() => {
    if (typeof window === "undefined") return;

    slides.forEach((slide) => {
      const logo = slide?.channel?.logo;
      if (!logo) return;

      const logoUrl = storageUrl(logo);
      if (preloadedLogosRef.current.has(logoUrl)) return;

      preloadedLogosRef.current.add(logoUrl);
      const preloader = new window.Image();
      preloader.src = logoUrl;
    });
  }, [slides]);

  const transition = useCallback(
    (nextIdx) => {
      setPhase("out");
      setTimeout(() => {
        setIdx(nextIdx);
        setPhase("in");
      }, 400);
    },
    []
  );

  // 1. Guard against empty or single-slide arrays
  const prev = () => {
    if (slides.length <= 1) return;
    transition((idx - 1 + slides.length) % slides.length);
  };

  const next = useCallback(() => {
    if (slides.length <= 1) return;
    transition((idx + 1) % slides.length);
  }, [idx, transition, slides.length]);

  // 2. Only start the timer if there is more than 1 slide. 
  // Added slides.length to dependencies so it clears the timer if slides update to 1.
  useEffect(() => {
    if (slides.length <= 1) {
      clearInterval(timerRef.current);
      return;
    }

    timerRef.current = setInterval(next, 7000);
    return () => clearInterval(timerRef.current);
  }, [next, slides.length]);

  const resetTimer = (fn) => {
    clearInterval(timerRef.current);
    fn();
    // Only restart the timer if there's more than 1 slide
    if (slides.length > 1) {
      timerRef.current = setInterval(next, 7000);
    }
  };

  // 3. Safer loading state check
  const slide = slides[idx];
  const accentStyles = {
    "--hero-accent": slide.accent,
    "--hero-accent-soft": hexToRgba(slide.accent, 0.18),
    "--hero-accent-border": hexToRgba(slide.accent, 0.28),
    "--hero-accent-shadow": `${slide.accent}66`,
  };

  return (
    <section
      className="relative pt-16 overflow-hidden sm:min-h-[60svh] lg:min-h-[70svh]"
      style={accentStyles}
    >
      {/* ── Background image ── */}
      <div
        className={`absolute inset-0 blur-[8px] transition-[opacity,transform] duration-[550ms] ease-out ${
          phase === "in" ? "scale-100 opacity-100" : "scale-[1.04] opacity-0"
        }`}
      >
        <Image
          src={storageUrl(slide.image)}
          alt={slide.title}
          fill
          className="absolute inset-0 w-full h-full object-cover object-center"
        />

        <div className="absolute inset-0 bg-[linear-gradient(to_right,#0a0a0f_0%,#0a0a0f_18%,rgba(10,10,15,0.93)_32%,rgba(10,10,15,0.72)_50%,rgba(10,10,15,0.34)_68%,rgba(10,10,15,0.08)_84%,transparent_100%)]" />
        <div className="absolute inset-0 bg-[linear-gradient(to_top,#0a0a0f_0%,rgba(10,10,15,0.68)_26%,transparent_58%)]" />
        <div className="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(10,10,15,0.56)_0%,transparent_24%)]" />
        <div className="absolute inset-0 bg-[linear-gradient(to_right,var(--hero-accent)_0%,transparent_35%)] opacity-20" />
      </div>
      <div className="relative z-10 mx-auto grid h-full max-w-7xl grid-cols-[5fr_2fr] items-center gap-2 px-2 py-3 sm:gap-8 sm:px-6 sm:py-8 md:flex md:justify-end md:px-10 lg:px-12 xl:px-16">
        {/* ── Content ── */}
        <div
          className={`flex min-w-0 w-full max-w-3xl flex-col gap-4 transition-[opacity,transform] duration-500 ease-out ${
            phase === "in" ? "translate-y-0 opacity-100" : "translate-y-4 opacity-0"
          }`}
        >
          <div className="flex flex-wrap items-center gap-2 md:gap-4">
            <span className="relative inline-flex h-10 w-[100px] shrink-0 items-center justify-center overflow-hidden rounded-md bg-white/5 px-2 sm:h-12 sm:w-[120px] lg:h-14 lg:w-[140px]">
              <Image
                key={slide.channel.logo}
                src={storageUrl(slide.channel.logo)}
                alt={slide.channel.slug}
                fill
                sizes="(min-width: 1024px) 140px, (min-width: 640px) 120px, 100px"
                priority={idx === 0}
                unoptimized
                className="rounded-md object-contain"
              />
            </span>

            {slide.type && (
              <span className="rounded-md border border-[color:var(--hero-accent-border)] bg-[color:var(--hero-accent-soft)] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-[color:var(--hero-accent)] sm:text-[11px]">
                {slide.type}
              </span>
            )}

            <span className="text-xs text-white/50 sm:text-sm">
              {slide.genres.join(", ")}
            </span>
          </div>
          <div className="inline-flex self-start items-center gap-2 rounded-full border border-red-500/30 bg-red-950/40 px-3 py-1 font-sans text-xs font-bold uppercase tracking-wider text-red-400 backdrop-blur-sm shadow-[0_0_12px_rgba(239,68,68,0.25)]">
          {/*-- Glowing Red Live Dot -- */}
          <span className="relative flex h-2 w-2">
            <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
            <span className="relative inline-flex h-2 w-2 rounded-full bg-red-500"></span>
          </span>
          <span>Now Streaming Live</span>
        </div>
          <h2 className="max-w-2xl text-balance text-[clamp(2.4rem,7vw,4rem)] leading-10 sm:leading-15 md:leading-20 text-white font-black line-clamp-2 md:line-clamp-none drop-shadow-md">
            {slide.title}
          </h2>

          <p className="max-w-xl line-clamp-3 text-sm leading-relaxed text-white/65 sm:text-base">
            {slide.description}
          </p>

          <div className="flex flex-wrap gap-3 sm:gap-4">
            {[
              { label: "Rating", value: slide.rating },
              { label: "Release", value: slide.release },
              { label: "Quality", value: slide.quality },
            ].map(({ label, value }) => (
              <div key={label} className="max-w-[60px] sm:max-w-[92px] md:max-w-[128px] rounded-md sm:rounded-xl border border-white/10 bg-white/8 px-2 py-1 sm:px-4 sm:py-3 backdrop-blur-md">
                <p className="text-tiny sm:text-base font-bold uppercase md:tracking-[0.1em] text-white/40">
                  {label}
                </p>
                <p className="mt-1 text-xs font-bold text-white sm:text-base">
                  {value}
                </p>
              </div>
            ))}
          </div>

          <div className="flex flex-wrap items-center gap-1.5 sm:gap-3 pt-1 mb-8">
            <Link href={`/channels/${slide.channel.slug}`} className="inline-flex items-center gap-2.5 rounded-xl bg-[var(--hero-accent)] px-2 sm:px-6 py-3 sm:py-6 text-sm font-bold uppercase tracking-[0.08em] text-white shadow-[0_0_28px_var(--hero-accent-shadow)] transition hover:brightness-110 active:scale-[0.98] sm:px-7 sm:py-3.5">
              <Icon.Play />
              WATCH NOW
            </Link>
            <button className="rounded-xl border border-white/10 bg-white/8 p-3.5 sm:p-6 text-white/60 backdrop-blur-md transition-colors hover:text-white">
              <Icon.Bookmark />
            </button>
          </div>
        </div>

        {/* ── Poster ── */}
        <div
          className={`relative z-10 min-w-0 h-48 md:h-72 lg:h-96 w-full transition-[opacity,transform] duration-500 ease-out sm:max-w-[280px] md:max-w-xs lg:self-center lg:pb-12 ${
            phase === "in" ? "translate-y-0 opacity-100" : "translate-y-4 opacity-0"
          }`}
        >
          <Image
            src={storageUrl(slide.image)}
            alt={slide.title}
            fill
            className="aspect-[3/4] w-full rounded-2xl border-2 border-white/50 object-cover shadow-[0_24px_60px_rgba(0,0,0,0.45)]"
          />
        </div>
      </div>
      {/* ── Prev / Next controls (Hidden if only 1 slide) ── */}
      {slides.length > 1 && (
        <div className="absolute bottom-4 right-4 z-10 flex items-center gap-2 sm:bottom-6 sm:right-6 md:bottom-8 md:right-8 lg:bottom-10 lg:right-10">
          <button onClick={() => resetTimer(prev)} className="rounded-full border border-white/15 bg-white/10 p-2 text-white transition-transform hover:scale-110 backdrop-blur-md">
            <Icon.ChevL />
          </button>
          <div className="min-w-[5rem] text-center text-xs font-semibold text-white/70">
            <span className="text-lg font-extrabold text-white">{idx + 1}</span>
            <span className="mx-1 text-white/30">/</span>
            <span>{slides.length}</span>
          </div>
          <button onClick={() => resetTimer(next)} className="rounded-full border border-white/15 bg-white/10 p-2 text-white transition-transform hover:scale-110 backdrop-blur-md">
            <Icon.ChevR />
          </button>
        </div>
      )}

      {/* ── Dot indicators (Hidden if only 1 slide) ── */}
      {slides.length > 1 && (
        <div className="absolute bottom-4 left-4 z-10 flex items-center gap-1.5 sm:bottom-6 sm:left-6 md:bottom-8 md:left-12 lg:bottom-10 lg:left-16">
          {slides.map((s, i) => (
            <button
              key={s.id}
              onClick={() => resetTimer(() => transition(i))}
              className={`rounded-full transition-all duration-300 ${
                i === idx ? "h-1.5 w-7 bg-[var(--hero-accent)]" : "h-1.5 w-1.5 bg-white/25 hover:bg-white/40"
              }`}
            />
          ))}
        </div>
      )}

      {/* ── Progress bar (Hidden if only 1 slide) ── */}
      {slides.length > 1 && (
        <div className="absolute inset-x-0 bottom-0 z-10 h-0.5 bg-white/10">
          <div key={`${idx}-progress`} className="h-full w-full rounded-full bg-[var(--hero-accent)] animate-hero-progress" />
        </div>
      )}

    </section>
  );
}
