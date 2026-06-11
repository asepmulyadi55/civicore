<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyListing;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HomepageController extends Controller
{
    /**
     * Public JSON endpoint — homepage content for the React SPA.
     *
     * GET /api/homepage
     */
    public function index(): JsonResponse
    {
        $data = Cache::remember('api:homepage:index', now()->addMinutes(60), function () {
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

            return [
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
            ];
        });

        return response()->json($data);
    }

    /**
     * All events for the events listing page.
     *
     * GET /api/events
     */
    public function events(): JsonResponse
    {
        $data = Cache::remember('api:homepage:events', now()->addMinutes(60), function () {
            $events = json_decode(Setting::get('homepage_events', '[]'), true) ?? [];
            $footer = json_decode(Setting::get('homepage_footer',  '{}'), true) ?? [];

            $today = now()->toDateString();
            usort($events, function ($a, $b) use ($today) {
                $aDate    = $a['date'] ?? '';
                $bDate    = $b['date'] ?? '';
                $aIsPast  = !empty($aDate) && $aDate < $today;
                $bIsPast  = !empty($bDate) && $bDate < $today;

                if ($aIsPast !== $bIsPast) {
                    return $aIsPast <=> $bIsPast;
                }

                if (!$aIsPast) {
                    $aEmpty = empty($aDate);
                    $bEmpty = empty($bDate);
                    if ($aEmpty !== $bEmpty) return $aEmpty <=> $bEmpty;
                }

                return strcmp($aDate, $bDate);
            });

            return [
                'events' => array_values($events),
                'footer' => $footer,
            ];
        });

        return response()->json($data);
    }

    /**
     * All buletin for the buletin listing page.
     *
     * GET /api/buletin
     */
    public function buletin(): JsonResponse
    {
        $data = Cache::remember('api:homepage:buletin', now()->addMinutes(60), function () {
            $buletin = json_decode(Setting::get('homepage_buletin', '[]'), true) ?? [];
            $footer  = json_decode(Setting::get('homepage_footer',  '{}'), true) ?? [];

            $today = now()->toDateString();
            usort($buletin, function ($a, $b) use ($today) {
                $aDate   = $a['date'] ?? '';
                $bDate   = $b['date'] ?? '';
                $aIsPast = !empty($aDate) && $aDate < $today;
                $bIsPast = !empty($bDate) && $bDate < $today;

                if ($aIsPast !== $bIsPast) {
                    return $aIsPast <=> $bIsPast;
                }

                if (!$aIsPast) {
                    $aEmpty = empty($aDate);
                    $bEmpty = empty($bDate);
                    if ($aEmpty !== $bEmpty) return $aEmpty <=> $bEmpty;
                }

                return strcmp($aDate, $bDate);
            });

            return [
                'buletin' => array_values($buletin),
                'footer'  => $footer,
            ];
        });

        return response()->json($data);
    }

    /**
     * Active property listings for the public property page.
     *
     * GET /api/property
     */
    public function property(): JsonResponse
    {
        $data = Cache::remember('api:homepage:property', now()->addMinutes(60), function () {
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

            return [
                'listings' => $listings,
                'footer'   => $footer,
            ];
        });

        return response()->json($data);
    }

    /**
     * Single property detail.
     *
     * GET /api/property/{id}
     */
    public function propertyDetail(string $id): JsonResponse
    {
        $l = PropertyListing::with(['block'])
            ->where('is_active', true)
            ->where('id', $id)
            ->first();

        if (!$l) {
            return response()->json(['error' => 'not_found'], 404);
        }

        $footer = json_decode(Setting::get('homepage_footer', '{}'), true) ?? [];

        return response()->json([
            'listing' => [
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
                'status_label'    => $l->statusLabel(),
                'block_name'      => $l->block?->name,
            ],
            'footer' => $footer,
        ]);
    }
}
