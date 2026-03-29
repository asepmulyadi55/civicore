<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HomepageController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $hero          = json_decode(Setting::get('homepage_hero',          '{}'), true) ?? [];
        $featuredEvent = json_decode(Setting::get('homepage_featured_event','{}'), true) ?? [];
        $events        = json_decode(Setting::get('homepage_events',        '[]'), true) ?? [];
        $about         = json_decode(Setting::get('homepage_about',         '{}'), true) ?? [];

        // Split events into upcoming / past by status field
        $upcomingEvents = array_values(array_filter($events, fn($e) => ($e['status'] ?? '') === 'upcoming'));
        $pastEvents     = array_values(array_filter($events, fn($e) => ($e['status'] ?? '') === 'past'));

        return view('homepage', compact('hero', 'featuredEvent', 'upcomingEvents', 'pastEvents', 'about'));
    }

    // ── Hero Section ──────────────────────────────────────────────────────────

    public function updateHero(Request $request)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'cta_text' => 'nullable|string|max:100',
            'cta_url'  => 'nullable|string|max:300',
            'bg_image' => 'nullable|string|max:500',
        ]);

        $this->saveSetting('homepage_hero', json_encode($data), 'Hero Section');

        return redirect()->route('homepage.index')->with('success', 'Hero section saved.');
    }

    // ── Featured Event ────────────────────────────────────────────────────────

    public function updateFeaturedEvent(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'youtube_id'  => 'nullable|string|max:20',
            'date'        => 'nullable|date',
            'status'      => 'nullable|string|in:upcoming,past,ongoing',
        ]);

        $this->saveSetting('homepage_featured_event', json_encode($data), 'Featured Event');

        return redirect()->route('homepage.index')->with('success', 'Featured event saved.');
    }

    // ── Events List ───────────────────────────────────────────────────────────

    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'date'        => 'nullable|date',
            'status'      => 'required|string|in:upcoming,past',
        ]);

        $events = json_decode(Setting::get('homepage_events', '[]'), true) ?? [];

        $events[] = array_merge($validated, ['id' => (string) Str::uuid()]);

        $this->saveSetting('homepage_events', json_encode(array_values($events)), 'Events List');

        return redirect()->route('homepage.index')->with('success', 'Event added.');
    }

    public function destroyEvent(string $id)
    {
        $events = json_decode(Setting::get('homepage_events', '[]'), true) ?? [];
        $events = array_values(array_filter($events, fn($e) => ($e['id'] ?? '') !== $id));

        $this->saveSetting('homepage_events', json_encode($events), 'Events List');

        return redirect()->route('homepage.index')->with('success', 'Event removed.');
    }

    // ── About Section ─────────────────────────────────────────────────────────

    public function updateAbout(Request $request)
    {
        $data = $request->validate([
            'content' => 'required|string|max:3000',
        ]);

        $this->saveSetting('homepage_about', json_encode($data), 'About Section');

        return redirect()->route('homepage.index')->with('success', 'About section saved.');
    }

    // ── Private helper ────────────────────────────────────────────────────────

    /**
     * Upsert a homepage CMS setting, supplying the required `label` and `group`
     * columns that the settings table enforces (label is NOT NULL).
     */
    private function saveSetting(string $key, string $value, string $label): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'label' => $label, 'group' => 'homepage'],
        );
        Cache::forget("setting:{$key}");
    }
}
