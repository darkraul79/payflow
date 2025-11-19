<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('gateway')->index(); // redsys, stripe, paypal, etc.
            $table->string('transaction_id')->unique();
            $table->string('order_id')->index();
            $table->morphs('transactable'); // Can be Order, Donation, etc.
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('status')->index(); // pending, completed, failed, refunded
            $table->string('payment_method')->nullable(); // card, bizum, paypal, etc.
            $table->json('gateway_request')->nullable();
            $table->json('gateway_response')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['gateway', 'status']);
            $table->index(['transactable_type', 'transactable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_transactions');
    }
};
