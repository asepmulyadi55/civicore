<?php

namespace App\Observers;

use App\Models\PropertyListing;
use Illuminate\Support\Facades\Cache;

class PropertyListingObserver
{
    private function flush(): void
    {
        // Flush all public API homepage caches that include property listings
        Cache::forget('api:homepage:index');
        Cache::forget('api:homepage:property');
    }

    public function created(PropertyListing $listing): void  { $this->flush(); }
    public function updated(PropertyListing $listing): void  { $this->flush(); }
    public function deleted(PropertyListing $listing): void  { $this->flush(); }
}
