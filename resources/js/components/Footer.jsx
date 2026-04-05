import React from 'react';

export default function Footer() {
  return (
    <footer id="contact" style={{ backgroundColor: '#0F1221', color: 'white' }} className="pt-24 pb-12">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
          {/* Brand */}
          <div className="col-span-1 md:col-span-1">
            <h3 className="text-2xl font-bold mb-6">Dwipapuri</h3>
            <p className="text-white/60 leading-relaxed text-sm">
              Cultivating a better lifestyle through community, nature, and innovation.
            </p>
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="font-bold mb-6 text-sm uppercase tracking-widest">Quick Links</h4>
            <ul className="space-y-4 text-sm text-white/60">
              {['Resident Portal', 'Event Calendar', 'Amenities', 'Privacy Policy'].map((link) => (
                <li key={link}>
                  <a href="#" className="hover:text-white transition-colors duration-200">{link}</a>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h4 className="font-bold mb-6 text-sm uppercase tracking-widest">Contact Us</h4>
            <ul className="space-y-4 text-sm text-white/60">
              <li className="flex items-center">
                <svg className="w-4 h-4 mr-3" style={{ color: '#FF7043' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                    strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} />
                </svg>
                hello@dwipapuri.com
              </li>
              <li className="flex items-center">
                <svg className="w-4 h-4 mr-3" style={{ color: '#FF7043' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                    strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} />
                </svg>
                +62 123 4567 890
              </li>
            </ul>
          </div>

          {/* Social */}
          <div>
            <h4 className="font-bold mb-6 text-sm uppercase tracking-widest">Follow Us</h4>
            <div className="flex space-x-4">
              {/* Facebook */}
              <a href="#"
                className="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-200"
                style={{ backgroundColor: 'rgba(255,255,255,0.1)' }}
                onMouseEnter={e => e.currentTarget.style.backgroundColor = '#FF7043'}
                onMouseLeave={e => e.currentTarget.style.backgroundColor = 'rgba(255,255,255,0.1)'}
              >
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
                </svg>
              </a>
              {/* Instagram */}
              <a href="#"
                className="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-200"
                style={{ backgroundColor: 'rgba(255,255,255,0.1)' }}
                onMouseEnter={e => e.currentTarget.style.backgroundColor = '#FF7043'}
                onMouseLeave={e => e.currentTarget.style.backgroundColor = 'rgba(255,255,255,0.1)'}
              >
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12.315 2c2.43 0 2.784.01 3.71.054 1.14.051 1.918.27 2.594.533a4.885 4.885 0 011.757 1.143 4.885 4.885 0 011.141 1.757c.264.676.48 1.454.533 2.594.045.927.054 1.28.054 3.71s-.01 2.784-.054 3.71c-.053 1.14-.27 1.918-.533 2.594a4.885 4.885 0 01-1.141 1.757 4.885 4.885 0 01-1.757 1.143c-.676.264-1.454.48-2.594.533-.927.045-1.28.054-3.71.054s-2.784-.01-3.71-.054c-1.14-.051-1.918-.27-2.594-.533a4.885 4.885 0 01-1.757-1.143 4.885 4.885 0 01-1.141-1.757c-.264-.676-.48-1.454-.533-2.594-.045-.927-.054-1.28-.054-3.71s.01-2.784.054-3.71c.053-1.14.27-1.918.533-2.594a4.885 4.885 0 011.141-1.757 4.885 4.885 0 011.757-1.143c.676-.264 1.454-.48 2.594-.533.927-.045 1.28-.054 3.71-.054zM12 6.865a5.135 5.135 0 100 10.27 5.135 5.135 0 000-10.27zm0 1.802a3.333 3.333 0 110 6.666 3.333 3.333 0 010-6.666zm5.338-3.205a1.2 1.2 0 100 2.4 1.2 1.2 0 000-2.4z" />
                </svg>
              </a>
            </div>
          </div>
        </div>

        {/* Bottom bar */}
        <div className="border-t pt-10 flex flex-col md:flex-row justify-between items-center text-xs"
          style={{ borderColor: 'rgba(255,255,255,0.1)', color: 'rgba(255,255,255,0.4)' }}>
          <p>© 2025 Dwipapuri Residential Community. All rights reserved.</p>
          <p className="mt-4 md:mt-0">Built for a better community experience.</p>
        </div>
      </div>
    </footer>
  );
}
