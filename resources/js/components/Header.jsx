import React, { useState, useEffect } from 'react';

export default function Header() {
  const [scrolled, setScrolled] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 10);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <header
      className={`sticky top-0 z-50 transition-all duration-300 ${scrolled
          ? 'bg-white/95 backdrop-blur-lg shadow-md'
          : 'bg-white/90 backdrop-blur-md border-b border-gray-200'
        }`}
    >
      <nav className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        {/* Logo */}
        <div className="flex items-center">
          <span className="text-2xl font-bold tracking-tight" style={{ color: '#1A237E' }}>
            Dwipapuri
          </span>
        </div>

        {/* Desktop Nav */}
        <div className="hidden md:flex space-x-8 items-center text-sm font-medium text-slate-600">
          <a href="#events" className="hover:text-indigo-900 transition-colors duration-200">Events</a>
          <a href="#about" className="hover:text-indigo-900 transition-colors duration-200">About</a>
          <a href="#contact" className="hover:text-indigo-900 transition-colors duration-200">Contact</a>
          <a
            href="/login"
            className="ml-4 px-5 py-2.5 rounded-xl text-white font-semibold text-sm transition-all duration-200 hover:opacity-90 hover:shadow-lg"
            style={{ background: 'linear-gradient(135deg, #3b5bdb, #1A237E)' }}
          >
            Admin Login
          </a>
        </div>

        {/* Mobile menu button */}
        <button
          className="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors"
          onClick={() => setMenuOpen(!menuOpen)}
          aria-label="Toggle menu"
        >
          {menuOpen ? (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          ) : (
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          )}
        </button>
      </nav>

      {/* Mobile menu */}
      {menuOpen && (
        <div className="md:hidden bg-white border-t border-gray-100 px-4 py-4 space-y-3">
          <a href="#events" className="block text-slate-600 font-medium py-2 hover:text-indigo-900 transition-colors">Events</a>
          <a href="#about" className="block text-slate-600 font-medium py-2 hover:text-indigo-900 transition-colors">About</a>
          <a href="#contact" className="block text-slate-600 font-medium py-2 hover:text-indigo-900 transition-colors">Contact</a>
          <a
            href="/login"
            className="block w-full text-center px-5 py-2.5 rounded-xl text-white font-semibold text-sm mt-2"
            style={{ background: 'linear-gradient(135deg, #3b5bdb, #1A237E)' }}
          >
            Admin Login
          </a>
        </div>
      )}
    </header>
  );
}
