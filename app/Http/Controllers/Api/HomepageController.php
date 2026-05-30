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
        $buletin          = json_decode(Setting::get('homepage_buletin',             '[]'), true) ?? [];
        $about            = json_decode(Setting::get('homepage_about',               '{}'), true) ?? [];
        $memorableMoments = json_decode(Setting::get('homepage_memorable_moments',   '{}'), true) ?? [];
        $footer           = json_decode(Setting::get('homepage_footer',              '{}'), true) ?? [];
        $sectionLabels    = array_merge(
            ['featured_eyebrow' => 'Featured Event', 'events_eyebrow' => 'Discover More', 'events_heading' => 'Upcoming Community Events', 'buletin_eyebrow' => 'Informasi', 'buletin_heading' => 'Buletin'],
            json_decode(Setting::get('homepage_section_labels', '{}'), true) ?? []
        );

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

        // Sort buletin ascending by date (same as events)
        usort($buletin, fn($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));
        $latestBuletin = array_slice(array_values($buletin), 0, 3);

        return response()->json([
            'hero'              => $hero,
            'featured_event'    => $featuredEvent,
            'upcoming_events'   => $upcomingEvents,
            'past_events'       => $pastEvents,
            'buletin'           => $latestBuletin,
            'memorable_moments' => $memorableMoments,
            'about'             => $about,
            'footer'            => $footer,
            'section_labels'    => $sectionLabels,
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

        $today = now()->toDateString();
        usort($events, function ($a, $b) use ($today) {
            $aDate    = $a['date'] ?? '';
            $bDate    = $b['date'] ?? '';
            $aIsPast  = !empty($aDate) && $aDate < $today;
            $bIsPast  = !empty($bDate) && $bDate < $today;

            // Different buckets: upcoming (incl. no-date) before past
            if ($aIsPast !== $bIsPast) {
                return $aIsPast <=> $bIsPast;
            }

            // Both upcoming: no-date goes last within the group; otherwise ascending
            if (!$aIsPast) {
                $aEmpty = empty($aDate);
                $bEmpty = empty($bDate);
                if ($aEmpty !== $bEmpty) return $aEmpty <=> $bEmpty;
            }

            return strcmp($aDate, $bDate);
        });

        return response()->json([
            'events' => array_values($events),
            'footer' => $footer,
        ]);
    }

    /**
     * All buletin for the buletin listing page.
     *
     * GET /api/buletin
     */
    public function buletin(): JsonResponse
    {
        $buletin = json_decode(Setting::get('homepage_buletin', '[]'), true) ?? [];
        $footer  = json_decode(Setting::get('homepage_footer',  '{}'), true) ?? [];

        $today = now()->toDateString();
        usort($buletin, function ($a, $b) use ($today) {
            $aDate   = $a['date'] ?? '';
            $bDate   = $b['date'] ?? '';
            $aIsPast = !empty($aDate) && $aDate < $today;
            $bIsPast = !empty($bDate) && $bDate < $today;

            // Different buckets: upcoming (incl. no-date) before past
            if ($aIsPast !== $bIsPast) {
                return $aIsPast <=> $bIsPast;
            }

            // Both upcoming: no-date goes last within the group; otherwise ascending
            if (!$aIsPast) {
                $aEmpty = empty($aDate);
                $bEmpty = empty($bDate);
                if ($aEmpty !== $bEmpty) return $aEmpty <=> $bEmpty;
            }

            return strcmp($aDate, $bDate);
        });

        return response()->json([
            'buletin' => array_values($buletin),
            'footer'  => $footer,
        ]);
    }
}
