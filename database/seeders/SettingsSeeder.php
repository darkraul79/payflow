<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {

        setting(['contact.email' => 'ayuda@fundacionelenatertre.es']);
        setting(['contact.telefono' => '648 986 753']);
        setting(['contact.horario' => '14:30 a 19:30']);

        setting(['rss.facebook' => 'https://www.facebook.com']);
        setting(['rss.x' => 'https://www.x.com']);
        setting(['rss.instagram' => 'https://www.instagram.com']);
        setting(['rss.youtube' => 'https://www.youtube.com']);


    }
}
