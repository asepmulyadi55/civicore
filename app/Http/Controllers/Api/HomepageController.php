<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class HomepageController extends Controller
{
    /**
     * Public JSON endpoint — homepage content for the React SPA.
     *
     * GET /api/homepage
     */
    public function index(): JsonResponse
    {
        $hero          = json_decode(Setting::get('homepage_hero',           '{}'), true) ?? [];
        $featuredEvent = json_decode(Setting::get('homepage_featured_event', '{}'), true) ?? [];
        $events        = json_decode(Setting::get('homepage_events',         '[]'), true) ?? [];
        $about         = json_decode(Setting::get('homepage_about',          '{}'), true) ?? [];

        $today          = now()->toDateString();
        $upcomingEvents = array_slice(array_values(array_filter($events, fn($e) =>
            ($e['status'] ?? '') === 'ongoing' ||
            empty($e['date']) ||
            $e['date'] >= $today
        )), 0, 3);
        $pastEvents     = array_slice(array_values(array_filter($events, fn($e) =>
            ($e['status'] ?? '') !== 'ongoing' &&
            !empty($e['date']) &&
            $e['date'] < $today
        )), 0, 4);

        return response()->json([
            'hero'            => $hero,
            'featured_event'  => $featuredEvent,
            'upcoming_events' => $upcomingEvents,
            'past_events'     => $pastEvents,
            'about'           => $about,
        ]);
    }
}
