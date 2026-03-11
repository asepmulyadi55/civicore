import React from 'react';
import Header from '../components/Header';
import HeroSection from '../components/HeroSection';
import FeaturedEvent from '../components/FeaturedEvent';
import UpcomingEvents from '../components/UpcomingEvents';
import PastHighlights from '../components/PastHighlights';
import AboutSection from '../components/AboutSection';
import Footer from '../components/Footer';

export default function HomePage() {
  return (
    <div className="font-sans text-slate-800" style={{ backgroundColor: '#fdfcfb' }}>
      <Header />
      <main>
        <HeroSection />
        <FeaturedEvent />
        <UpcomingEvents />
        <PastHighlights />
        <AboutSection />
      </main>
      <Footer />
    </div>
  );
}
