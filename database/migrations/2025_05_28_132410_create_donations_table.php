<?php

use App\Models\Donation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->float('amount')->default(0.00);
            $table->string('number', 8)->unique();
            $table->string('frequency')->nullable();
            $table->string('type')->default(Donation::UNICA);
            $table->string('identifier')->nullable();
            $table->json('info')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
