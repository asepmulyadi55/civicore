import React from 'react';

const DEFAULT_ABOUT = `Dwipapuri is more than just a residential area; it's a vibrant ecosystem where families thrive and neighbors become friends. Founded on the principles of inclusivity and sustainability, we pride ourselves on maintaining a safe, green, and engaging environment.

Our community events are designed to bridge the gap between digital convenience and physical connection, ensuring everyone has a seat at the table and a voice in our future.`;

const DEFAULT_STATS = [
    { value: '500+',    label: 'Residents' },
    { value: '24/7',    label: 'Security'  },
    { value: '12',      label: 'Parks'     },
    { value: 'Monthly', label: 'Events'    },
];

const STAT_COLORS = [
    { color: '#2563eb', bg: 'rgba(219,234,254,0.5)' },
    { color: '#4f46e5', bg: 'rgba(224,231,255,0.5)' },
    { color: '#0284c7', bg: 'rgba(224,242,254,0.5)' },
    { color: '#475569', bg: 'rgba(241,245,249,0.5)' },
];

export default function AboutSection({ about = {}, loading }) {
    const rawContent = about?.content || DEFAULT_ABOUT;
    const imageUrl   = about?.image_url || null;
    const stats      = (about?.stats?.length > 0) ? about.stats : DEFAULT_STATS;
    const paragraphs = rawContent.split(/\n\n+/).filter(Boolean);

    return (
        <section id="about" className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 relative overflow-hidden -mt-12">
            {/* Decorative blobs */}
            <div className="absolute -top-20 -right-20 w-80 h-80 rounded-full blur-3xl opacity-50 -z-10"
                style={{ backgroundColor: '#bfdbfe' }}></div>
            <div className="absolute -bottom-20 -left-20 w-80 h-80 rounded-full blur-3xl opacity-50 -z-10"
                style={{ backgroundColor: '#c7d2fe' }}></div>

            <div className="rounded-[3rem] p-8 md:p-16 shadow-xl"
                style={{ background: 'rgba(255,255,255,0.8)', backdropFilter: 'blur(8px)', border: '1px solid rgba(255,255,255,0.5)' }}>
                <div className="flex flex-col lg:flex-row gap-16">
                    {/* Text */}
                    <div className="lg:w-1/2">
                        <h2 className="text-4xl font-bold mb-8" style={{ color: '#1A237E' }}>About Dwipapuri</h2>

                        {loading ? (
                            <div className="space-y-3">
                                <div className="h-4 bg-slate-100 rounded animate-pulse" />
                                <div className="h-4 bg-slate-100 rounded animate-pulse w-5/6" />
                                <div className="h-4 bg-slate-100 rounded animate-pulse w-4/5" />
                                <div className="h-4 bg-slate-100 rounded animate-pulse mt-4" />
                                <div className="h-4 bg-slate-100 rounded animate-pulse w-3/4" />
                            </div>
                        ) : (
                            <div className="space-y-6 text-slate-600 leading-relaxed text-lg">
                                {paragraphs.map((para, i) => (
                                    <p key={i}>{para}</p>
                                ))}
                            </div>
                        )}

                        {/* About image — only shown when set in CMS */}
                        {!loading && imageUrl && (
                            <div className="mt-8 rounded-2xl overflow-hidden shadow-md">
                                <img src={imageUrl} alt="About Dwipapuri" className="w-full object-cover max-h-64" />
                            </div>
                        )}
                    </div>

                    {/* Stats — from CMS or defaults */}
                    <div className="lg:w-1/2 grid grid-cols-2 gap-4">
                        {loading ? (
                            Array.from({ length: 4 }).map((_, i) => (
                                <div key={i} className="p-8 rounded-2xl bg-slate-100 animate-pulse h-32" />
                            ))
                        ) : (
                            stats.map((stat, i) => {
                                const { color, bg } = STAT_COLORS[i % STAT_COLORS.length];
                                return (
                                    <div key={i} className="p-8 rounded-2xl text-center flex flex-col justify-center"
                                        style={{ backgroundColor: bg }}>
                                        <span className="text-3xl font-extrabold mb-1" style={{ color }}>
                                            {stat.value}
                                        </span>
                                        <span className="text-xs font-bold text-slate-400 uppercase tracking-widest">
                                            {stat.label}
                                        </span>
                                    </div>
                                );
                            })
                        )}
                    </div>
                </div>
            </div>
        </section>
    );
}
