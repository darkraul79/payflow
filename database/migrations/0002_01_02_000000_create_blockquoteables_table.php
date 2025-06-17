<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blockquoteables', function (Blueprint $table) {
            $table->foreignId('blockquote_id')->constrained('blockquotes');
            $table->foreignId('blockquoteable_id');
            $table->string('blockquoteable_type');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blockquoteables');
    }
};
