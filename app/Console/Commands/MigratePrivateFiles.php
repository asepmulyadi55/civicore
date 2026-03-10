<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigratePrivateFiles extends Command
{
  protected $signature = 'files:migrate-private {--dry-run : List files that would be moved without actually moving them}';
  protected $description = 'Move proof and avatar files from public disk (storage/app/public/) to private disk (storage/app/private/).';

  public function handle(): int
  {
    $dryRun = $this->option('dry-run');
    $public = Storage::disk('public');
    $private = Storage::disk('local');
    $total = 0;
    $moved = 0;
    $skipped = 0;

    foreach (['proofs', 'avatars'] as $folder) {
      if (!$public->exists($folder)) {
        $this->line("  <fg=yellow>Folder [{$folder}] not found on public disk — skipping.</>");
        continue;
      }

      $files = $public->files($folder);

      foreach ($files as $file) {
        $total++;

        if ($private->exists($file)) {
          $this->line("  <fg=yellow>SKIP (already exists on private):</> {$file}");
          $skipped++;
          continue;
        }

        if ($dryRun) {
          $this->line("  <fg=cyan>WOULD MOVE:</> {$file}");
          $moved++;
          continue;
        }

        $contents = $public->get($file);
        $private->put($file, $contents);
        $public->delete($file);
        $this->line("  <fg=green>MOVED:</> {$file}");
        $moved++;
      }
    }

    $action = $dryRun ? 'Would move' : 'Moved';
    $this->newLine();
    $this->info("Done. {$action} {$moved} / {$total} files. Skipped: {$skipped}.");

    return self::SUCCESS;
  }
}
