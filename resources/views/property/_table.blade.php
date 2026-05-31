{{-- Property Listings Table --}}
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
  <div class="overflow-x-auto">
    <table class="w-full text-left">
      <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
        <tr>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.property_col_listing') }}</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider hidden md:table-cell">{{ __('app.property_col_price') }}</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider hidden lg:table-cell">{{ __('app.property_col_details') }}</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider hidden sm:table-cell">{{ __('app.property_col_status') }}</th>
          <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('app.property_col_active') }}</th>
          <th class="px-6 py-4"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($listings as $listing)
          @php
            $typeColors = [
              'sell' => 'bg-primary/10 text-primary dark:bg-primary/20',
              'rent' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            ];
            $statusColors = [
              'available' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
              'sold'      => 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
              'rented'    => 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
            ];
            $firstImage = $listing->imageUrls()[0] ?? null;
          @endphp
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">

            {{-- Listing info --}}
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-xl overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                  @if($firstImage)
                    <img src="{{ $firstImage }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                  @else
                    <div class="w-full h-full flex items-center justify-center">
                      <span class="material-icons text-slate-400 text-xl">home</span>
                    </div>
                  @endif
                </div>
                <div>
                  <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $listing->title }}</p>
                  @if($listing->location_label)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $listing->location_label }}</p>
                  @endif
                  <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold rounded-full uppercase {{ $typeColors[$listing->type] ?? '' }}">
                    {{ $listing->typeLabel() }}
                  </span>
                </div>
              </div>
            </td>

            {{-- Price --}}
            <td class="px-6 py-4 hidden md:table-cell">
              <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $listing->formattedPrice() }}</p>
            </td>

            {{-- Details --}}
            <td class="px-6 py-4 hidden lg:table-cell">
              <div class="flex items-center gap-3 text-xs text-slate-500">
                @if($listing->bedrooms !== null)
                  <span class="flex items-center gap-1">
                    <span class="material-icons text-[13px]">bed</span> {{ $listing->bedrooms }}
                  </span>
                @endif
                @if($listing->bathrooms !== null)
                  <span class="flex items-center gap-1">
                    <span class="material-icons text-[13px]">bathroom</span> {{ $listing->bathrooms }}
                  </span>
                @endif
                @if($listing->land_area)
                  <span>LT {{ number_format($listing->land_area, 0) }}m²</span>
                @endif
                @if($listing->building_area)
                  <span>LB {{ number_format($listing->building_area, 0) }}m²</span>
                @endif
              </div>
              @if($listing->contact_phone)
                <p class="text-xs text-slate-400 mt-0.5">{{ $listing->contact_name }} · {{ $listing->contact_phone }}</p>
              @endif
            </td>

            {{-- Status --}}
            <td class="px-6 py-4 hidden sm:table-cell">
              <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $statusColors[$listing->status] ?? '' }}">
                {{ $listing->statusLabel() }}
              </span>
            </td>

            {{-- Active toggle --}}
            <td class="px-6 py-4">
              @if(auth()->user()->can('property.edit'))
                <form method="POST" action="{{ route('property.toggle-active', $listing) }}">
                  @csrf @method('PATCH')
                  <button type="submit"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $listing->is_active ? 'bg-primary' : 'bg-slate-200 dark:bg-slate-700' }}"
                    title="{{ $listing->is_active ? __('app.active') : __('app.inactive') }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $listing->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                  </button>
                </form>
              @else
                <span class="px-2 py-1 text-xs rounded-full {{ $listing->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                  {{ $listing->is_active ? __('app.active') : __('app.inactive') }}
                </span>
              @endif
            </td>

            {{-- Actions --}}
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                @if(auth()->user()->can('property.edit'))
                  <button type="button"
                    onclick="openPropertyModal({{ Js::from($listing) }})"
                    class="p-2 text-slate-400 hover:text-primary dark:hover:text-secondary transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                    title="{{ __('app.btn_edit') }}">
                    <span class="material-icons text-[18px]">edit</span>
                  </button>
                @endif
                @if(auth()->user()->can('property.delete'))
                  <button type="button"
                    onclick="confirmDeleteProperty('{{ $listing->id }}', {{ json_encode($listing->title) }})"
                    class="p-2 text-slate-400 hover:text-rose-500 transition-colors rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20"
                    title="{{ __('app.btn_delete') }}">
                    <span class="material-icons text-[18px]">delete</span>
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center gap-3 text-slate-400">
                <span class="material-icons text-5xl">home_work</span>
                <p class="text-sm font-medium">{{ __('app.property_empty') }}</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($listings->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
      {{ $listings->links() }}
    </div>
  @endif
</div>
