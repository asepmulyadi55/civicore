import React, { useState, useEffect } from 'react';

export default function Header({ isDark = false, toggleDark }) {
  const C = isDark ? {
    primary: '#D4AF37',
    secondary: '#D4AF37',
    surface: '#0D1A17',
    surfaceVar: '#1C2D27',
    onSurface: '#F0EDE8',
    onSurfaceVar: '#9E9C97',
  } : {
    primary: '#1C2D27',
    secondary: '#D4AF37',
    surface: '#FAF9F6',
    surfaceVar: '#E8E6E1',
    onSurface: '#2C2C2C',
    onSurfaceVar: '#595959',
  };

  const [scrolled, setScrolled] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 10);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const navLinks = [
    { label: 'Events', href: '#events' },
    { label: 'Gallery', href: '#gallery' },
    { label: 'Bulletins', href: '#bulletins' },
    { label: 'About', href: '#about' },
  ];

  return (
    <nav
      className={`fixed top-0 w-full z-50 transition-all`}
      style={{
        background: `${C.surface}E6`,
        backdropFilter: 'blur(16px)',
        WebkitBackdropFilter: 'blur(16px)',
        borderBottom: `1px solid ${C.surfaceVar}80`,
      }}
    >
      <div className="flex justify-between items-center max-w-7xl mx-auto px-6 md:px-8 py-4 md:py-5">

        {/* Logo */}
        <div
          className="text-xl md:text-2xl font-semibold tracking-tight"
          style={{ color: C.primary, fontFamily: "'Plus Jakarta Sans', sans-serif" }}
        >
          Dwipapuri.
        </div>

        {/* Mobile: dark toggle + hamburger */}
        <div className="md:hidden flex items-center gap-2">
          {toggleDark && (
            <button
              onClick={toggleDark}
              className="p-2 rounded-lg transition-colors"
              style={{ color: C.onSurfaceVar }}
              aria-label="Toggle dark mode"
            >
              <span className="material-symbols-outlined text-[20px]">
                {isDark ? 'light_mode' : 'dark_mode'}
              </span>
            </button>
          )}
          <button
            className="p-2"
            style={{ color: C.primary }}
            onClick={() => setMenuOpen(!menuOpen)}
            aria-label="Toggle menu"
          >
            <span className="material-symbols-outlined">
              {menuOpen ? 'close' : 'menu'}
            </span>
          </button>
        </div>

        {/* Desktop nav links + dark toggle */}
        <div className="hidden md:flex items-center gap-10">
          {navLinks.map((link) => (
            <a
              key={link.label}
              href={link.href}
              className="font-medium tracking-wide text-sm transition-colors"
              style={{
                fontFamily: "'Plus Jakarta Sans', sans-serif",
                color: C.onSurfaceVar,
                borderBottom: '1px solid transparent',
                paddingBottom: '4px',
              }}
              onClick={(e) => {
                if (link.href.startsWith('#')) {
                  e.preventDefault();
                  const id = link.href.slice(1);
                  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
              }}
              onMouseEnter={e => { e.currentTarget.style.color = C.primary; e.currentTarget.style.borderBottomColor = C.primary; }}
              onMouseLeave={e => { e.currentTarget.style.color = C.onSurfaceVar; e.currentTarget.style.borderBottomColor = 'transparent'; }}
            >
              {link.label}
            </a>
          ))}
          {toggleDark && (
            <button
              onClick={toggleDark}
              className="p-2 rounded-lg transition-colors"
              style={{ color: C.onSurfaceVar }}
              aria-label="Toggle dark mode"
              onMouseEnter={e => e.currentTarget.style.color = C.primary}
              onMouseLeave={e => e.currentTarget.style.color = C.onSurfaceVar}
            >
              <span className="material-symbols-outlined text-[20px]">
                {isDark ? 'light_mode' : 'dark_mode'}
              </span>
            </button>
          )}
        </div>

      </div>

      {/* Mobile menu panel */}
      {menuOpen && (
        <div
          className="md:hidden border-t px-6 py-5 space-y-4"
          style={{ borderColor: `${C.surfaceVar}80`, background: `${C.surface}F5` }}
        >
          {navLinks.map(link => (
            <a
              key={link.label}
              href={link.href}
              className="block font-medium text-sm transition-colors"
              style={{ color: C.onSurfaceVar, fontFamily: "'Plus Jakarta Sans', sans-serif" }}
              onClick={(e) => {
                setMenuOpen(false);
                if (link.href.startsWith('#')) {
                  e.preventDefault();
                  const id = link.href.slice(1);
                  setTimeout(() => {
                    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                  }, 50);
                }
              }}
            >
              {link.label}
            </a>
          ))}
        </div>
      )}
    </nav>
  );
}
