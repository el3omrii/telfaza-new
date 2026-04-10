'use client';

export default function Footer({ quickLinks, countries, socialLinks }) {
  return (
    <footer className="bg-dark py-10 mt-12">
      <div className="container mx-auto px-4">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div>
            <h3 className="text-primary text-lg font-bold mb-4">StreamTV</h3>
            <p className="text-gray-400">
              Watch thousands of TV channels from around the world. Live streaming, on-demand content, and curated
              collections.
            </p>
          </div>

          <div>
            <h4 className="text-white font-semibold mb-4">Quick Links</h4>
            <ul>
              {quickLinks.map((link) => (
                <li key={link.label} className="mb-2">
                  <a href={link.href} className="text-gray-400 hover:text-white transition">
                    {link.label}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h4 className="text-white font-semibold mb-4">Countries</h4>
            <ul>
              {countries.slice(0, 6).map((country) => (
                <li key={country.code} className="mb-2 text-gray-400">
                  {country.name}
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h4 className="text-white font-semibold mb-4">Connect</h4>
            <ul>
              {socialLinks.map((item) => (
                <li key={item.label} className="mb-3">
                  <a href={item.href} className="text-gray-400 hover:text-white transition">
                    {item.label}
                  </a>
                </li>
              ))}
            </ul>
            <div className="mt-4">
              <p className="text-gray-400 mb-2">Subscribe to our newsletter</p>
              <div className="flex flex-col gap-2 sm:flex-row">
                <input
                  type="email"
                  placeholder="Your email"
                  className="bg-gray-800 text-white px-3 py-2 rounded-l w-full focus:outline-none"
                />
                <button className="bg-primary text-white px-4 py-2 rounded-r hover:bg-red-700 transition">
                  Subscribe
                </button>
              </div>
            </div>
          </div>
        </div>

        <div className="border-t border-gray-800 mt-8 pt-6 text-center text-gray-400">
          <p>&copy; 2023 StreamTV. All channel content is provided by broadcasters.</p>
        </div>
      </div>
    </footer>
  );
}
