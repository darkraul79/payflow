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

        setting(['billing.company' => 'Fundación Elena Tertre']);
        setting(['billing.nif' => 'G12345678']);
        setting([
            'billing.email' => 'ayuda@fundacionelenatertre.es',
        ]);
        setting(['billing.address' => 'Calle Falsa, 123']);
        setting(['billing.city' => 'Madrid']);
        setting(['billing.postal_code' => '28080']);
        setting(['billing.country' => 'España']);
        setting(['billing.phone' => '648 986 753']);

        setting(['billing.vat.orders_default' => '21']);
        setting(['billing.vat.donations_default' => '21']);
        setting(['billing.vat.donations_default' => '0']);

    }
}
