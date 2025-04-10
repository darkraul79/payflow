<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->json('blocks')->nullable();
            $table->string('layout')->default('default')->index();
            $table->boolean('is_home')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->dateTime('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
