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
        $hero             = json_decode(Setting::get('homepage_hero',                '{}'), true) ?? [];
        $featuredEvent    = json_decode(Setting::get('homepage_featured_event',      '{}'), true) ?? [];
        $events           = json_decode(Setting::get('homepage_events',              '[]'), true) ?? [];
        $about            = json_decode(Setting::get('homepage_about',               '{}'), true) ?? [];
        $memorableMoments = json_decode(Setting::get('homepage_memorable_moments',   '{}'), true) ?? [];
        $footer           = json_decode(Setting::get('homepage_footer',              '{}'), true) ?? [];

        $today          = now()->toDateString();
        $upcomingFiltered = array_values(array_filter($events, fn($e) =>
            ($e['status'] ?? '') === 'ongoing' ||
            empty($e['date']) ||
            $e['date'] >= $today
        ));
        usort($upcomingFiltered, function ($a, $b) {
            $aDate = $a['date'] ?? '';
            $bDate = $b['date'] ?? '';
            $aEmpty = empty($aDate);
            $bEmpty = empty($bDate);
            if ($aEmpty || $bEmpty) {
                return $aEmpty <=> $bEmpty;
            }
            return strcmp($aDate, $bDate);
        });
        $upcomingEvents = array_slice($upcomingFiltered, 0, 3);
        $pastEvents     = array_slice(array_values(array_filter($events, fn($e) =>
            ($e['status'] ?? '') !== 'ongoing' &&
            !empty($e['date']) &&
            $e['date'] < $today
        )), 0, 4);

        return response()->json([
            'hero'              => $hero,
            'featured_event'    => $featuredEvent,
            'upcoming_events'   => $upcomingEvents,
            'past_events'       => $pastEvents,
            'memorable_moments' => $memorableMoments,
            'about'             => $about,
            'footer'            => $footer,
        ]);
    }

    /**
     * All events for the events listing page.
     *
     * GET /api/events
     */
    public function events(): JsonResponse
    {
        $events = json_decode(Setting::get('homepage_events', '[]'), true) ?? [];
        $footer = json_decode(Setting::get('homepage_footer',  '{}'), true) ?? [];

        usort($events, function ($a, $b) {
            $aDate  = $a['date'] ?? '';
            $bDate  = $b['date'] ?? '';
            $aEmpty = empty($aDate);
            $bEmpty = empty($bDate);

            if ($aEmpty || $bEmpty) {
                return $aEmpty <=> $bEmpty;
            }

            return strcmp($aDate, $bDate);
        });

        return response()->json([
            'events' => array_values($events),
            'footer' => $footer,
        ]);
    }
}
