<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
  public function run(): void
  {
    User::factory()->create([
      'name' => 'Raul Sebastian',
      'email' => 'info@raulsebastian.es',
      'password' => bcrypt('ajax656'),
    ]);
    User::factory()->create([
      'name' => 'Test User',
      'email' => 'test@example.com',
      'password' => bcrypt('password'),
    ]);

  }
}
