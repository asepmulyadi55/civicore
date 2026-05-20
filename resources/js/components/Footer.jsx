import React from 'react';

const C = {
  primary:  '#1C2D27',
  secondary: '#D4AF37',
  surface:  '#FAF9F6',
};

const QUICK_LINKS = [
  { label: 'Resident Portal',    href: '#' },
  { label: 'Amenities Map',      href: '#' },
  { label: 'Community Rules',    href: '#' },
  { label: 'Maintenance Request',href: '#' },
];

export default function Footer() {
  return (
    <footer id="contact" style={{ backgroundColor: C.primary, color: C.surface }}>
      <div className="grid grid-cols-1 md:grid-cols-4 gap-10 md:gap-12 max-w-7xl mx-auto px-6 md:px-8 py-12 md:py-20">

        {/* Brand */}
        <div className="space-y-4 md:space-y-6 md:pr-8">
          <div
            className="text-xl md:text-2xl font-semibold tracking-tight"
            style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}
          >
            Dwipapuri.
          </div>
          <p className="text-sm leading-relaxed font-light" style={{ color: `${C.surface}99` }}>
            Defining the gold standard of modern residential living through curation, privacy, and community.
          </p>
        </div>

        {/* Quick Links */}
        <div>
          <h4
            className="font-medium mb-4 md:mb-6 tracking-wide text-sm md:text-base"
            style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}
          >
            Quick Links
          </h4>
          <ul className="space-y-3 md:space-y-4 text-xs md:text-sm font-light" style={{ color: `${C.surface}99` }}>
            {QUICK_LINKS.map(link => (
              <li key={link.label}>
                <a
                  href={link.href}
                  className="transition-colors"
                  onMouseEnter={e => e.currentTarget.style.color = C.secondary}
                  onMouseLeave={e => e.currentTarget.style.color = `${C.surface}99`}
                >
                  {link.label}
                </a>
              </li>
            ))}
          </ul>
        </div>

        {/* Contact */}
        <div>
          <h4
            className="font-medium mb-4 md:mb-6 tracking-wide text-sm md:text-base"
            style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}
          >
            Contact
          </h4>
          <ul className="space-y-3 md:space-y-4 text-xs md:text-sm font-light" style={{ color: `${C.surface}99` }}>
            <li className="flex items-start gap-3">
              <span className="material-symbols-outlined text-base opacity-70 mt-0.5">location_on</span>
              101 Dwipapuri Blvd, Serene Valley
            </li>
            <li className="flex items-center gap-3">
              <span className="material-symbols-outlined text-base opacity-70">call</span>
              +62 123 4567 890
            </li>
            <li className="flex items-center gap-3">
              <span className="material-symbols-outlined text-base opacity-70">mail</span>
              concierge@dwipapuri.res
            </li>
          </ul>
        </div>

        {/* Newsletter */}
        <div>
          <h4
            className="font-medium mb-4 md:mb-6 tracking-wide text-sm md:text-base"
            style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}
          >
            Newsletter
          </h4>
          <div className="flex gap-2 border-b pb-2" style={{ borderColor: `${C.surface}33` }}>
            <input
              type="email"
              placeholder="Email address"
              className="bg-transparent border-none px-0 py-1 md:py-2 text-xs md:text-sm w-full focus:ring-0 font-light placeholder:opacity-40"
              style={{ color: C.surface, fontFamily: "'Inter', sans-serif" }}
            />
            <button
              className="transition-colors p-1 md:p-2"
              style={{ color: C.secondary }}
              onMouseEnter={e => e.currentTarget.style.color = C.surface}
              onMouseLeave={e => e.currentTarget.style.color = C.secondary}
            >
              <span className="material-symbols-outlined text-lg">arrow_forward</span>
            </button>
          </div>
          <div className="mt-6 md:mt-8 flex gap-4 md:gap-5" style={{ color: `${C.surface}66` }}>
            <span
              className="material-symbols-outlined cursor-pointer transition-colors"
              onMouseEnter={e => e.currentTarget.style.color = C.surface}
              onMouseLeave={e => e.currentTarget.style.color = `${C.surface}66`}
            >
              brand_awareness
            </span>
            <span
              className="material-symbols-outlined cursor-pointer transition-colors"
              onMouseEnter={e => e.currentTarget.style.color = C.surface}
              onMouseLeave={e => e.currentTarget.style.color = `${C.surface}66`}
            >
              groups
            </span>
            <span
              className="material-symbols-outlined cursor-pointer transition-colors"
              onMouseEnter={e => e.currentTarget.style.color = C.surface}
              onMouseLeave={e => e.currentTarget.style.color = `${C.surface}66`}
            >
              public
            </span>
          </div>
        </div>
      </div>

      {/* Bottom bar */}
      <div
        className="border-t py-6 md:py-8 text-center text-[10px] md:text-xs font-light flex flex-col sm:flex-row justify-center items-center gap-2 sm:gap-0"
        style={{ borderColor: `${C.surface}1A`, color: `${C.surface}66` }}
      >
        <span>© 2026 Dwipapuri Residential. All rights reserved.</span>
        <div className="flex items-center mt-2 sm:mt-0">
          <span className="mx-3 md:mx-4 opacity-30 hidden sm:inline">|</span>
          <a href="#" className="hover:opacity-80 transition-opacity">Privacy Policy</a>
          <span className="mx-3 md:mx-4 opacity-30">|</span>
          <a href="#" className="hover:opacity-80 transition-opacity">Terms of Service</a>
        </div>
      </div>
    </footer>
  );
}
