<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class OrdersSeeder extends Seeder
{
    public function run(): void
    {
        Mail::fake();
        Notification::fake();

        Order::factory()->withItems(1)->create();
    }
}
