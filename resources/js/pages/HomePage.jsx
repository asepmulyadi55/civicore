import React, { useState, useEffect } from 'react';
import Header from '../components/Header';
import FeaturedEvent from '../components/FeaturedEvent';
import UpcomingEvents from '../components/UpcomingEvents';
import MemorableMoments from '../components/MemorableMoments';
import AboutSection from '../components/AboutSection';
import Footer from '../components/Footer';

export default function HomePage() {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const basePath = import.meta.env.VITE_APP_BASE ?? '';
        const apiKey = document.querySelector('meta[name="api-key"]')?.content ?? '';
        fetch(`${basePath}/api/homepage`, {
            headers: { 'X-Api-Key': apiKey },
        })
            .then(res => res.json())
            .then(json => {
                setData(json);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, []);

    return (
        <div className="font-sans text-slate-800" style={{ backgroundColor: '#f8f9fa' }}>
            <Header />
            <main className="pt-20">
                <FeaturedEvent featuredEvent={data?.featured_event} loading={loading} />
                <UpcomingEvents events={data?.upcoming_events ?? []} loading={loading} />
                <MemorableMoments moments={data?.memorable_moments} pastEvents={data?.past_events ?? []} loading={loading} />
                <AboutSection about={data?.about} loading={loading} />
            </main>
            <Footer footer={data?.footer} />
        </div>
    );
}
