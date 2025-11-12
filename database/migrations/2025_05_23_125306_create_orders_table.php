<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 8)->unique();
            $table->string('shipping');
            $table->float('shipping_cost')->default(0.00);
            $table->float('subtotal')->default(0.00);
            $table->float('taxes')->default(0.00);
            $table->float('total')->default(0.00);
            $table->string('payment_method');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
