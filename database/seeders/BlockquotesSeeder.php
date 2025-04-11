<?php

namespace Database\Seeders;

use App\Models\Blockquote;
use Illuminate\Database\Seeder;

class BlockquotesSeeder extends Seeder
{
    public function run(): void
    {
        Blockquote::factory()->create([
            'text' => 'Tu familia y tus amigos ahí estarán',
        ]);
        Blockquote::factory()->create([
            'text' => 'La vida en un instante te puede cambiar',
        ]);
    }
}
