<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UsersSeeder::class);
        $this->call(PagesSeeder::class);
        $this->call(PostSeeder::class);
        $this->call(BlockquotesSeeder::class);
        $this->call(TagsSeeder::class);
    }
}
