import React from 'react';

const CATEGORY_COLORS = {
    wellness:  { text: '#10b981', border: '#10b981' },
    meetings:  { text: '#4f46e5', border: '#4f46e5' },
    education: { text: '#f97316', border: '#f97316' },
    cultural:  { text: '#8b5cf6', border: '#8b5cf6' },
    sports:    { text: '#0ea5e9', border: '#0ea5e9' },
    other:     { text: '#64748b', border: '#64748b' },
};

const PLACEHOLDER_IMAGES = [
    'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=600&q=80&auto=format',
    'https://images.unsplash.com/photo-1431540015161-0bf868a2d407?w=600&q=80&auto=format',
    'https://images.unsplash.com/photo-1416331108676-a22ccb276e35?w=600&q=80&auto=format',
];

function SkeletonCard() {
    return (
        <div className="bg-white rounded-2xl overflow-hidden shadow-sm border-t-4 border-slate-200 animate-pulse">
            <div className="w-full aspect-[4/3] bg-slate-100" />
            <div className="p-6 space-y-3">
                <div className="h-3 bg-slate-100 rounded w-1/3" />
                <div className="h-5 bg-slate-100 rounded w-3/4" />
                <div className="h-3 bg-slate-100 rounded w-1/2" />
                <div className="h-10 bg-slate-100 rounded mt-4" />
            </div>
        </div>
    );
}

export default function UpcomingEvents({ events = [], loading }) {
    return (
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 -mt-12">
            <div className="flex flex-col md:flex-row md:items-end justify-between mb-12">
                <div>
                    <h2 className="text-3xl font-bold mb-2" style={{ color: '#1A237E' }}>Upcoming Events</h2>
                    <p className="text-slate-500">Don't miss out on what's happening next.</p>
                </div>

            </div>

            {loading ? (
                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <SkeletonCard /><SkeletonCard /><SkeletonCard />
                </div>
            ) : events.length === 0 ? (
                <div className="text-center py-16 bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <svg className="w-12 h-12 mx-auto text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p className="text-slate-400 font-semibold">No Upcoming Events</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {events.map((event, i) => {
                        const category = (event.category || 'other').toLowerCase();
                        const colors   = CATEGORY_COLORS[category] || CATEGORY_COLORS.other;
                        // Use CMS image_url if provided, else fallback to placeholder
                        const image    = event.image_url || PLACEHOLDER_IMAGES[i % PLACEHOLDER_IMAGES.length];
                        const formattedDate = event.date
                            ? new Date(event.date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                            : 'Date TBD';

                        return (
                            <article
                                key={event.id || i}
                                className="bg-white rounded-2xl overflow-hidden shadow-sm lift-on-hover"
                                style={{ borderTop: `4px solid ${colors.border}` }}
                            >
                                <img
                                    src={image}
                                    alt={event.title}
                                    className="w-full object-cover"
                                    style={{ aspectRatio: '4/3' }}
                                />
                                <div className="p-6">
                                    <span className="text-xs font-bold uppercase tracking-widest mb-2 block"
                                        style={{ color: colors.text }}>
                                        {category}
                                    </span>
                                    <h3 className="text-xl font-bold mb-2" style={{ color: '#1A237E' }}>
                                        {event.title}
                                    </h3>
                                    <p className="text-slate-500 text-sm mb-6">{formattedDate}</p>
                                    {event.url ? (
                                        <a href={event.url} target="_blank" rel="noopener noreferrer"
                                            className="block w-full py-2.5 bg-slate-50 text-slate-600 rounded-lg font-semibold text-sm hover:bg-slate-100 transition-colors text-center">
                                            Learn More
                                        </a>
                                    ) : (
                                        <button disabled className="w-full py-2.5 bg-slate-50/50 text-slate-300 rounded-lg font-semibold text-sm cursor-default">
                                            Learn More
                                        </button>
                                    )}
                                </div>
                            </article>
                        );
                    })}
                </div>
            )}
        </section>
    );
}
