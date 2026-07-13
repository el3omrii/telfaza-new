"use client";

import Link from "next/link";

export function SectionHead({ title, href }: { title: string; href?: string }) {
  return (
    <div className="relative mb-8 flex items-baseline justify-between">
      <h2 className="font-head text-2xl font-bold tracking-tight pb-4 border-b border-gray-700 after:content-['']
           after:absolute after:bottom-0 after:left-0
           after:h-1.5 after:w-32
           after:bg-red-500 ">{title}</h2>
      {href && (
        <Link href={href} className="text-xs text-zinc-500 transition-colors hover:text-teal-400">
          View all →
        </Link>
      )}
    </div>
  );
}
