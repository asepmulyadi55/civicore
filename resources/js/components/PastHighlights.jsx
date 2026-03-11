import React from 'react';

export default function PastHighlights() {
  return (
    <section className="py-32" style={{ background: 'linear-gradient(to bottom, #ffffff, #f5f5f5)' }}>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 className="text-3xl font-bold mb-4" style={{ color: '#1A237E' }}>Past Highlights</h2>
        <p className="text-slate-500 max-w-2xl mx-auto mb-16">
          Relive the moments that make our community special. Browse our gallery and video archives.
        </p>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
          {/* Card 1 */}
          <div className="aspect-square bg-white rounded-2xl overflow-hidden shadow-sm flex items-center justify-center">
            <img
              src="https://images.unsplash.com/photo-1528605105345-5344ea20e269?w=400&q=80&auto=format"
              alt="Community gathering"
              className="w-full h-full object-cover"
            />
          </div>

          {/* Card 2 — dark text card */}
          <div className="aspect-square rounded-2xl overflow-hidden shadow-sm flex items-center justify-center p-8 text-white text-xs leading-tight uppercase font-medium"
            style={{ backgroundColor: '#2d5a4c' }}>
            <div className="border border-white/30 p-4 text-center">
              <p className="mb-2">Nov 2024</p>
              <p className="text-lg font-bold">Past Event</p>
              <p className="mt-2 text-white/60">Community Day</p>
            </div>
          </div>

          {/* Card 3 */}
          <div className="aspect-square bg-white rounded-2xl overflow-hidden shadow-sm flex items-center justify-center">
            <img
              src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=400&q=80&auto=format"
              alt="Festival highlights"
              className="w-full h-full object-cover"
            />
          </div>

          {/* Card 4 */}
          <div className="aspect-square bg-white rounded-2xl overflow-hidden shadow-sm flex items-center justify-center">
            <img
              src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=400&q=80&auto=format"
              alt="Neighborhood event"
              className="w-full h-full object-cover"
            />
          </div>
        </div>

        <button className="px-8 py-3 rounded-full font-bold hover:text-white transition-all duration-300"
          style={{
            border: '2px solid #2563eb',
            color: '#2563eb',
          }}
          onMouseEnter={e => { e.currentTarget.style.background = '#2563eb'; e.currentTarget.style.color = '#fff'; }}
          onMouseLeave={e => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.color = '#2563eb'; }}
        >
          View Full Archive
        </button>
      </div>
    </section>
  );
}
