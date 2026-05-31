<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyListing;
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

        // Sort buletin ascending by date (same as events), then filter to upcoming only
        usort($buletin, fn($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));
        $upcomingBuletin = array_values(array_filter($buletin, fn($item) =>
            empty($item['date']) || $item['date'] >= $today
        ));
        $latestBuletin = array_slice($upcomingBuletin, 0, 3);

        $propertyListings = PropertyListing::with(['block'])
            ->where('is_active', true)
            ->where('status', 'available')
            ->latest()
            ->take(6)
            ->get()
            ->map(fn($l) => [
                'id'              => $l->id,
                'title'           => $l->title,
                'type'            => $l->type,
                'type_label'      => $l->typeLabel(),
                'status'          => $l->status,
                'status_label'    => $l->statusLabel(),
                'price'           => $l->price,
                'formatted_price' => $l->formattedPrice(),
                'location_label'  => $l->location_label,
                'bedrooms'        => $l->bedrooms,
                'bathrooms'       => $l->bathrooms,
                'land_area'       => $l->land_area,
                'building_area'   => $l->building_area,
                'contact_name'    => $l->contact_name,
                'contact_phone'   => $l->contact_phone,
                'images'          => $l->imageUrls(),
            ]);

        return response()->json([
            'hero'               => $hero,
            'featured_event'     => $featuredEvent,
            'upcoming_events'    => $upcomingEvents,
            'past_events'        => $pastEvents,
            'buletin'            => $latestBuletin,
            'memorable_moments'  => $memorableMoments,
            'about'              => $about,
            'footer'             => $footer,
            'section_labels'     => $sectionLabels,
            'property_listings'  => $propertyListings,
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

    /**
     * Active property listings for the public property page.
     *
     * GET /api/property
     */
    public function property(): JsonResponse
    {
        $footer = json_decode(Setting::get('homepage_footer', '{}'), true) ?? [];

        $listings = PropertyListing::with(['block'])
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(fn($l) => [
                'id'              => $l->id,
                'title'           => $l->title,
                'type'            => $l->type,
                'type_label'      => $l->typeLabel(),
                'price'           => $l->price,
                'formatted_price' => $l->formattedPrice(),
                'location_label'  => $l->location_label,
                'bedrooms'        => $l->bedrooms,
                'bathrooms'       => $l->bathrooms,
                'land_area'       => $l->land_area,
                'building_area'   => $l->building_area,
                'description'     => $l->description,
                'contact_name'    => $l->contact_name,
                'contact_phone'   => $l->contact_phone,
                'images'          => $l->imageUrls(),
                'status'          => $l->status,
            ])
            ->values()
            ->all();

        return response()->json([
            'listings' => $listings,
            'footer'   => $footer,
        ]);
    }
}
