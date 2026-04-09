import React from 'react';

const PLACEHOLDER_IMAGES = [
    'https://images.unsplash.com/photo-1528605105345-5344ea20e269?w=800&q=80&auto=format',
    'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=800&q=80&auto=format',
    'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=400&q=80&auto=format',
    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80&auto=format',
];

export default function MemorableMoments({ moments = {}, pastEvents = [], loading }) {
    const title      = moments?.title       || 'Memorable Moments';
    const subtitle   = moments?.subtitle    || 'A look back at the experiences that define our community.';
    const archiveUrl = moments?.archive_url || null;
    const images     = moments?.images      || [];

    // Build 4 display images: prefer curated moments images, fall back to past events, then placeholders
    const displayImages = Array.from({ length: 4 }, (_, i) => {
        if (images[i]?.url) return { url: images[i].url, alt: images[i].caption || 'Memorable Moment' };
        if (pastEvents[i])  return { url: pastEvents[i].image_url || PLACEHOLDER_IMAGES[i % PLACEHOLDER_IMAGES.length], alt: pastEvents[i].title || 'Memorable Moment' };
        return { url: PLACEHOLDER_IMAGES[i % PLACEHOLDER_IMAGES.length], alt: 'Memorable Moment' };
    });

    if (loading) {
        return (
            <section className="py-32" style={{ background: '#f3f4f5' }}>
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex flex-col md:flex-row md:items-center justify-between mb-16 gap-6">
                        <div className="space-y-3">
                            <div className="h-9 w-64 bg-slate-200 animate-pulse rounded-xl" />
                            <div className="h-4 w-80 bg-slate-200 animate-pulse rounded-lg" />
                        </div>
                        <div className="h-12 w-48 bg-slate-200 animate-pulse rounded-xl" />
                    </div>
                    <div
                        className="memorable-bento grid grid-cols-1 md:grid-cols-4 gap-4"
                        style={{ gridAutoRows: '250px' }}
                    >
                        <div className="memorable-bento__large rounded-2xl bg-slate-200 animate-pulse" />
                        <div className="memorable-bento__wide rounded-2xl bg-slate-200 animate-pulse" />
                        <div className="rounded-2xl bg-slate-200 animate-pulse" />
                        <div className="rounded-2xl bg-slate-200 animate-pulse" />
                    </div>
                </div>
            </section>
        );
    }

    return (
        <section id="gallery" className="py-16 md:py-32" style={{ background: '#f3f4f5', scrollMarginTop: '80px' }}>
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                {/* Header row */}
                <div className="flex flex-col md:flex-row md:items-center justify-between mb-16 gap-6">
                    <div>
                        <h2
                            className="text-2xl sm:text-3xl md:text-4xl font-bold tracking-tight"
                            style={{ color: '#1a237e', fontFamily: "'Plus Jakarta Sans', sans-serif" }}
                        >
                            {title}
                        </h2>
                        <p className="text-slate-500 mt-2">{subtitle}</p>
                    </div>

                    {archiveUrl && (
                        <a
                            href={archiveUrl}
                            className="group inline-flex items-center gap-2 bg-white font-bold px-8 py-4 rounded-xl transition-all duration-300 hover:shadow-lg"
                            style={{ color: '#000666' }}
                            onMouseEnter={e => { e.currentTarget.style.background = '#000666'; e.currentTarget.style.color = '#ffffff'; }}
                            onMouseLeave={e => { e.currentTarget.style.background = '#ffffff'; e.currentTarget.style.color = '#000666'; }}
                        >
                            View Full Archive
                            <svg
                                className="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    )}
                </div>

                {/* Bento Grid */}
                <div
                    className="memorable-bento grid grid-cols-1 md:grid-cols-4 gap-4"
                    style={{ gridAutoRows: '250px' }}
                >
                    {/* Large image — col-span-2 row-span-2 on md+ */}
                    <div className="memorable-bento__large rounded-2xl overflow-hidden group">
                        <img
                            src={displayImages[0].url}
                            alt={displayImages[0].alt}
                            className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        />
                    </div>

                    {/* Top-right — col-span-2 on md+ */}
                    <div className="memorable-bento__wide rounded-2xl overflow-hidden group">
                        <img
                            src={displayImages[1].url}
                            alt={displayImages[1].alt}
                            className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        />
                    </div>

                    {/* Bottom-right small #1 */}
                    <div className="rounded-2xl overflow-hidden group">
                        <img
                            src={displayImages[2].url}
                            alt={displayImages[2].alt}
                            className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        />
                    </div>

                    {/* Bottom-right small #2 */}
                    <div className="rounded-2xl overflow-hidden group">
                        <img
                            src={displayImages[3].url}
                            alt={displayImages[3].alt}
                            className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        />
                    </div>
                </div>
            </div>
        </section>
    );
}
