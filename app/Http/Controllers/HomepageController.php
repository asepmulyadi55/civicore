<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomepageController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $hero = json_decode(Setting::get('homepage_hero', '{}'), true) ?? [];
        $featuredEvent = json_decode(Setting::get('homepage_featured_event', '{}'), true) ?? [];
        $allEvents = json_decode(Setting::get('homepage_events', '[]'), true) ?? [];
        $allBuletin = json_decode(Setting::get('homepage_buletin', '[]'), true) ?? [];
        $about = json_decode(Setting::get('homepage_about', '{}'), true) ?? [];
        $memorableMoments = json_decode(Setting::get('homepage_memorable_moments', '{}'), true) ?? [];
        $footer = json_decode(Setting::get('homepage_footer', '{}'), true) ?? [];
        $sectionLabels = array_merge(
            ['featured_eyebrow' => 'Featured Event', 'events_eyebrow' => 'Discover More', 'events_heading' => 'Upcoming Community Events', 'buletin_eyebrow' => 'Informasi', 'buletin_heading' => 'Buletin'],
            json_decode(Setting::get('homepage_section_labels', '{}'), true) ?? []
        );

        // ── Event filters ────────────────────────────────────────────────────
        $search = trim($request->input('event_search', ''));
        $filterCategory = $request->input('event_category', '');
        $filtered = $allEvents;

        if ($search !== '') {
            $s = mb_strtolower($search);
            $filtered = array_values(array_filter(
                $filtered,
                fn($e) =>
                str_contains(mb_strtolower($e['title'] ?? ''), $s) ||
                str_contains(mb_strtolower($e['description'] ?? ''), $s)
            ));
        }
        if ($filterCategory !== '') {
            $filtered = array_values(array_filter(
                $filtered,
                fn($e) =>
                ($e['category'] ?? '') === $filterCategory
            ));
        }

        // Sort: newest date first, undated events at end
        usort($filtered, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

        // ── Pagination ───────────────────────────────────────────────────────
        $perPage = 10;
        $currentPage = max(1, (int) $request->input('event_page', 1));
        $total = count($filtered);
        $events = array_slice($filtered, ($currentPage - 1) * $perPage, $perPage);
        $pagination = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => max(1, (int) ceil($total / $perPage)),
            'search' => $search,
            'category' => $filterCategory,
        ];

        $totalEvents = count($allEvents);

        // ── Buletin filters ──────────────────────────────────────────────────
        $buletinSearch = trim($request->input('buletin_search', ''));
        $filteredBuletin = $allBuletin;

        if ($buletinSearch !== '') {
            $s = mb_strtolower($buletinSearch);
            $filteredBuletin = array_values(array_filter(
                $filteredBuletin,
                fn($b) =>
                str_contains(mb_strtolower($b['title'] ?? ''), $s) ||
                str_contains(mb_strtolower($b['description'] ?? ''), $s)
            ));
        }

        usort($filteredBuletin, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

        $buletinPerPage = 10;
        $buletinPage = max(1, (int) $request->input('buletin_page', 1));
        $buletinTotal = count($filteredBuletin);
        $buletin = array_slice($filteredBuletin, ($buletinPage - 1) * $buletinPerPage, $buletinPerPage);
        $buletinPagination = [
            'total'        => $buletinTotal,
            'per_page'     => $buletinPerPage,
            'current_page' => $buletinPage,
            'last_page'    => max(1, (int) ceil($buletinTotal / $buletinPerPage)),
            'search'       => $buletinSearch,
        ];
        $totalBuletin = count($allBuletin);

        return view('homepage', compact(
            'hero', 'featuredEvent', 'events', 'pagination', 'about',
            'totalEvents', 'memorableMoments', 'footer',
            'buletin', 'buletinPagination', 'totalBuletin', 'sectionLabels'
        ));
    }

    // ── Hero Section ──────────────────────────────────────────────────────────

    public function updateSectionLabels(Request $request)
    {
        $validated = $request->validate([
            'featured_eyebrow' => 'nullable|string|max:60',
            'events_eyebrow'  => 'nullable|string|max:60',
            'events_heading'  => 'nullable|string|max:120',
            'buletin_eyebrow' => 'nullable|string|max:60',
            'buletin_heading' => 'nullable|string|max:120',
        ]);

        $existing = json_decode(Setting::get('homepage_section_labels', '{}'), true) ?? [];
        foreach ($validated as $k => $v) {
            if (!is_null($v)) $existing[$k] = $v;
        }
        $this->saveSetting('homepage_section_labels', json_encode($existing), 'Section Labels');

        return redirect()->route('homepage.index')->with('success', 'Section labels updated.');
    }

    public function updateHero(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:300',
            'bg_image' => 'nullable|image|max:5120', // max 5 MB
        ]);

        // Load existing hero to preserve/replace the stored image
        $existing = json_decode(Setting::get('homepage_hero', '{}'), true) ?? [];

        if ($request->hasFile('bg_image')) {
            // Delete old stored file if it exists
            if (!empty($existing['bg_image_path'])) {
                Storage::disk('public')->delete($existing['bg_image_path']);
                MediaFile::where('path', $existing['bg_image_path'])->delete();
            }

            $file = $request->file('bg_image');
            $path = $file->store('homepage', 'public');
            $publicUrl = Storage::disk('public')->url($path);

            // Track in media_files
            MediaFile::create([
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);

            $data['bg_image'] = $publicUrl;
            $data['bg_image_path'] = $path;
        } else {
            // Keep the existing image values
            $data['bg_image'] = $existing['bg_image'] ?? null;
            $data['bg_image_path'] = $existing['bg_image_path'] ?? null;
        }

        $this->saveSetting('homepage_hero', json_encode($data), 'Hero Section');

        return redirect()->route('homepage.index')->with('success', 'Hero section saved.');
    }

    // ── Featured Event ────────────────────────────────────────────────────────

    public function updateFeaturedEvent(Request $request)
    {
        $data = $request->validate([
            'type'       => 'required|string|in:full,simple',
            'title'      => 'required|string|max:200',
            'youtube_id' => 'nullable|string|max:20',
            'date'       => 'nullable|date',
            'image_file' => 'nullable|image|max:5120',
        ]);

        $existing = json_decode(Setting::get('homepage_featured_event', '{}'), true) ?? [];

        if ($request->hasFile('image_file')) {
            // Delete old stored file if it exists
            if (!empty($existing['image_path'])) {
                Storage::disk('public')->delete($existing['image_path']);
                MediaFile::where('path', $existing['image_path'])->delete();
            }

            $file = $request->file('image_file');
            $path = $file->store('homepage/featured', 'public');
            $publicUrl = Storage::disk('public')->url($path);

            MediaFile::create([
                'disk'          => 'public',
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'uploaded_by'   => auth()->id(),
            ]);

            $data['image_url']  = $publicUrl;
            $data['image_path'] = $path;
        } else {
            // Keep existing image values
            $data['image_url']  = $existing['image_url']  ?? null;
            $data['image_path'] = $existing['image_path'] ?? null;
        }

        // Clear YouTube/date fields when type is simple
        if ($data['type'] === 'simple') {
            $data['youtube_id'] = null;
            $data['date']       = null;
        }

        unset($data['image_file']);
        $this->saveSetting('homepage_featured_event', json_encode($data), 'Featured Event');

        return redirect()->route('homepage.index')->with('success', 'Featured event saved.');
    }

    // ── Events List ───────────────────────────────────────────────────────────

    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'date' => 'nullable|date',
            'category' => 'nullable|string|in:wellness,meetings,education,cultural,sports,other',
            'url' => 'nullable|url|max:500',
            'image_file' => 'nullable|image|max:5120',
        ]);

        // Derive status automatically from date
        $validated['status'] = (!empty($validated['date']) && $validated['date'] < now()->toDateString())
            ? 'past' : 'upcoming';

        $imageUrl = null;
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $path = $file->store('homepage/events', 'public');
            $imageUrl = Storage::disk('public')->url($path);
            MediaFile::create([
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
        }

        $events = json_decode(Setting::get('homepage_events', '[]'), true) ?? [];
        $events[] = array_merge(
            array_diff_key($validated, ['image_file' => null]),
            ['id' => (string) Str::uuid(), 'image_url' => $imageUrl]
        );

        $this->saveSetting('homepage_events', json_encode(array_values($events)), 'Events List');

        return redirect()->route('homepage.index')->with('success', 'Event added.');
    }

    public function updateEvent(Request $request, string $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'date' => 'nullable|date',
            'category' => 'nullable|string|in:wellness,meetings,education,cultural,sports,other',
            'url' => 'nullable|url|max:500',
            'image_file' => 'nullable|image|max:5120',
        ]);

        // Derive status automatically from date
        $validated['status'] = (!empty($validated['date']) && $validated['date'] < now()->toDateString())
            ? 'past' : 'upcoming';

        $events = json_decode(Setting::get('homepage_events', '[]'), true) ?? [];
        $found = false;
        foreach ($events as &$event) {
            if (($event['id'] ?? '') === $id) {
                // Handle image replacement
                if ($request->hasFile('image_file')) {
                    // Delete old file if stored
                    if (!empty($event['image_path'])) {
                        Storage::disk('public')->delete($event['image_path']);
                        MediaFile::where('path', $event['image_path'])->delete();
                    }
                    $file = $request->file('image_file');
                    $path = $file->store('homepage/events', 'public');
                    $url = Storage::disk('public')->url($path);
                    MediaFile::create([
                        'disk' => 'public',
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => auth()->id(),
                    ]);
                    $event['image_url'] = $url;
                    $event['image_path'] = $path;
                }
                $event = array_merge($event, array_diff_key($validated, ['image_file' => null]));
                $found = true;
                break;
            }
        }
        unset($event);

        if (!$found) {
            return redirect()->route('homepage.index')->with('error', 'Event not found.');
        }

        $this->saveSetting('homepage_events', json_encode(array_values($events)), 'Events List');

        return redirect()->route('homepage.index')->with('success', 'Event updated.');
    }

    public function destroyEvent(string $id)
    {
        $events = json_decode(Setting::get('homepage_events', '[]'), true) ?? [];
        $events = array_values(array_filter($events, fn($e) => ($e['id'] ?? '') !== $id));

        $this->saveSetting('homepage_events', json_encode($events), 'Events List');

        return redirect()->route('homepage.index')->with('success', 'Event removed.');
    }

    // ── Buletin ───────────────────────────────────────────────────────────────

    public function storeBuletin(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'date'        => 'nullable|date',
            'url'         => 'nullable|url|max:500',
            'image_file'  => 'nullable|image|max:5120',
        ]);

        $imageUrl  = null;
        $imagePath = null;
        if ($request->hasFile('image_file')) {
            $file      = $request->file('image_file');
            $path      = $file->store('homepage/buletin', 'public');
            $imageUrl  = Storage::disk('public')->url($path);
            $imagePath = $path;
            MediaFile::create([
                'disk'          => 'public',
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'uploaded_by'   => auth()->id(),
            ]);
        }

        $buletin = json_decode(Setting::get('homepage_buletin', '[]'), true) ?? [];
        $buletin[] = array_merge(
            array_diff_key($validated, ['image_file' => null]),
            ['id' => (string) Str::uuid(), 'image_url' => $imageUrl, 'image_path' => $imagePath]
        );

        $this->saveSetting('homepage_buletin', json_encode(array_values($buletin)), 'Buletin');

        return redirect()->route('homepage.index')->with('success', 'Buletin added.');
    }

    public function updateBuletin(Request $request, string $id)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'date'        => 'nullable|date',
            'url'         => 'nullable|url|max:500',
            'image_file'  => 'nullable|image|max:5120',
        ]);

        $buletin = json_decode(Setting::get('homepage_buletin', '[]'), true) ?? [];
        $found   = false;
        foreach ($buletin as &$item) {
            if (($item['id'] ?? '') === $id) {
                if ($request->hasFile('image_file')) {
                    if (!empty($item['image_path'])) {
                        Storage::disk('public')->delete($item['image_path']);
                        MediaFile::where('path', $item['image_path'])->delete();
                    }
                    $file            = $request->file('image_file');
                    $path            = $file->store('homepage/buletin', 'public');
                    $url             = Storage::disk('public')->url($path);
                    $item['image_url']  = $url;
                    $item['image_path'] = $path;
                    MediaFile::create([
                        'disk'          => 'public',
                        'path'          => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type'     => $file->getMimeType(),
                        'size'          => $file->getSize(),
                        'uploaded_by'   => auth()->id(),
                    ]);
                }
                $item  = array_merge($item, array_diff_key($validated, ['image_file' => null]));
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            return redirect()->route('homepage.index')->with('error', 'Buletin not found.');
        }

        $this->saveSetting('homepage_buletin', json_encode(array_values($buletin)), 'Buletin');

        return redirect()->route('homepage.index')->with('success', 'Buletin updated.');
    }

    public function destroyBuletin(string $id)
    {
        $buletin = json_decode(Setting::get('homepage_buletin', '[]'), true) ?? [];
        foreach ($buletin as $item) {
            if (($item['id'] ?? '') === $id && !empty($item['image_path'])) {
                Storage::disk('public')->delete($item['image_path']);
                MediaFile::where('path', $item['image_path'])->delete();
            }
        }
        $buletin = array_values(array_filter($buletin, fn($b) => ($b['id'] ?? '') !== $id));

        $this->saveSetting('homepage_buletin', json_encode($buletin), 'Buletin');

        return redirect()->route('homepage.index')->with('success', 'Buletin removed.');
    }

    // ── About Section ─────────────────────────────────────────────────────────

    public function updateAbout(Request $request)
    {
        $data = $request->validate([
            'badge'         => 'nullable|string|max:60',
            'heading'       => 'required|string|max:120',
            'btn1_label'    => 'nullable|string|max:60',
            'btn1_url'      => 'nullable|url|max:500',
            'btn2_label'    => 'nullable|string|max:60',
            'btn2_url'      => 'nullable|url|max:500',
            'content'       => 'required|string|max:3000',
            'stats'         => 'nullable|array|max:4',
            'stats.*.value' => 'nullable|string|max:50',
            'stats.*.label' => 'nullable|string|max:50',
        ]);

        // Clean out empty stat rows
        if (!empty($data['stats'])) {
            $data['stats'] = array_values(array_filter(
                $data['stats'],
                fn($s) => !empty($s['value']) || !empty($s['label'])
            ));
        }

        $this->saveSetting('homepage_about', json_encode($data), 'About Section');

        return redirect()->route('homepage.index')->with('success', 'About section saved.');
    }

    // ── Footer Section ────────────────────────────────────────────────────────

    public function updateFooter(Request $request)
    {
        $data = $request->validate([
            'brand_name'     => 'nullable|string|max:100',
            'tagline'        => 'nullable|string|max:300',
            'contact_email'  => 'nullable|email|max:200',
            'contact_phone'  => 'nullable|string|max:50',
            'location'       => 'nullable|string|max:200',
            'facebook_url'   => 'nullable|url|max:500',
            'instagram_url'  => 'nullable|url|max:500',
            'copyright'      => 'nullable|string|max:300',
            'bottom_note'    => 'nullable|string|max:300',
            'links'          => 'nullable|array|max:4',
            'links.*.label'  => 'nullable|string|max:60',
            'links.*.url'    => 'nullable|url|max:500',
        ]);

        if (!empty($data['links'])) {
            $data['links'] = array_values(array_filter(
                $data['links'],
                fn($l) => !empty($l['label']) || !empty($l['url'])
            ));
        }

        $this->saveSetting('homepage_footer', json_encode($data), 'Footer Section');

        return redirect()->route('homepage.index')->with('success', 'Footer saved.');
    }

    public function updateMemorableMoments(Request $request)
    {
        $request->validate([
            'eyebrow' => 'nullable|string|max:60',
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'archive_url' => 'nullable|url|max:500',
            'images.*' => 'nullable|image|max:5120',
            'captions.*' => 'nullable|string|max:200',
        ]);

        $existing = json_decode(Setting::get('homepage_memorable_moments', '{}'), true) ?? [];
        $existingImages = $existing['images'] ?? [];

        $images = [];
        for ($i = 0; $i < 4; $i++) {
            $slot = $existingImages[$i] ?? ['url' => null, 'path' => null, 'caption' => null];

            if ($request->hasFile("images.{$i}")) {
                // Delete old file if present
                if (!empty($slot['path'])) {
                    Storage::disk('public')->delete($slot['path']);
                    MediaFile::where('path', $slot['path'])->delete();
                }
                $file = $request->file("images.{$i}");
                $path = $file->store('homepage/moments', 'public');
                $url = Storage::disk('public')->url($path);
                MediaFile::create([
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);
                $slot['url'] = $url;
                $slot['path'] = $path;
            }

            $slot['caption'] = $request->input("captions.{$i}") ?? $slot['caption'];
            $images[] = $slot;
        }

        $data = [
            'eyebrow' => $request->input('eyebrow', $existing['eyebrow'] ?? 'The Gallery'),
            'title' => $request->input('title', $existing['title'] ?? ''),
            'subtitle' => $request->input('subtitle', $existing['subtitle'] ?? ''),
            'archive_url' => $request->input('archive_url', $existing['archive_url'] ?? null),
            'images' => $images,
        ];

        $this->saveSetting('homepage_memorable_moments', json_encode($data), 'Memorable Moments');

        return redirect()->route('homepage.index')->with('success', 'Memorable Moments saved.');
    }

    // ── Private helper ────────────────────────────────────────────────────────

    /**
     * Upsert a homepage CMS setting, supplying the required `label` and `group`
     * columns that the settings table enforces (label is NOT NULL).
     */
    private function saveSetting(string $key, string $value, string $label): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'label' => $label, 'group' => 'homepage'],
        );
        Cache::forget("setting:{$key}");
    }
}
