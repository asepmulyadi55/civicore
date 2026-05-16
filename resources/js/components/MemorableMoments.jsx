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
        <section id="gallery" className="py-16 md:py-32" style={{ background: '#F4F3EF', scrollMarginTop: '80px' }}>
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                {/* Header */}
                <div className="text-center max-w-2xl mx-auto mb-12 md:mb-20">
                    <span
                        className="font-semibold tracking-widest uppercase text-xs mb-3 md:mb-4 block"
                        style={{ color: '#D4AF37', fontFamily: "'Plus Jakarta Sans', sans-serif" }}
                    >
                        The Gallery
                    </span>
                    <h2
                        className="text-2xl sm:text-3xl md:text-4xl font-medium tracking-tight mb-3 md:mb-4"
                        style={{ color: '#1C2D27', fontFamily: "'Plus Jakarta Sans', sans-serif" }}
                    >
                        {title}
                    </h2>
                    <p className="font-light text-sm md:text-base leading-relaxed" style={{ color: '#595959' }}>{subtitle}</p>
                </div>

                {archiveUrl && (
                    <div className="flex justify-center mb-12">
                        <a
                            href={archiveUrl}
                            className="group inline-flex items-center gap-2 font-medium text-sm px-8 py-3 rounded-lg transition-all hover:opacity-90"
                            style={{ background: '#1C2D27', color: '#FAF9F6', fontFamily: "'Plus Jakarta Sans', sans-serif" }}
                        >
                            View Full Archive
                            <span className="material-symbols-outlined text-sm transition-transform group-hover:translate-x-1">arrow_forward</span>
                        </a>
                    </div>
                )}

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
