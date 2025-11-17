<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagsSeeder extends Seeder
{
    public function run(): void
    {

        $tags = [
            'Osteosarcoma',
            'Programas',
            'Musico terapia',
        ];

        foreach ($tags as $tag) {
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            Tag::create([
                'name' => $tag,
            ]);
        }
    }
}
