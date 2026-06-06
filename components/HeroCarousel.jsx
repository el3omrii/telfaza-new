import { getSlides } from '@/lib/api';
import Link from "next/link";
import Image from "next/image";
import { storageUrl } from "@/lib/api";
import HeroCarouselClient from "./HeroCarouselClient";

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
export default async function HeroCarousel() {
  try {
    const response = await getSlides();
    const slides = response.data;
    
    if (!slides || slides.length === 0) {
      return (
        <section 
          className="relative overflow-hidden flex items-center justify-center" 
          style={{ height: "80svh", minHeight: 560, background: "#0a0a0f" }}
        >
          <p className="text-white/50 text-lg">No content available</p>
        </section>
      );
    }

    return <HeroCarouselClient slides={slides} />;
  } catch (error) {
    console.error("Failed to fetch slides:", error);
    return (
      <section 
        className="relative overflow-hidden flex items-center justify-center" 
        style={{ height: "80svh", minHeight: 560, background: "#0a0a0f" }}
      >
        <p className="text-white/50 text-lg">Failed to load content</p>
      </section>
    );
  }
}