<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodsSeeder extends Seeder
{
    public function run(): void
    {
        ShippingMethod::factory()->create([
            'name' => 'Gratis',
            'price' => 0,
        ]);
        ShippingMethod::factory()->create([
            'name' => 'Seur',
            'price' => 3.50,
        ]);
    }
}
