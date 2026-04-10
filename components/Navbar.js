'use client';

import { navigationItems } from '../lib/data';

export default function Navbar({ onSearch }) {
  return (
    <header className="bg-dark sticky top-0 z-50 shadow-lg">
      <div className="container mx-auto px-4 py-3">
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div className="flex items-center flex-wrap gap-8">
            <a href="#" className="text-primary text-3xl font-bold">
              StreamTV
            </a>
            <nav>
              <ul className="flex flex-wrap gap-4 text-white">
                {navigationItems.map((item) => (
                  <li key={item.label}>
                    <a
                      href={item.href}
                      className={`transition ${item.active ? 'text-primary' : 'hover:text-primary'}`}
                    >
                      {item.label}
                    </a>
                  </li>
                ))}
              </ul>
            </nav>
          </div>

          <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div className="relative w-full sm:w-72">
              <input
                type="text"
                placeholder="Search channels..."
                onChange={(event) => onSearch(event.target.value)}
                className="bg-gray-800 text-white px-4 py-2 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-primary"
              />
              <span className="absolute right-3 top-2.5 text-gray-400">🔍</span>
            </div>
            <button className="bg-primary text-white px-4 py-2 rounded hover:bg-red-700 transition">
              Sign In
            </button>
            <div className="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-xl text-gray-300">
              👤
            </div>
          </div>
        </div>
      </div>
    </header>
  );
}
