<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->string('nif')->nullable();
            $table->string('address');
            $table->foreignId('order_id')->constrained('orders');
            $table->string('province');
            $table->string('city');
            $table->string('cp');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_addresses');
    }
};
