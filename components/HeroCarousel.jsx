"use client";
import { useState, useEffect, useCallback, useRef } from "react";

const SLIDES = [
  {
    id: 1,
    title: "Daemons of the Shadow Realm",
    type: "TV",
    genres: ["Adventure", "Comedy", "Fantasy"],
    description:
      "In an isolated village, two twins were born, separated by day and night. It is years later, and while the older brother Yuru has become a hunter of animals, his sister Asa has been locked away in a cage, ordered to perform a special duty that prohibits her from ever seeing sunlight.",
    rating: "?",
    release: "2026",
    quality: "HD",
    cc: 5,
    ep: 4,
    image: "https://media0101.elcinema.com/uploads/deea510acc3949f937460c0cc4bb7a5075d24715d4cc51296bca5f066913d5fb.jpg",
    accent: "#e8490f",
  },
  {
    id: 2,
    title: "Celestial Blade Chronicles",
    type: "TV",
    genres: ["Action", "Supernatural", "Drama"],
    description:
      "A young swordsman discovers an ancient blade that grants him visions of a forgotten war between gods and mortals. As the boundary between realms dissolves, he must master powers that could reshape the very fabric of existence itself.",
    rating: "8.4",
    release: "2025",
    quality: "FHD",
    cc: 12,
    ep: 8,
    image: "https://images.unsplash.com/photo-1560169897-fc0cdbdfa4d5?w=1400&q=80",
    accent: "#3b82f6",
  },
  {
    id: 3,
    title: "Neon Ghost Protocol",
    type: "ONA",
    genres: ["Sci-Fi", "Thriller", "Mystery"],
    description:
      "In Neo-Osaka 2087, a disgraced detective with a cybernetic eye that reads memories takes one last case: a string of murders where the victims have no digital footprint. The deeper she digs, the more she questions what is real.",
    rating: "9.1",
    release: "2025",
    quality: "4K",
    cc: 24,
    ep: 3,
    image: "https://images.unsplash.com/photo-1518770660439-4636190af475?w=1400&q=80",
    accent: "#8b5cf6",
  },
  {
    id: 4,
    title: "Sakura Storm Division",
    type: "Movie",
    genres: ["Romance", "Action", "Historical"],
    description:
      "During the final days of the Edo period, a fierce kunoichi trained in the lost art of Hana-ryu must protect the last shogun's heir while navigating a web of political intrigue, forbidden love, and an enemy who cannot be killed by mortal hands.",
    rating: "7.8",
    release: "2024",
    quality: "HD",
    cc: 0,
    ep: 1,
    image: "https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=1400&q=80",
    accent: "#ec4899",
  },
  {
    id: 5,
    title: "Void Walker: Last Epoch",
    type: "TV",
    genres: ["Isekai", "Fantasy", "Adventure"],
    description:
      "Summoned to a dying world held together by crumbling magic seals, an ordinary librarian discovers she can read the universe's source code. With ten seals left and nine already shattered, every decision she makes rewrites destiny itself.",
    rating: "8.9",
    release: "2026",
    quality: "HD",
    cc: 7,
    ep: 12,
    image: "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=1400&q=80",
    accent: "#10b981",
  },
];

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
  const [idx, setIdx] = useState(0);
  const [phase, setPhase] = useState("in"); // "in" | "out"
  const timerRef = useRef(null);

  const transition = useCallback(
    (next) => {
      setPhase("out");
      setTimeout(() => {
        setIdx(next);
        setPhase("in");
      }, 400);
    },
    []
  );

  const prev = () => transition((idx - 1 + SLIDES.length) % SLIDES.length);
  const next = useCallback(() => transition((idx + 1) % SLIDES.length), [idx, transition]);

  useEffect(() => {
    timerRef.current = setInterval(next, 7000);
    return () => clearInterval(timerRef.current);
  }, [next]);

  const resetTimer = (fn) => {
    clearInterval(timerRef.current);
    fn();
    timerRef.current = setInterval(next, 7000);
  };

  const slide = SLIDES[idx];

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

        {/* Left-heavy gradient fade — makes text on left legible */}
        <div
          className="absolute inset-0"
          style={{
            background:
              "linear-gradient(to right, #0a0a0f 0%, #0a0a0f 15%, rgba(10,10,15,0.93) 30%, rgba(10,10,15,0.72) 48%, rgba(10,10,15,0.35) 65%, rgba(10,10,15,0.1) 82%, transparent 100%)",
          }}
        />
        {/* Bottom vignette */}
        <div
          className="absolute inset-0"
          style={{
            background:
              "linear-gradient(to top, #0a0a0f 0%, rgba(10,10,15,0.65) 28%, transparent 55%)",
          }}
        />
        {/* Top vignette (for nav) */}
        <div
          className="absolute inset-0"
          style={{
            background: "linear-gradient(to bottom, rgba(10,10,15,0.55) 0%, transparent 22%)",
          }}
        />
        {/* Accent tint on far left edge */}
        <div
          className="absolute inset-0"
          style={{
            background: `linear-gradient(to right, ${slide.accent}22 0%, transparent 35%)`,
          }}
        />
      </div>

      {/* ── Content ── */}
      <div
        className="relative z-10 h-full flex flex-col justify-end pb-10 px-6 md:px-12 lg:px-20"
        style={{ maxWidth: 700 }}
      >
        <div style={contentStyle}>
          {/* Badges row */}
          <div className="flex flex-wrap items-center gap-2 mb-3">
            {slide.cc > 0 && (
              <span
                className="px-2 py-0.5 rounded-md text-[11px] font-bold"
                style={{
                  background: "rgba(33,150,243,0.2)",
                  color: "#60a5fa",
                  border: "1px solid rgba(33,150,243,0.3)",
                  fontFamily: "system-ui, sans-serif",
                }}
              >
                CC {slide.cc}
              </span>
            )}
            {slide.ep > 0 && (
              <span
                className="px-2 py-0.5 rounded-md text-[11px] font-bold"
                style={{
                  background: "rgba(76,175,80,0.2)",
                  color: "#4ade80",
                  border: "1px solid rgba(76,175,80,0.3)",
                  fontFamily: "system-ui, sans-serif",
                }}
              >
                EP {slide.ep}
              </span>
            )}
            <span
              className="text-xs font-bold"
              style={{ color: "rgba(255,255,255,0.5)", fontFamily: "system-ui, sans-serif" }}
            >
              {slide.type}
            </span>
            <span style={{ color: "rgba(255,255,255,0.25)", fontSize: 10 }}>•</span>
            <span
              className="text-xs"
              style={{ color: "rgba(255,255,255,0.45)", fontFamily: "system-ui, sans-serif" }}
            >
              {slide.genres.join(", ")}
            </span>
          </div>

          {/* Title */}
          <h1
            className="leading-none mb-4 text-white"
            style={{
              fontFamily: "'Bebas Neue', 'Impact', system-ui, sans-serif",
              fontSize: "clamp(2.6rem, 6vw, 5rem)",
              letterSpacing: "0.04em",
              textShadow: "0 2px 40px rgba(0,0,0,0.9)",
            }}
          >
            {slide.title}
          </h1>

          {/* Description */}
          <p
            className="mb-6 leading-relaxed line-clamp-3"
            style={{
              fontFamily: "system-ui, sans-serif",
              fontSize: 14,
              color: "rgba(255,255,255,0.65)",
              maxWidth: 520,
            }}
          >
            {slide.description}
          </p>

          {/* Stats row */}
          <div className="flex flex-wrap gap-3 mb-7">
            {[
              { label: "Rating", value: slide.rating },
              { label: "Release", value: slide.release },
              { label: "Quality", value: slide.quality },
            ].map(({ label, value }) => (
              <div
                key={label}
                className="rounded-xl px-4 py-2.5"
                style={{
                  background: "rgba(255,255,255,0.07)",
                  backdropFilter: "blur(12px)",
                  border: "1px solid rgba(255,255,255,0.09)",
                  minWidth: 76,
                }}
              >
                <p
                  style={{
                    fontSize: 10,
                    fontWeight: 700,
                    color: "rgba(255,255,255,0.4)",
                    textTransform: "uppercase",
                    letterSpacing: "0.1em",
                    fontFamily: "system-ui, sans-serif",
                  }}
                >
                  {label}
                </p>
                <p
                  style={{
                    fontSize: 16,
                    fontWeight: 700,
                    color: "#fff",
                    fontFamily: "system-ui, sans-serif",
                    marginTop: 2,
                  }}
                >
                  {value}
                </p>
              </div>
            ))}
          </div>

          {/* CTA buttons */}
          <div className="flex items-center gap-3">
            <button
              className="flex items-center gap-2.5 px-7 py-3.5 rounded-xl font-bold text-sm text-white transition-all active:scale-95 hover:brightness-110"
              style={{
                background: slide.accent,
                boxShadow: `0 0 28px ${slide.accent}60`,
                fontFamily: "system-ui, sans-serif",
                letterSpacing: "0.05em",
              }}
            >
              <Icon.Play />
              WATCH NOW
            </button>
            <button
              className="p-3.5 rounded-xl text-white/60 hover:text-white transition-colors"
              style={{
                background: "rgba(255,255,255,0.08)",
                backdropFilter: "blur(12px)",
                border: "1px solid rgba(255,255,255,0.1)",
              }}
            >
              <Icon.Bookmark />
            </button>
          </div>
        </div>
      </div>

      {/* ── Prev / Next controls ── */}
      <div className="absolute bottom-10 right-6 md:right-12 lg:right-20 z-10 flex items-center gap-3">
        <button
          onClick={() => resetTimer(prev)}
          className="p-2 rounded-full transition-all hover:scale-110"
          style={{
            background: "rgba(255,255,255,0.1)",
            border: "1px solid rgba(255,255,255,0.15)",
            backdropFilter: "blur(8px)",
          }}
        >
          <Icon.ChevL />
        </button>
        <div
          style={{
            fontFamily: "system-ui, sans-serif",
            fontSize: 13,
            fontWeight: 600,
            color: "rgba(255,255,255,0.7)",
          }}
        >
          <span style={{ fontSize: 20, fontWeight: 800, color: "#fff" }}>{idx + 1}</span>
          <span style={{ color: "rgba(255,255,255,0.3)", margin: "0 3px" }}>/</span>
          <span>{SLIDES.length}</span>
        </div>
        <button
          onClick={() => resetTimer(next)}
          className="p-2 rounded-full transition-all hover:scale-110"
          style={{
            background: "rgba(255,255,255,0.1)",
            border: "1px solid rgba(255,255,255,0.15)",
            backdropFilter: "blur(8px)",
          }}
        >
          <Icon.ChevR />
        </button>
      </div>

      {/* ── Dot indicators ── */}
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

      {/* ── Progress bar ── */}
      <div
        className="absolute bottom-0 inset-x-0 h-0.5 z-10"
        style={{ background: "rgba(255,255,255,0.06)" }}
      >
        <div
          key={`${idx}-progress`}
          className="h-full rounded-full"
          style={{
            background: slide.accent,
            animation: "heroProgress 7s linear infinite",
          }}
        />
      </div>

      <style>{`
        @keyframes heroProgress { from { width: 0% } to { width: 100% } }
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap');
      `}</style>
    </section>
  );
}