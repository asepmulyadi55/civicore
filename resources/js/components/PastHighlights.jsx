import React from 'react';

const PLACEHOLDER_IMAGES = [
    'https://images.unsplash.com/photo-1528605105345-5344ea20e269?w=400&q=80&auto=format',
    'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=400&q=80&auto=format',
    'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=400&q=80&auto=format',
    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80&auto=format',
];

export default function PastHighlights({ pastEvents = [], loading }) {
    const hasEvents = !loading && pastEvents.length > 0;

    return (
        <section className="py-32" style={{ background: 'linear-gradient(to bottom, #ffffff, #f5f5f5)' }}>
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 className="text-3xl font-bold mb-4" style={{ color: '#1A237E' }}>Past Highlights</h2>
                <p className="text-slate-500 max-w-2xl mx-auto mb-16">
                    Relive the moments that make our community special. Browse our gallery and video archives.
                </p>

                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
                    {loading ? (
                        Array.from({ length: 4 }).map((_, i) => (
                            <div key={i} className="aspect-square bg-slate-100 rounded-2xl animate-pulse" />
                        ))
                    ) : hasEvents ? (
                        pastEvents.slice(0, 4).map((event, i) => {
                            const imgSrc = event.image_url || PLACEHOLDER_IMAGES[i % PLACEHOLDER_IMAGES.length];
                            const dateLabel = event.date
                                ? new Date(event.date).toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
                                : null;

                            return (
                                // FIX: `relative` is required so the absolute overlay stays inside this card
                                <div key={event.id || i}
                                    className="relative aspect-square rounded-2xl overflow-hidden shadow-sm group">
                                    <img
                                        src={imgSrc}
                                        alt={event.title}
                                        className="w-full h-full object-cover"
                                    />
                                    {/* Hover overlay — stays within this card because parent is relative */}
                                    <div className="absolute inset-0 bg-black/50 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 p-4">
                                        <p className="text-white font-bold text-sm text-center leading-tight">{event.title}</p>
                                        {dateLabel && (
                                            <p className="text-white/70 text-xs mt-1">{dateLabel}</p>
                                        )}
                                    </div>
                                </div>
                            );
                        })
                    ) : (
                        // Static placeholder cards when no past events in CMS yet
                        <>
                            <div className="aspect-square rounded-2xl overflow-hidden shadow-sm">
                                <img src={PLACEHOLDER_IMAGES[0]} alt="Community gathering" className="w-full h-full object-cover" />
                            </div>
                            <div className="aspect-square rounded-2xl overflow-hidden shadow-sm flex items-center justify-center p-8 text-white text-xs leading-tight uppercase font-medium"
                                style={{ backgroundColor: '#2d5a4c' }}>
                                <div className="border border-white/30 p-4 text-center">
                                    <p className="mb-2">Nov 2024</p>
                                    <p className="text-lg font-bold">Past Event</p>
                                    <p className="mt-2 text-white/60">Community Day</p>
                                </div>
                            </div>
                            <div className="aspect-square rounded-2xl overflow-hidden shadow-sm">
                                <img src={PLACEHOLDER_IMAGES[1]} alt="Festival highlights" className="w-full h-full object-cover" />
                            </div>
                            <div className="aspect-square rounded-2xl overflow-hidden shadow-sm">
                                <img src={PLACEHOLDER_IMAGES[2]} alt="Neighborhood event" className="w-full h-full object-cover" />
                            </div>
                        </>
                    )}
                </div>

                <button
                    className="px-8 py-3 rounded-full font-bold transition-all duration-300"
                    style={{ border: '2px solid #2563eb', color: '#2563eb' }}
                    onMouseEnter={e => { e.currentTarget.style.background = '#2563eb'; e.currentTarget.style.color = '#fff'; }}
                    onMouseLeave={e => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.color = '#2563eb'; }}
                >
                    View Full Archive
                </button>
            </div>
        </section>
    );
}
