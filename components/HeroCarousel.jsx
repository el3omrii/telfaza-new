"use client";
import { useState, useEffect, useCallback, useRef } from "react";
import { getSlides } from '@/lib/api';

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

/* ─── Hero Carousel ────────────────────────────────────────────────── */
export default function HeroCarousel() {
  const [SLIDES, setSLIDES] = useState([]);
  const [idx, setIdx] = useState(0);
  const [phase, setPhase] = useState("in");
  const timerRef = useRef(null);

  useEffect(() => {
    async function fetchSlides() {
      try {
        const slides = await getSlides();
        setSLIDES(slides.data);
      } catch (error) {
        console.error("Failed to fetch slides:", error);
      }
    }
    fetchSlides();
  }, []);

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
    if (SLIDES.length <= 1) return;
    transition((idx - 1 + SLIDES.length) % SLIDES.length);
  };

  const next = useCallback(() => {
    if (SLIDES.length <= 1) return;
    transition((idx + 1) % SLIDES.length);
  }, [idx, transition, SLIDES.length]);

  // 2. Only start the timer if there is more than 1 slide. 
  // Added SLIDES.length to dependencies so it clears the timer if slides update to 1.
  useEffect(() => {
    if (SLIDES.length <= 1) {
      clearInterval(timerRef.current);
      return;
    }

    timerRef.current = setInterval(next, 7000);
    return () => clearInterval(timerRef.current);
  }, [next, SLIDES.length]);

  const resetTimer = (fn) => {
    clearInterval(timerRef.current);
    fn();
    // Only restart the timer if there's more than 1 slide
    if (SLIDES.length > 1) {
      timerRef.current = setInterval(next, 7000);
    }
  };

  // 3. Safer loading state check
  const slide = SLIDES[idx];

  if (SLIDES.length === 0 || !slide) {
    return (
      <section 
        className="relative overflow-hidden flex items-center justify-center" 
        style={{ height: "80svh", minHeight: 560, background: "#0a0a0f" }}
      >
        <p className="text-white/50 text-lg animate-pulse">Loading featured content...</p>
      </section>
    );
  }

  const imgStyle = {
    opacity: phase === "in" ? 1 : 0,
    transform: phase === "in" ? "scale(1)" : "scale(1.04)",
    transition: "opacity 0.55s ease, transform 0.55s ease",
  };

  const contentStyle = {
    opacity: phase === "in" ? 1 : 0,
    transform: phase === "in" ? "translateY(0)" : "translateY(16px)",
    transition: phase === "in"
      ? "opacity 0.5s ease 0.12s, transform 0.5s ease 0.12s"
      : "opacity 0.35s ease, transform 0.3s ease",
  };

  return (
    <section className="relative overflow-hidden" style={{ height: "80svh", minHeight: 560 }}>
      {/* ── Background image ── */}
      <div className="absolute inset-0" style={imgStyle}>
        <img
          src={slide.image}
          alt={slide.title}
          className="absolute inset-0 w-full h-full object-cover object-center"
        />

        <div className="absolute inset-0" style={{ background: "linear-gradient(to right, #0a0a0f 0%, #0a0a0f 15%, rgba(10,10,15,0.93) 30%, rgba(10,10,15,0.72) 48%, rgba(10,10,15,0.35) 65%, rgba(10,10,15,0.1) 82%, transparent 100%)" }} />
        <div className="absolute inset-0" style={{ background: "linear-gradient(to top, #0a0a0f 0%, rgba(10,10,15,0.65) 28%, transparent 55%)" }} />
        <div className="absolute inset-0" style={{ background: "linear-gradient(to bottom, rgba(10,10,15,0.55) 0%, transparent 22%)" }} />
        <div className="absolute inset-0" style={{ background: `linear-gradient(to right, ${slide.accent}22 0%, transparent 35%)` }} />
      </div>

      {/* ── Content ── */}
      <div className="relative z-10 h-full flex flex-col justify-end pb-10 px-6 md:px-12 lg:px-20" style={{ maxWidth: 700 }}>
        <div style={contentStyle}>
          <div className="flex flex-wrap items-center gap-2 mb-3">
            {slide.cc > 0 && (
              <span className="px-2 py-0.5 rounded-md text-[11px] font-bold" style={{ background: "rgba(33,150,243,0.2)", color: "#60a5fa", border: "1px solid rgba(33,150,243,0.3)", fontFamily: "system-ui, sans-serif" }}>
                CC {slide.cc}
              </span>
            )}
            {slide.ep > 0 && (
              <span className="px-2 py-0.5 rounded-md text-[11px] font-bold" style={{ background: "rgba(76,175,80,0.2)", color: "#4ade80", border: "1px solid rgba(76,175,80,0.3)", fontFamily: "system-ui, sans-serif" }}>
                EP {slide.ep}
              </span>
            )}
            <span className="text-xs font-bold" style={{ color: "rgba(255,255,255,0.5)", fontFamily: "system-ui, sans-serif" }}>
              {slide.type}
            </span>
            <span style={{ color: "rgba(255,255,255,0.25)", fontSize: 10 }}>•</span>
            <span className="text-xs" style={{ color: "rgba(255,255,255,0.45)", fontFamily: "system-ui, sans-serif" }}>
              {slide.genres.join(", ")}
            </span>
          </div>

          <h1 className="leading-none mb-4 text-white" style={{ fontFamily: "'Bebas Neue', 'Impact', system-ui, sans-serif", fontSize: "clamp(2.6rem, 6vw, 5rem)", letterSpacing: "0.04em", textShadow: "0 2px 40px rgba(0,0,0,0.9)" }}>
            {slide.title}
          </h1>

          <p className="mb-6 leading-relaxed line-clamp-3" style={{ fontFamily: "system-ui, sans-serif", fontSize: 14, color: "rgba(255,255,255,0.65)", maxWidth: 520 }}>
            {slide.description}
          </p>

          <div className="flex flex-wrap gap-3 mb-7">
            {[
              { label: "Rating", value: slide.rating },
              { label: "Release", value: slide.release },
              { label: "Quality", value: slide.quality },
            ].map(({ label, value }) => (
              <div key={label} className="rounded-xl px-4 py-2.5" style={{ background: "rgba(255,255,255,0.07)", backdropFilter: "blur(12px)", border: "1px solid rgba(255,255,255,0.09)", minWidth: 76 }}>
                <p style={{ fontSize: 10, fontWeight: 700, color: "rgba(255,255,255,0.4)", textTransform: "uppercase", letterSpacing: "0.1em", fontFamily: "system-ui, sans-serif" }}>
                  {label}
                </p>
                <p style={{ fontSize: 16, fontWeight: 700, color: "#fff", fontFamily: "system-ui, sans-serif", marginTop: 2 }}>
                  {value}
                </p>
              </div>
            ))}
          </div>

          <div className="flex items-center gap-3">
            <button className="flex items-center gap-2.5 px-7 py-3.5 rounded-xl font-bold text-sm text-white transition-all active:scale-95 hover:brightness-110" style={{ background: slide.accent, boxShadow: `0 0 28px ${slide.accent}60`, fontFamily: "system-ui, sans-serif", letterSpacing: "0.05em" }}>
              <Icon.Play />
              WATCH NOW
            </button>
            <button className="p-3.5 rounded-xl text-white/60 hover:text-white transition-colors" style={{ background: "rgba(255,255,255,0.08)", backdropFilter: "blur(12px)", border: "1px solid rgba(255,255,255,0.1)" }}>
              <Icon.Bookmark />
            </button>
          </div>
        </div>
      </div>

      {/* ── Prev / Next controls (Hidden if only 1 slide) ── */}
      {SLIDES.length > 1 && (
        <div className="absolute bottom-10 right-6 md:right-12 lg:right-20 z-10 flex items-center gap-3">
          <button onClick={() => resetTimer(prev)} className="p-2 rounded-full transition-all hover:scale-110" style={{ background: "rgba(255,255,255,0.1)", border: "1px solid rgba(255,255,255,0.15)", backdropFilter: "blur(8px)" }}>
            <Icon.ChevL />
          </button>
          <div style={{ fontFamily: "system-ui, sans-serif", fontSize: 13, fontWeight: 600, color: "rgba(255,255,255,0.7)" }}>
            <span style={{ fontSize: 20, fontWeight: 800, color: "#fff" }}>{idx + 1}</span>
            <span style={{ color: "rgba(255,255,255,0.3)", margin: "0 3px" }}>/</span>
            <span>{SLIDES.length}</span>
          </div>
          <button onClick={() => resetTimer(next)} className="p-2 rounded-full transition-all hover:scale-110" style={{ background: "rgba(255,255,255,0.1)", border: "1px solid rgba(255,255,255,0.15)", backdropFilter: "blur(8px)" }}>
            <Icon.ChevR />
          </button>
        </div>
      )}

      {/* ── Dot indicators (Hidden if only 1 slide) ── */}
      {SLIDES.length > 1 && (
        <div className="absolute bottom-6 left-6 md:left-12 lg:left-20 z-10 flex items-center gap-1.5">
          {SLIDES.map((s, i) => (
            <button
              key={s.id}
              onClick={() => resetTimer(() => transition(i))}
              className="rounded-full transition-all duration-300"
              style={{
                width: i === idx ? 28 : 6,
                height: 6,
                background: i === idx ? slide.accent : "rgba(255,255,255,0.25)",
              }}
            />
          ))}
        </div>
      )}

      {/* ── Progress bar (Hidden if only 1 slide) ── */}
      {SLIDES.length > 1 && (
        <div className="absolute bottom-0 inset-x-0 h-0.5 z-10" style={{ background: "rgba(255,255,255,0.06)" }}>
          <div
            key={`${idx}-progress`}
            className="h-full rounded-full"
            style={{
              background: slide.accent,
              animation: "heroProgress 7s linear infinite",
            }}
          />
        </div>
      )}

      <style>{`
        @keyframes heroProgress { from { width: 0% } to { width: 100% } }
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap');
      `}</style>
    </section>
  );
}