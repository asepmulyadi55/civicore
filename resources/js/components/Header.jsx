import React, { useState, useEffect } from 'react';

export default function Header() {
  const [scrolled, setScrolled] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 10);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const navLinks = [
    { label: 'Featured Event', href: '#featured' },
    { label: 'Events',         href: '#events'   },
    { label: 'Gallery',        href: '#gallery'  },
    { label: 'About',          href: '#about'    },
  ];

  return (
    <header
      className={`fixed top-0 w-full z-50 transition-all duration-300 ${
        scrolled ? 'shadow-md' : ''
      }`}
      style={{
        background: scrolled ? 'rgba(248,249,250,0.88)' : 'rgba(248,249,250,0.80)',
        backdropFilter: 'blur(20px)',
        WebkitBackdropFilter: 'blur(20px)',
      }}
    >
      <nav className="max-w-7xl mx-auto px-8 h-20 flex items-center justify-between">
        {/* Logo */}
        <div className="text-2xl font-bold tracking-tighter"
          style={{ color: '#1a237e', fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
          Dwipapuri
        </div>

        {/* Desktop Nav */}
        <div className="hidden md:flex items-center gap-8">
          {navLinks.map(link => (
            <a key={link.label} href={link.href}
              className="transition-colors font-medium tracking-tight"
              style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: '#454652', fontSize: '0.9375rem' }}
              onMouseEnter={e => e.currentTarget.style.color = '#1a237e'}
              onMouseLeave={e => e.currentTarget.style.color = '#454652'}
            >
              {link.label}
            </a>
          ))}
          <a href="/login"
            className="ml-2 px-6 py-2.5 rounded-lg text-white font-semibold text-sm transition-all hover:opacity-90 active:scale-95"
            style={{ background: '#000666', fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
            Login
          </a>
        </div>

        {/* Mobile menu button */}
        <button
          className="md:hidden p-2 rounded-lg transition-colors"
          style={{ color: '#454652' }}
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
        <div className="md:hidden border-t px-4 py-4 space-y-3"
          style={{ borderColor: 'rgba(198,197,212,0.4)', background: 'rgba(248,249,250,0.97)' }}>
          {navLinks.map(link => (
            <a key={link.label} href={link.href}
              className="block py-2 font-medium transition-colors"
              style={{ color: '#454652', fontFamily: "'Plus Jakarta Sans', sans-serif" }}
              onClick={() => setMenuOpen(false)}>
              {link.label}
            </a>
          ))}
          <a href="/login"
            className="block w-full text-center px-5 py-2.5 rounded-lg text-white font-semibold text-sm mt-2"
            style={{ background: '#000666', fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
            Login
          </a>
        </div>
      )}
    </header>
  );
}
