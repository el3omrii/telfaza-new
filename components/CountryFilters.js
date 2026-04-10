'use client';

export default function CountryFilters({ countries, selectedCountry, onSelect }) {
  return (
    <div className="flex flex-wrap gap-3">
      {countries.map((country) => (
        <button
          key={country.code}
          type="button"
          onClick={() => onSelect(country.code)}
          className={`px-4 py-2 rounded-full border border-gray-700 text-white transition ${
            selectedCountry === country.code ? 'bg-primary text-white' : 'bg-gray-900 hover:border-primary hover:text-primary'
          }`}
        >
          {country.name}
        </button>
      ))}
    </div>
  );
}
