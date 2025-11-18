<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('gateway_transactions')->cascadeOnDelete();
            $table->string('refund_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('reason')->nullable();
            $table->string('status')->index(); // pending, completed, failed
            $table->json('gateway_response')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_refunds');
    }
};
