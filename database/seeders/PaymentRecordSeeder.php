<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PaymentRecordSeeder extends Seeder
{
  public function run(): void
  {
    $admin = User::where('username', 'admin')->first();
    $coordinator = User::where('username', 'coordinator')->first();
    $cash = PaymentMethod::where('name', 'cash')->first();
    $transfer = PaymentMethod::where('name', 'bank_transfer')->first();
    $eWallet = PaymentMethod::where('name', 'e_wallet')->first();

    $residents = Resident::with(['block', 'feeHistories'])->get();

    // Generate records for Jan 2023 – Dec 2024 (24 months)
    $startDate = Carbon::create(2023, 1, 1);
    $endDate = Carbon::create(2024, 12, 1);

    $residentIndex = 0;
    foreach ($residents as $resident) {
      $feeAmount = $resident->feeHistories->first()?->amount ?? 500000;

      $current = $startDate->copy();
      $monthIndex = 0;

      while ($current->lte($endDate)) {
        $monthIndex++;

        // Skip some months to create realistic unpaid gaps
        // Every 7th month is unpaid for odd-indexed residents
        if ($residentIndex % 2 !== 0 && $monthIndex % 7 === 0) {
          $current->addMonth();
          continue;
        }

        // Vary payment method
        $method = match ($monthIndex % 3) {
          0 => $cash,
          1 => $transfer,
          2 => $eWallet,
        };

        // Recent months (last 2) are pending for about half residents
        $isPending = $current->gte(Carbon::create(2024, 11, 1)) && $residentIndex % 3 === 0;

        // One rejected record as example (second resident, June 2024)
        $isRejected = $current->isSameMonth(Carbon::create(2024, 6, 1)) && $residentIndex === 1;

        $status = match (true) {
          $isRejected => 'rejected',
          $isPending => 'pending',
          default => 'approved',
        };

        PaymentRecord::firstOrCreate(
          [
            'resident_id' => $resident->id,
            'payment_month' => $current->format('Y-m-d'),
          ],
          [
            'amount' => $feeAmount,
            'payment_method_id' => $method?->id,
            'proof_path' => null,
            'status' => $status,
            'rejection_reason' => $isRejected ? 'Proof of payment image was blurry and unreadable. Please resubmit with a clearer photo.' : null,
            'submitted_by' => $coordinator?->id,
            'approved_by' => in_array($status, ['approved']) ? $admin?->id : null,
            'approved_at' => $status === 'approved' ? $current->copy()->addDays(rand(1, 5)) : null,
          ]
        );

        $current->addMonth();
      }
      $residentIndex++;
    }
  }
}
