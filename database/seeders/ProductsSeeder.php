<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()->hasOffer(2.99)->imagen(public_path('storage/productos/botella-azul.webp'))->create([
            'name' => 'Botella de agua',
            'price' => 10.99,
            'description' => '<p>Botella de agua para uso diario.</p><p>Bidón en Aluminio Reciclado de 600 ml de capacidad.<br>De originales y variados colores pastel metalizados.<br>Tapón a rosca en bambú con cordón de transporte<br>trenzado.</p><p>Presentado en una caja de diseño kraft.<br><em>600 ml</em></p>',
            'stock' => 2,
        ]);

        Product::factory()->hasOffer(5.99)->imagen([public_path('storage/productos/botella-azul2.webp'), public_path('storage/productos/botella-azul.webp')])->create([
            'name' => 'Botella de agua 2',
            'price' => 10.99,
            'description' => '<p>Botella de agua para uso diario.</p><p>Bidón en Aluminio Reciclado de 600 ml de capacidad.<br>De originales y variados colores pastel metalizados.<br>Tapón a rosca en bambú con cordón de transporte<br>trenzado.</p><p>Presentado en una caja de diseño kraft.<br><em>600 ml</em></p>',
            'stock' => 2,
        ]);

        Product::factory()->count(10)->imagen(public_path('storage/productos/botella-azul.webp'))->create();
    }
}
