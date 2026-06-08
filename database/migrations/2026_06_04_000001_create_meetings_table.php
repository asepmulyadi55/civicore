<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('topic', 200);
            $table->date('meeting_date');
            $table->time('meeting_time');
            $table->string('location', 150)->nullable();
            $table->longText('notes')->nullable();
            $table->foreignUuid('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();

            $table->index('meeting_date', 'meetings_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
