import React from 'react';

const DEFAULT_CONTENT = `Dwipapuri isn't just a location; it's a curated ecosystem where modern technology meets soulful living. We prioritize seamless experiences, professional management, and a vibrant community spirit that turns neighbors into lifelong friends.`;

const DEFAULT_STATS = [
    { value: '500+',    label: 'Residents' },
    { value: '24/7',    label: 'Security'  },
    { value: '12',      label: 'Parks'     },
    { value: 'Monthly', label: 'Events'    },
];

// Matches v2: top-left navy, top-right grey (offset), bottom-left grey, bottom-right violet (offset)
const STAT_CARD_STYLES = [
    { background: '#1a237e', color: '#8690ee',  offset: false },
    { background: '#e1e3e4', color: '#191c1d',  offset: true  },
    { background: '#e1e3e4', color: '#191c1d',  offset: false },
    { background: '#5f00e3', color: '#ffffff',  offset: true  },
];

const STAT_ICONS = ['group', 'shield', 'park', 'event_repeat'];

export default function AboutSection({ about = {}, loading }) {
    const rawContent = about?.content || DEFAULT_CONTENT;
    const stats      = (about?.stats?.length > 0) ? about.stats : DEFAULT_STATS;
    const paragraphs = rawContent.split(/\n\n+/).filter(Boolean);

    return (
        <section id="about" className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-32" style={{ scrollMarginTop: '80px' }}>
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

                {/* Left — text */}
                <div className="space-y-8">
                    <div>
                        <span className="font-bold tracking-widest uppercase text-xs mb-3 block"
                            style={{ color: '#5f00e3', fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                            Our Identity
                        </span>
                        <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight mb-6 lg:mb-8"
                            style={{ color: '#1a237e', fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                            Elevating Residential Living at Dwipapuri
                        </h2>

                        {loading ? (
                            <div className="space-y-3">
                                <div className="h-4 bg-slate-100 rounded animate-pulse" />
                                <div className="h-4 bg-slate-100 rounded animate-pulse w-5/6" />
                                <div className="h-4 bg-slate-100 rounded animate-pulse w-4/5" />
                            </div>
                        ) : (
                            <div className="space-y-5 leading-relaxed font-light" style={{ color: '#454652', fontSize: '1.0625rem' }}>
                                {paragraphs.map((para, i) => <p key={i}>{para}</p>)}
                            </div>
                        )}
                    </div>

                    <div className="flex flex-col sm:flex-row gap-4">
                        <button
                            className="px-8 py-4 rounded-xl font-bold text-white transition-all hover:opacity-90"
                            style={{ background: '#000666', fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                            Explore Amenities
                        </button>
                        <button
                            className="px-8 py-4 rounded-xl font-bold transition-all hover:border-opacity-80"
                            style={{
                                border: '1px solid rgba(198,197,212,0.6)',
                                color: '#191c1d',
                                fontFamily: "'Plus Jakarta Sans', sans-serif",
                            }}
                            onMouseEnter={e => e.currentTarget.style.borderColor = '#000666'}
                            onMouseLeave={e => e.currentTarget.style.borderColor = 'rgba(198,197,212,0.6)'}
                        >
                            Our History
                        </button>
                    </div>
                </div>

                {/* Right — 2×2 stat grid */}
                <div className="grid grid-cols-2 gap-4">
                    {loading ? (
                        Array.from({ length: 4 }).map((_, i) => (
                            <div key={i} className={`rounded-3xl bg-slate-100 animate-pulse h-48 ${i % 2 === 1 ? 'mt-8' : ''}`} />
                        ))
                    ) : (
                        stats.map((stat, i) => {
                            const style = STAT_CARD_STYLES[i % STAT_CARD_STYLES.length];
                            const icon  = STAT_ICONS[i % STAT_ICONS.length];
                            return (
                                <div key={i}
                                    className={`p-5 sm:p-8 rounded-3xl flex flex-col justify-between h-36 sm:h-48 transition-transform hover:-translate-y-1 ${style.offset ? 'mt-6 sm:mt-8' : ''}`}
                                    style={{ background: style.background }}>
                                    <span className="material-icons text-4xl opacity-50" style={{ color: style.color }}>
                                        {icon}
                                    </span>
                                    <div>
                                        <div className="text-2xl sm:text-3xl font-extrabold"
                                            style={{ color: style.color, fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                                            {stat.value}
                                        </div>
                                        <div className="text-sm uppercase tracking-widest font-bold opacity-80"
                                            style={{ color: style.color }}>
                                            {stat.label}
                                        </div>
                                    </div>
                                </div>
                            );
                        })
                    )}
                </div>
            </div>
        </section>
    );
}
