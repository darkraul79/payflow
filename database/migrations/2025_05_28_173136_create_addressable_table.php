<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addressables', function (Blueprint $table) {
            $table->foreignId('address_id')->constrained('addresses');
            $table->foreignId('addressable_id');
            $table->string('addressable_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addressables');
    }
};
