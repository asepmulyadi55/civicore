import React from 'react';

const C = {
  primary:  '#1C2D27',
  secondary: '#D4AF37',
  surface:  '#FAF9F6',
};

export default function Footer({ footer = {} }) {
  const brandName   = footer.brand_name    || 'Dwipapuri.';
  const tagline     = footer.tagline       || 'Defining the gold standard of modern residential living through curation, privacy, and community.';
  const quickLinks  = (footer.links?.length ? footer.links : [
    { label: 'Resident Portal',     url: '#' },
    { label: 'Amenities Map',       url: '#' },
    { label: 'Community Rules',     url: '#' },
    { label: 'Maintenance Request', url: '#' },
  ]);
  const contactEmail   = footer.contact_email  || 'concierge@dwipapuri.res';
  const contactPhone   = footer.contact_phone  || '+62 123 4567 890';
  const facebookUrl    = footer.facebook_url   || null;
  const instagramUrl   = footer.instagram_url  || null;
  const copyright      = footer.copyright      || '© 2026 Dwipapuri Residential. All rights reserved.';
  const bottomNote     = footer.bottom_note    || null;
  return (
    <footer id="contact" style={{ backgroundColor: C.primary, color: C.surface }}>
      <div className="grid grid-cols-1 md:grid-cols-4 gap-10 md:gap-12 max-w-7xl mx-auto px-6 md:px-8 py-12 md:py-20">

        {/* Brand */}
        <div className="space-y-4 md:space-y-6 md:pr-8">
          <div
            className="text-xl md:text-2xl font-semibold tracking-tight"
            style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}
          >
            {brandName}
          </div>
          <p className="text-sm leading-relaxed font-light" style={{ color: `${C.surface}99` }}>
            {tagline}
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
            {quickLinks.map((link, i) => (
              <li key={i}>
                <a
                  href={link.url || '#'}
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
              {contactPhone}
            </li>
            <li className="flex items-center gap-3">
              <span className="material-symbols-outlined text-base opacity-70">mail</span>
              {contactEmail}
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
            {facebookUrl ? (
              <a
                href={facebookUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="material-symbols-outlined cursor-pointer transition-colors"
                style={{ color: `${C.surface}66` }}
                onMouseEnter={e => e.currentTarget.style.color = C.surface}
                onMouseLeave={e => e.currentTarget.style.color = `${C.surface}66`}
              >brand_awareness</a>
            ) : (
              <span
                className="material-symbols-outlined cursor-pointer transition-colors"
                onMouseEnter={e => e.currentTarget.style.color = C.surface}
                onMouseLeave={e => e.currentTarget.style.color = `${C.surface}66`}
              >brand_awareness</span>
            )}
            {instagramUrl ? (
              <a
                href={instagramUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="material-symbols-outlined cursor-pointer transition-colors"
                style={{ color: `${C.surface}66` }}
                onMouseEnter={e => e.currentTarget.style.color = C.surface}
                onMouseLeave={e => e.currentTarget.style.color = `${C.surface}66`}
              >groups</a>
            ) : (
              <span
                className="material-symbols-outlined cursor-pointer transition-colors"
                onMouseEnter={e => e.currentTarget.style.color = C.surface}
                onMouseLeave={e => e.currentTarget.style.color = `${C.surface}66`}
              >groups</span>
            )}
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
        className="border-t py-6 md:py-8 text-[10px] md:text-xs font-light flex flex-col sm:flex-row justify-between items-center px-6 md:px-8 max-w-7xl mx-auto gap-2"
        style={{ borderColor: `${C.surface}1A`, color: `${C.surface}66` }}
      >
        <span>{copyright}</span>
        {bottomNote && <span>{bottomNote}</span>}
      </div>
    </footer>
  );
}
