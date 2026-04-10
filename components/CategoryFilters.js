'use client';

export default function CategoryFilters({ categories, selectedCategory, onSelect }) {
  return (
    <div className="flex flex-wrap gap-3">
      {categories.map((category) => (
        <button
          key={category.id}
          type="button"
          onClick={() => onSelect(category.id)}
          className={`px-4 py-2 rounded-full border border-gray-700 text-white transition ${
            selectedCategory === category.id ? 'bg-primary text-white' : 'bg-gray-900 hover:border-primary hover:text-primary'
          }`}
        >
          {category.name} ({category.count})
        </button>
      ))}
    </div>
  );
}
