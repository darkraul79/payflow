<?php

namespace Database\Seeders;

use App\Models\Donation;
use Illuminate\Database\Seeder;

class DonationsSeeder extends Seeder
{
    public function run(): void
    {

        Donation::factory()->withCertificado()->withPayment()->create();

        Donation::factory()->withCertificado()->withPayment()->recurrente(Donation::FREQUENCY['MENSUAL'])->create();
        Donation::factory()->withCertificado()->withPayment()->recurrente(Donation::FREQUENCY['TRIMESTRAL'])->create();
        Donation::factory()->withCertificado()->withPayment()->recurrente(Donation::FREQUENCY['ANUAL'])->create();
    }
}
