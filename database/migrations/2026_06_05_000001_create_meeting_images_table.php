<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->string('path');           // relative path under private storage
            $table->string('original_name');  // original upload filename
            $table->unsignedBigInteger('size')->default(0); // bytes
            $table->string('mime_type')->default('image/jpeg');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_images');
    }
};
