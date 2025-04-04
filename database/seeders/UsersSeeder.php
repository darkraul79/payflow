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
            'name' => 'David Tertre',
            'email' => 'dtertre@surf3.es',
            'password' => bcrypt('password'),
        ]);

    }
}
