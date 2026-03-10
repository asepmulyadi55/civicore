<?php

namespace App\Enums;

enum PaymentStatus: string
{
  case Unpaid = 'unpaid';
  case Pending = 'pending';
  case Approved = 'approved';
  case Rejected = 'rejected';

  public function label(): string
  {
    return match ($this) {
      self::Unpaid => 'Unpaid',
      self::Pending => 'Pending',
      self::Approved => 'Approved',
      self::Rejected => 'Rejected',
    };
  }

  public function badgeClass(): string
  {
    return match ($this) {
      self::Approved => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
      self::Pending => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
      self::Rejected => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
      self::Unpaid => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
    };
  }
}
