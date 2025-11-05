<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                // Polymorphic relation to Order or Donation
                $table->morphs('invoiceable');

                // Numbering
                $table->string('series', 10); // e.g., FP or FD
                $table->unsignedSmallInteger('year');
                $table->unsignedBigInteger('sequence');
                $table->string('number', 50)->unique(); // e.g., FP-2025-000123

                // Amounts
                $table->decimal('subtotal', 12, 2);
                $table->decimal('vat_rate', 5, 4); // 0.2100 for 21%
                $table->decimal('vat_amount', 12, 2);
                $table->decimal('total', 12, 2);
                $table->string('currency', 3)->default('EUR');

                // Storage
                $table->string('storage_path'); // relative storage path

                // Email tracking
                $table->timestamp('sent_at')->nullable();
                $table->json('emailed_to')->nullable();

                $table->timestamps();

                // Ensure uniqueness per series-year-sequence
                $table->unique(['series', 'year', 'sequence']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
