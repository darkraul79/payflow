<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {

        Setting::factory()->create([
            'property' => 'email',
            'value' => 'ayuda@fundacionelenatertre.es',
        ]);

        Setting::factory()->create([
            'property' => 'telefono',
            'value' => '648 986 753',
        ]);

        Setting::factory()->create([
            'property' => 'horario',
            'value' => '14:30 a 19:30',
        ]);


        Setting::factory()->create([
            'property' => 'facebook',
            'value' => 'https://www.facebook.com/',
        ]);
        Setting::factory()->create([
            'property' => 'x',
            'value' => 'https://x.com/',
        ]);
        Setting::factory()->create([
            'property' => 'instagram',
            'value' => 'https://www.instagram.com/',
        ]);
        Setting::factory()->create([
            'property' => 'youtube',
            'value' => 'https://www.youtube.com/',
        ]);
    }
}
