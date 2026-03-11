import React from 'react';

const events = [
  {
    id: 1,
    category: 'Wellness',
    categoryColor: '#10b981',
    borderColor: '#10b981',
    title: 'Sunday Morning Yoga',
    date: 'September 3, 2025 • Central Park',
    image: 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=600&q=80&auto=format',
    action: 'Remind Me',
  },
  {
    id: 2,
    category: 'Meetings',
    categoryColor: '#4f46e5',
    borderColor: '#4f46e5',
    title: 'Q3 Town Hall Meeting',
    date: 'September 12, 2025 • Community Hall',
    image: 'https://images.unsplash.com/photo-1431540015161-0bf868a2d407?w=600&q=80&auto=format',
    action: 'RSVP Now',
  },
  {
    id: 3,
    category: 'Education',
    categoryColor: '#f97316',
    borderColor: '#f97316',
    title: 'Green Living Workshop',
    date: 'September 20, 2025 • Community Library',
    image: 'https://images.unsplash.com/photo-1416331108676-a22ccb276e35?w=600&q=80&auto=format',
    action: 'Join Waitlist',
  },
];

export default function UpcomingEvents() {
  return (
    <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 -mt-12">
      <div className="flex flex-col md:flex-row md:items-end justify-between mb-12">
        <div>
          <h2 className="text-3xl font-bold mb-2" style={{ color: '#1A237E' }}>Upcoming Events</h2>
          <p className="text-slate-500">Don't miss out on what's happening next.</p>
        </div>
        <a href="#" className="text-blue-600 font-semibold hover:underline mt-4 md:mt-0">View Calendar</a>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
        {events.map((event) => (
          <article
            key={event.id}
            className="bg-white rounded-2xl overflow-hidden shadow-sm lift-on-hover"
            style={{ borderTop: `4px solid ${event.borderColor}` }}
          >
            <img
              src={event.image}
              alt={event.title}
              className="w-full object-cover"
              style={{ aspectRatio: '4/3' }}
            />
            <div className="p-6">
              <span
                className="text-xs font-bold uppercase tracking-widest mb-2 block"
                style={{ color: event.categoryColor }}
              >
                {event.category}
              </span>
              <h3 className="text-xl font-bold mb-2" style={{ color: '#1A237E' }}>{event.title}</h3>
              <p className="text-slate-500 text-sm mb-6">{event.date}</p>
              <button className="w-full py-2.5 bg-slate-50 text-slate-600 rounded-lg font-semibold text-sm hover:bg-slate-100 transition-colors">
                {event.action}
              </button>
            </div>
          </article>
        ))}
      </div>
    </section>
  );
}
