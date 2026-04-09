import React from 'react';

const CATEGORY_BADGE_STYLES = {
    wellness:  { background: '#000666', color: '#ffffff' },
    meetings:  { background: '#5f00e3', color: '#ffffff' },
    education: { background: '#5c1800', color: '#ffb59d' },
    cultural:  { background: '#1a237e', color: '#bdc2ff' },
    sports:    { background: '#000666', color: '#ffffff' },
    other:     { background: '#454652', color: '#ffffff' },
};

const PLACEHOLDER_IMAGES = [
    'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=600&q=80&auto=format',
    'https://images.unsplash.com/photo-1431540015161-0bf868a2d407?w=600&q=80&auto=format',
    'https://images.unsplash.com/photo-1416331108676-a22ccb276e35?w=600&q=80&auto=format',
];

function SkeletonCard() {
    return (
        <div className="bg-white rounded-2xl overflow-hidden animate-pulse" style={{ border: '1px solid rgba(198,197,212,0.10)' }}>
            <div className="w-full h-64 bg-slate-100" />
            <div className="p-8 space-y-4">
                <div className="h-3 bg-slate-100 rounded w-1/4" />
                <div className="h-5 bg-slate-100 rounded w-3/4" />
                <div className="h-3 bg-slate-100 rounded w-full" />
                <div className="h-3 bg-slate-100 rounded w-5/6" />
                <div className="h-8 bg-slate-100 rounded w-1/3 mt-4" />
            </div>
        </div>
    );
}

export default function UpcomingEvents({ events = [], loading }) {
    return (
        <section id="events" className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20" style={{ scrollMarginTop: '80px' }}>
            <div className="flex items-end justify-between mb-12">
                <div className="max-w-2xl">
                    <span className="font-bold tracking-widest uppercase text-xs mb-3 block"
                        style={{ color: '#5f00e3', fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                        Discover More
                    </span>
                    <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold tracking-tight"
                        style={{ color: '#1a237e', fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                        Upcoming Community Events
                    </h2>
                </div>
            </div>

            {loading ? (
                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <SkeletonCard /><SkeletonCard /><SkeletonCard />
                </div>
            ) : events.length === 0 ? (
                <div className="text-center py-16 bg-white rounded-2xl shadow-sm" style={{ border: '1px solid rgba(198,197,212,0.10)' }}>
                    <svg className="w-12 h-12 mx-auto text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p className="text-slate-400 font-semibold">No Upcoming Events</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {events.map((event, i) => {
                        const category   = (event.category || 'other').toLowerCase();
                        const badgeStyle = CATEGORY_BADGE_STYLES[category] || CATEGORY_BADGE_STYLES.other;
                        const image      = event.image_url || PLACEHOLDER_IMAGES[i % PLACEHOLDER_IMAGES.length];

                        return (
                            <article key={event.id || i}
                                className="bg-white rounded-2xl overflow-hidden group flex flex-col h-full lift-on-hover transition-all duration-500"
                                style={{ border: '1px solid rgba(198,197,212,0.10)' }}>

                                {/* Image with category badge overlay */}
                                <div className="relative h-64 overflow-hidden flex-shrink-0">
                                    <img src={image} alt={event.title}
                                        className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                    <div className="absolute top-4 left-4 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-widest"
                                        style={{
                                            ...badgeStyle,
                                            fontFamily: "'Plus Jakarta Sans', sans-serif",
                                        }}>
                                        {category}
                                    </div>
                                </div>

                                {/* Card body */}
                                <div className="p-8 flex flex-col flex-grow">
                                    <h3 className="text-xl font-bold mb-4"
                                        style={{ color: '#191c1d', fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                                        {event.title}
                                    </h3>
                                    {event.description && (
                                        <p className="text-sm leading-relaxed mb-6 flex-grow italic"
                                            style={{ color: '#454652' }}>
                                            &ldquo;{event.description}&rdquo;
                                        </p>
                                    )}

                                    {/* Action row */}
                                    <div className="flex items-center justify-between mt-auto">
                                        {event.url ? (
                                            <a href={event.url} target="_blank" rel="noopener noreferrer"
                                                className="group/btn font-bold text-sm flex items-center gap-2 transition-all hover:gap-3"
                                                style={{ color: '#5f00e3', fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                                                RSVP Now
                                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                                </svg>
                                            </a>
                                        ) : (
                                            <span className="font-bold text-sm"
                                                style={{ color: '#767683', fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                                                Details TBA
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </article>
                        );
                    })}
                </div>
            )}
        </section>
    );
}
