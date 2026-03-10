@php
  $blocks = \App\Models\Block::active()->orderBy('name')->get();
@endphp
{{-- ============================================================
Modal: Approve User
Allows admin to assign Block + Unit before approving.
If email exists in residents, Block/Unit are auto-filled and locked.
Trigger: openApproveModal(userId, userName, userEmail, currentBlockId, currentUnitNumber)
============================================================ --}}
<div id="modal-approve"
  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden"
  onclick="closeModalOnBackdrop(event, 'modal-approve')">
  <div
    class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">

    {{-- Header --}}
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
          <span class="material-icons text-emerald-600 dark:text-emerald-400">verified</span>
        </div>
        <div>
          <h2 class="text-lg font-bold text-slate-900 dark:text-white">Approve User</h2>
          <p id="approve-subtitle" class="text-xs text-slate-500 mt-0.5"></p>
        </div>
      </div>
      <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
        onclick="closeModal('modal-approve')">
        <span class="material-icons">close</span>
      </button>
    </div>

    {{-- Form --}}
    <form id="form-approve-user" method="POST" action="" class="p-8 space-y-5">
      @csrf
      {{-- Change from PATCH to POST handled by controller route --}}

      {{-- Block & Unit section --}}
      <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
          <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">Household Assignment</p>
          <p class="text-[11px] text-slate-400 mt-0.5">Assign the user's block and unit number.</p>
        </div>
        <div class="p-4 space-y-4">

          {{-- Resident status badge --}}
          <div id="approve-resident-badge-found"
            class="hidden items-center gap-2 text-xs text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-2 rounded-lg border border-emerald-200 dark:border-emerald-800">
            <span class="material-icons text-sm">check_circle</span>
            Resident record found — block &amp; unit auto-filled.
          </div>
          <div id="approve-resident-badge-notfound"
            class="hidden items-center gap-2 text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-3 py-2 rounded-lg border border-amber-200 dark:border-amber-800">
            <span class="material-icons text-sm">info</span>
            No resident record — please assign block &amp; unit manually (optional).
          </div>

          {{-- Block --}}
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-500 uppercase">Block</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-icons text-slate-400 text-sm">location_city</span>
              </span>
              <select id="approve-block-id" name="block_id"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                <option value="">— No block assigned —</option>
                @foreach ($blocks as $block)
                  <option value="{{ $block->id }}">{{ $block->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          {{-- Unit Number --}}
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-500 uppercase">Unit Number</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-icons text-slate-400 text-sm">home</span>
              </span>
              <input id="approve-unit-number" name="unit_number" type="text"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"
                placeholder="e.g. A-01" />
            </div>
          </div>

        </div>
      </div>

      {{-- Actions --}}
      <div class="flex items-center gap-4 pt-1">
        <button type="button" onclick="closeModal('modal-approve')"
          class="flex-1 px-6 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          Cancel
        </button>
        <button type="submit"
          class="flex-1 px-6 py-3 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 transition-all flex items-center justify-center gap-2">
          <span class="material-icons text-sm">verified</span>
          Approve User
        </button>
      </div>
    </form>
  </div>
</div>