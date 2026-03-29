import React from 'react';

export default function FeaturedEvent({ featuredEvent = {}, loading }) {
    const title       = featuredEvent?.title       || 'Dwipapuri Summer Carnival';
    const description = featuredEvent?.description || 'Join us for the most anticipated event of the year! Live music, local food stalls, and community activities for all ages.';
    const date        = featuredEvent?.date        || null;
    const youtubeId   = featuredEvent?.youtube_id  || null;
    const status      = featuredEvent?.status      || 'upcoming';

    const formattedDate = date
        ? new Date(date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' })
        : 'Saturday, August 25th • 4:00 PM';

    const isLive = status === 'ongoing';

    return (
        <section id="events" className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pb-20 -mt-12">
            <div className="bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row">
                {/* Video / YouTube Embed */}
                <div
                    className="lg:w-2/3 relative flex items-center justify-center aspect-video lg:aspect-auto min-h-[340px]"
                    style={{ background: '#1A237E' }}
                >
                    {isLive && (
                        <span
                            className="absolute top-6 left-6 inline-flex items-center px-3 py-1 rounded-full text-xs font-bold tracking-widest uppercase text-white animate-pulse z-10"
                            style={{ backgroundColor: '#FF7043' }}
                        >
                            <span className="w-2 h-2 rounded-full bg-white mr-2"></span>
                            LIVE NOW
                        </span>
                    )}

                    {/* Decorative rings */}
                    <div className="absolute inset-0 opacity-5">
                        <div className="absolute top-10 left-10 w-40 h-40 rounded-full border-4 border-white"></div>
                        <div className="absolute bottom-10 right-10 w-60 h-60 rounded-full border-4 border-white"></div>
                    </div>

                    {youtubeId ? (
                        <iframe
                            className="w-full h-full absolute inset-0"
                            src={`https://www.youtube.com/embed/${youtubeId}`}
                            title={title}
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowFullScreen
                        />
                    ) : (
                        <div className="text-center relative z-10">
                            <div className="w-20 h-20 rounded-full bg-white/20 flex items-center justify-center mx-auto hover:bg-white/30 transition-all duration-300">
                                <svg className="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            </div>
                            <p className="text-white/40 mt-4 text-sm font-medium">YouTube Video Stream Placeholder</p>
                        </div>
                    )}
                </div>

                {/* Event Details */}
                <div className="lg:w-1/3 p-8 md:p-12 flex flex-col justify-center">
                    {loading ? (
                        <div className="space-y-3">
                            <div className="h-4 bg-slate-100 rounded animate-pulse w-1/2" />
                            <div className="h-8 bg-slate-100 rounded animate-pulse" />
                            <div className="h-4 bg-slate-100 rounded animate-pulse w-3/4" />
                            <div className="h-16 bg-slate-100 rounded animate-pulse" />
                        </div>
                    ) : (
                        <>
                            <p className="uppercase tracking-widest text-xs font-bold mb-2" style={{ color: 'rgba(26,35,126,0.6)' }}>
                                Annual Festival 2025
                            </p>
                            <h2 className="text-3xl font-bold mb-4" style={{ color: '#1A237E' }}>
                                {title}
                            </h2>
                            <div className="flex items-center text-slate-500 mb-6 text-sm">
                                <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} />
                                </svg>
                                {formattedDate}
                            </div>
                            <p className="text-slate-600 mb-8 leading-relaxed">{description}</p>
                        </>
                    )}

                    <div className="space-y-3">
                        <button className="w-full text-white font-bold py-3 px-6 rounded-xl hover:opacity-90 transition-opacity"
                            style={{ background: 'linear-gradient(135deg, #3b82f6, #3b5bdb)' }}>
                            Watch Now
                        </button>
                        <div className="flex gap-3">
                            <button className="flex-1 bg-gray-50 text-slate-700 font-semibold py-3 px-4 rounded-xl border border-gray-200 hover:bg-gray-100 transition-colors text-sm">
                                Add to Calendar
                            </button>
                            <button className="flex-1 bg-gray-50 text-slate-700 font-semibold py-3 px-4 rounded-xl border border-gray-200 hover:bg-gray-100 transition-colors text-sm">
                                Share
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
