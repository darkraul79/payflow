<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SponsorsSeeder extends Seeder
{
    public function run(): void
    {
        $array = [
            [
                'name' => 'Iberdrola',
                'url' => 'https://www.iberdrola.com/',
                'order' => 2,
                'image' => public_path('/images/sponsors/iberdrola.jpg'),
            ],
            [
                'name' => 'Google',
                'url' => 'https://www.google.com/',
                'order' => 1,
                'image' => public_path('/images/sponsors/descarga.jpeg'),
            ],
        ];

        foreach ($array as $sponsor) {
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            $sponsorModel = Sponsor::create([
                'name' => $sponsor['name'],
                'url' => $sponsor['url'],
                'order' => $sponsor['order'],
            ]);

            $sponsorModel->addMedia($sponsor['image'])
                ->preservingOriginal()
                ->toMediaCollection('sponsors');
        }
    }
}
