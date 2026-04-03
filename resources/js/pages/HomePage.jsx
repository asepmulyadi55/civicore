import React, { useState, useEffect } from 'react';
import Header from '../components/Header';
import HeroSection from '../components/HeroSection';
import FeaturedEvent from '../components/FeaturedEvent';
import UpcomingEvents from '../components/UpcomingEvents';
import PastHighlights from '../components/PastHighlights';
import AboutSection from '../components/AboutSection';
import Footer from '../components/Footer';

export default function HomePage() {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch('/api/homepage')
            .then(res => res.json())
            .then(json => {
                setData(json);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, []);

    return (
        <div className="font-sans text-slate-800" style={{ backgroundColor: '#fdfcfb' }}>
            <Header />
            <main>
                <HeroSection hero={data?.hero} loading={loading} />
                <FeaturedEvent featuredEvent={data?.featured_event} loading={loading} />
                <UpcomingEvents events={data?.upcoming_events ?? []} loading={loading} />
                <PastHighlights pastEvents={data?.past_events ?? []} loading={loading} />
                <AboutSection about={data?.about} loading={loading} />
            </main>
            <Footer />
        </div>
    );
}
