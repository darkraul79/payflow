<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {

        Schema::dropIfExists('order_states');
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->morphs('stateable');
            $table->string('name');
            $table->json('info')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
