'use client';

import { useMemo, useState } from 'react';
import Navbar from '../components/Navbar';
import Carousel from '../components/Carousel';
import CategoryFilters from '../components/CategoryFilters';
import CountryFilters from '../components/CountryFilters';
import ChannelCard from '../components/ChannelCard';
import Footer from '../components/Footer';
import {
  featuredChannels,
  categories,
  countries,
  allChannels,
  quickLinks,
  socialLinks,
  getChannelUrl
} from '../lib/data';

const sortChannels = (channels, sortKey) => {
  const list = [...channels];

  if (sortKey === 'alpha') {
    return list.sort((a, b) => a.name.localeCompare(b.name));
  }

  return list.sort((a, b) => {
    const aViews = parseFloat(a.views.replace(/[^0-9.]/g, '')); 
    const bViews = parseFloat(b.views.replace(/[^0-9.]/g, ''));
    return bViews - aViews;
  });
};

export default function HomePage() {
  const [currentCategory, setCurrentCategory] = useState(null);
  const [currentCountry, setCurrentCountry] = useState(null);
  const [currentSort, setCurrentSort] = useState('popular');
  const [currentSearch, setCurrentSearch] = useState('');
  const [visibleChannelCount, setVisibleChannelCount] = useState(10);

  const filteredChannels = useMemo(() => {
    const normalizedSearch = currentSearch.toLowerCase();

    return sortChannels(
      allChannels.filter((channel) => {
        const matchesCategory = currentCategory ? channel.category === currentCategory : true;
        const matchesCountry = currentCountry ? channel.country === currentCountry : true;
        const matchesSearch = normalizedSearch
          ? channel.name.toLowerCase().includes(normalizedSearch) ||
            channel.tags.some((tag) => tag.toLowerCase().includes(normalizedSearch))
          : true;

        return matchesCategory && matchesCountry && matchesSearch;
      }),
      currentSort
    );
  }, [currentCategory, currentCountry, currentSearch, currentSort]);

  const handleCategoryChange = (categoryId) => {
    setCurrentCategory((current) => (current === categoryId ? null : categoryId));
    setVisibleChannelCount(10);
  };

  const handleCountryChange = (countryCode) => {
    setCurrentCountry((current) => (current === countryCode ? null : countryCode));
    setVisibleChannelCount(10);
  };

  const handleSearch = (value) => {
    setCurrentSearch(value);
    setVisibleChannelCount(10);
  };

  const handleSortChange = (sortKey) => {
    setCurrentSort(sortKey);
  };

  const handleLoadMore = () => {
    setVisibleChannelCount((count) => count + 10);
  };

  const availableChannels = filteredChannels.slice(0, visibleChannelCount);

  return (
    <div>
      <Navbar onSearch={handleSearch} />

      <main className="container mx-auto px-4 py-6">
        <section className="mb-12">
          <h2 className="text-2xl font-bold mb-4">Featured Channels</h2>
          <Carousel channels={featuredChannels} />
        </section>

        <section className="mb-12">
          <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
              <h2 className="text-2xl font-bold mb-4">Browse by Category</h2>
              <CategoryFilters
                categories={categories}
                selectedCategory={currentCategory}
                onSelect={handleCategoryChange}
              />
            </div>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-6">
            {allChannels
              .filter((channel) => !currentCategory || channel.category === currentCategory)
              .slice(0, 8)
              .map((channel) => (
                <ChannelCard key={channel.id} channel={channel} href={getChannelUrl(channel)} />
              ))}
          </div>
        </section>

        <section className="mb-12">
          <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
              <h2 className="text-2xl font-bold mb-4">Channels by Country</h2>
              <CountryFilters
                countries={countries}
                selectedCountry={currentCountry}
                onSelect={handleCountryChange}
              />
            </div>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-6">
            {allChannels
              .filter((channel) => !currentCountry || channel.country === currentCountry)
              .slice(0, 8)
              .map((channel) => (
                <ChannelCard key={channel.id} channel={channel} href={getChannelUrl(channel)} />
              ))}
          </div>
        </section>

        <section>
          <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
            <div>
              <h2 className="text-2xl font-bold">All Channels</h2>
            </div>
            <div className="flex flex-wrap items-center gap-3">
              <span className="text-gray-400">Sort by:</span>
              <button
                type="button"
                onClick={() => handleSortChange('popular')}
                className={`px-4 py-2 rounded-full border border-gray-700 text-white transition ${
                  currentSort === 'popular' ? 'bg-primary text-white' : 'bg-gray-900 hover:border-primary hover:text-primary'
                }`}
              >
                Most Popular
              </button>
              <button
                type="button"
                onClick={() => handleSortChange('alpha')}
                className={`px-4 py-2 rounded-full border border-gray-700 text-white transition ${
                  currentSort === 'alpha' ? 'bg-primary text-white' : 'bg-gray-900 hover:border-primary hover:text-primary'
                }`}
              >
                A-Z
              </button>
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            {availableChannels.map((channel) => (
              <ChannelCard key={channel.id} channel={channel} href={getChannelUrl(channel)} />
            ))}
          </div>

          <div className="text-center mt-8">
            <button
              type="button"
              onClick={handleLoadMore}
              disabled={visibleChannelCount >= filteredChannels.length}
              className="bg-primary text-white px-6 py-3 rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Load More Channels
            </button>
          </div>
        </section>
      </main>

      <Footer quickLinks={quickLinks} countries={countries} socialLinks={socialLinks} />
    </div>
  );
}
