<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('finance_transactions');

        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['income', 'expense']);
            $table->string('category', 100)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('description', 255);
            $table->text('notes')->nullable();
            $table->date('transaction_date');
            $table->unsignedTinyInteger('report_month'); // 1-12
            $table->unsignedSmallInteger('report_year');
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['report_year', 'report_month'], 'fin_tx_year_month_idx');
            $table->index(['report_year', 'report_month', 'type'], 'fin_tx_year_month_type_idx');
            $table->index('transaction_date', 'fin_tx_date_idx');
            $table->index('category', 'fin_tx_category_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
    }
};
