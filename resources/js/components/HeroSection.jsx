import React from 'react';

const DEFAULTS = {
    title: 'Dwipapuri Community Events',
    subtitle: 'Bringing neighbors together through curated gatherings, cultural \
celebrations, and digital experiences. Stay connected with your community.',
    cta_text: 'Watch Event',
    bg_image: 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1920&q=80&auto=format',
};

export default function HeroSection({ hero = {}, loading }) {
    const title    = hero?.title    || DEFAULTS.title;
    const subtitle = hero?.subtitle || DEFAULTS.subtitle;
    const ctaText  = hero?.cta_text || DEFAULTS.cta_text;
    const bgImage  = hero?.bg_image || DEFAULTS.bg_image;

    const heroStyle = {
        background: `linear-gradient(to bottom, rgba(15,18,33,0.65), rgba(15,18,33,0.45)), url('${bgImage}')`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
    };

    return (
        <section
            className="flex items-center justify-center text-center px-4 min-h-[700px] pb-32 pt-20"
            style={heroStyle}
        >
            <div className="max-w-3xl">
                <div
                    className="inline-flex items-center gap-2 px-4 py-2 rounded-full mb-6 text-sm font-semibold text-white/90 border border-white/20"
                    style={{ background: 'rgba(255,255,255,0.1)', backdropFilter: 'blur(12px)' }}
                >
                    <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Live Events This Weekend
                </div>

                {loading ? (
                    <div className="h-16 w-96 mx-auto bg-white/10 animate-pulse rounded-2xl mb-6" />
                ) : (
                    <h1 className="text-4xl md:text-6xl font-extrabold text-white mb-6 drop-shadow-md leading-tight">
                        {title}
                    </h1>
                )}

                {loading ? (
                    <div className="h-8 w-80 mx-auto bg-white/10 animate-pulse rounded-xl mb-10" />
                ) : (
                    <p className="text-lg md:text-xl text-white/90 mb-10 leading-relaxed drop-shadow-sm">
                        {subtitle}
                    </p>
                )}

                <div
                    className="glass-effect p-4 rounded-2xl inline-flex gap-4 flex-wrap justify-center"
                >
                    <button
                        className="bg-white font-bold px-10 py-4 rounded-xl hover:shadow-xl transition-all shadow-md"
                        style={{ color: '#1A237E' }}
                    >
                        {ctaText}
                    </button>
                    <button className="bg-white/10 text-white border border-white/20 backdrop-blur-md px-10 py-4 rounded-xl font-bold hover:bg-white/20 transition-all">
                        View All Events
                    </button>
                </div>
            </div>
        </section>
    );
}
