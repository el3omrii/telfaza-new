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
        <section  className="relative overflow-hidden flex items-center justify-center" 
          style={{ height: "80svh", minHeight: 560, background: "#0a0a0f" }}>
          
<div role="status" class="w-full px-12 animate-pulse">
    <div class="flex flex-row items-center justify-between w-full h-full">        
    
    <div class="w-full">
        <div class="h-8 bg-neutral-500 rounded-sm w-16 mb-4"></div>
        <div class="h-4 bg-neutral-500 rounded-sm max-w-[480px] my-4"></div>
        <div class="h-2 bg-neutral-500 rounded-full max-w-sm mb-4"></div>
        <div class="h-2 bg-neutral-500 rounded-full max-w-[440px] mb-4"></div>
        <div class="h-2 bg-neutral-500 rounded-full max-w-[460px] mb-4"></div>
        <div class="flex flex-row gap-4 max-w-sm mb-8">
          <div class="h-8 bg-neutral-500 rounded-sm w-16"></div>
          <div class="h-8 bg-neutral-500 rounded-sm w-16"></div>
          <div class="h-8 bg-neutral-500 rounded-sm w-16"></div>
        </div>
        <div class="h-16 bg-neutral-500 rounded-xl max-w-[240px]"></div>
    </div>
    <div class="flex items-center justify-center w-full h-48 bg-neutral-500 rounded-xl sm:w-96">
        <svg class="w-24 h-24 text-white/50" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 16 5-7 6 6.5m6.5 2.5L16 13l-4.286 6M14 10h.01M4 19h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z"/></svg>
    </div>
    </div>
    <span class="sr-only">Loading...</span>
</div>


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