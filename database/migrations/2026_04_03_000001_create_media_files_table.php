<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('disk', 30)->default('public');
            $table->string('path');                     // relative path on disk, e.g. homepage/abc.jpg
            $table->string('original_name');            // original filename from user
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0); // bytes
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('path');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
