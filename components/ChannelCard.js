'use client';

import Link from 'next/link';

export default function ChannelCard({ channel, featured = false, href }) {
  const card = (
    <article className={`bg-gray-900 rounded-3xl overflow-hidden shadow-xl transition hover:shadow-2xl ${featured ? 'h-full' : ''}`}>
      <img src={channel.logo} alt={channel.name} className="w-full h-44 object-cover" />
      <div className="p-5">
        {featured && (
          <span className="inline-flex items-center px-3 py-1 rounded-full bg-primary/15 text-primary text-xs font-semibold mb-3">
            Featured
          </span>
        )}
        <h3 className="text-lg font-semibold mb-2">{channel.name}</h3>
        <p className="text-gray-400 mb-3 text-sm">{channel.description}</p>
        <div className="flex flex-wrap gap-2 mb-4">
          {channel.tags.map((tag) => (
            <span key={tag} className="text-xs bg-gray-800 text-gray-200 px-2 py-1 rounded-full">
              {tag}
            </span>
          ))}
        </div>
        <div className="flex items-center justify-between text-sm text-gray-400">
          <span>{channel.country}</span>
          <span>{channel.views} views</span>
        </div>
      </div>
    </article>
  );

  return href ? (
    <Link href={href} className="block transition hover:shadow-2xl">
      {card}
    </Link>
  ) : (
    card
  );
}
