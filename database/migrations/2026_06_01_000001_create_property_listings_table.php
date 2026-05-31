<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('property_listings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->enum('type', ['sell', 'rent']);
            $table->decimal('price', 15, 0)->nullable();
            $table->string('location_label')->nullable();
            $table->foreignUuid('block_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->decimal('land_area', 8, 2)->nullable();
            $table->decimal('building_area', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->json('images')->nullable();
            $table->enum('status', ['available', 'sold', 'rented'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_listings');
    }
};
