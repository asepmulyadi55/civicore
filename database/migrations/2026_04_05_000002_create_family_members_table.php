<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('resident_id')->constrained('residents')->cascadeOnDelete();
            $table->string('fullname');
            $table->enum('relationship', ['head', 'spouse', 'child', 'parent', 'tenant', 'other'])->default('other');
            $table->string('nik', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('education', [
                'none', 'elementary', 'junior_high', 'senior_high',
                'associate', 'bachelor', 'master', 'doctorate', 'other',
            ])->nullable();
            $table->string('occupation')->nullable();
            $table->boolean('is_head')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
