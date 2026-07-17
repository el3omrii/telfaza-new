import Link from 'next/link'
import { getCategories } from '@/lib/api'
import { buildMetadata } from '@/lib/seo'

export const metadata = buildMetadata({
  title: 'Categories',
  description: 'Explore live TV channels grouped by genre and category.',
  path: '/categories',
})

export default async function CategoriesPage() {
  const categories = await getCategories()

  return (
    <main className="max-w-7xl w-full mx-auto mt-16 px-6 md:px-16 lg:px-24 xl:px-32">
      <div className="border-b border-white/[0.07] px-5 py-8">
        <h1 className="font-head text-3xl font-extrabold tracking-tight">Categories</h1>
        <p className="mt-1 text-sm text-zinc-500">
          {categories.length} categories · browse by genre
        </p>
      </div>

      <div className="grid grid-cols-2 gap-4 px-5 py-8 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
        {categories.map(cat => (
          <Link
            key={cat.id}
            href={`/categories/${cat.slug}`}
            className="group relative overflow-hidden rounded-xl border border-white/[0.07] bg-zinc-900 p-5 transition-all duration-150 hover:-translate-y-0.5 hover:border-white/15"
          >
            {/* Color accent bar */}
            <div
              className="absolute left-0 top-0 h-full w-1"
              style={{ background: cat.color }}
            />
            {/* Color glow on hover */}
            <div
              className="absolute inset-0 opacity-0 transition-opacity duration-200 group-hover:opacity-5"
              style={{ background: cat.color }}
            />

            <p
              className="font-head mb-1.5 text-base font-bold"
              style={{ color: cat.color }}
            >
              {cat.name}
            </p>
            {cat.description && (
              <p className="mb-2 line-clamp-2 text-xs font-light text-zinc-500">
                {cat.description}
              </p>
            )}
            <p className="text-[11px] text-zinc-500">
              {(cat.channels_count ?? 0).toLocaleString()} channels
            </p>
          </Link>
        ))}
      </div>
    </main>
  )
}
