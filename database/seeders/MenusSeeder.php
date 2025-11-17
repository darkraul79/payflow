<?php

namespace Database\Seeders;

use Datlechin\FilamentMenuBuilder\Models\Menu;
use Datlechin\FilamentMenuBuilder\Models\MenuLocation;
use Illuminate\Database\Seeder;

class MenusSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            'header' => 'Principal',
            'footer1' => 'Acerca de Nosotros',
            'footer2' => 'Enlaces de ayuda',
        ];
        foreach ($menus as $location => $menu) {
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            $m = Menu::create([
                'name' => $menu,
                'is_visible' => true,
            ]);
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            MenuLocation::create([
                'menu_id' => $m->id,
                'location' => $location,
            ]);
        }

    }
}
