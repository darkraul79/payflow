<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
  public function run(): void
  {
    Page::factory()
      ->isHome()
      ->published()
      ->create([
        'title' => 'Home',
        'slug' => 'home',
      ]);
    Page::factory()
      ->count(2)
      ->published()
      ->create();

    Page::factory()
      ->count(2)
      ->create();
  }
}
