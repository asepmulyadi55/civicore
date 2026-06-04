<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('meeting_id')
                  ->constrained('meetings')
                  ->cascadeOnDelete();
            $table->foreignUuid('resident_id')
                  ->constrained('residents')
                  ->cascadeOnDelete();
            $table->boolean('present')->default(true);
            $table->string('remarks', 255)->nullable();
            $table->timestamps();

            // Each resident appears once per meeting
            $table->unique(['meeting_id', 'resident_id'], 'meeting_attendance_unique');
            $table->index('meeting_id', 'ma_meeting_idx');
            $table->index('resident_id', 'ma_resident_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_attendances');
    }
};
