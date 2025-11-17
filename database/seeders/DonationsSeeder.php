<?php

namespace Database\Seeders;

use App\Enums\DonationFrequency;
use App\Models\Donation;
use Illuminate\Database\Seeder;

class DonationsSeeder extends Seeder
{
    public function run(): void
    {

        Donation::factory()->withCertificado()->withPayment()->create();

        Donation::factory()->withCertificado()->withPayment()->recurrente(DonationFrequency::MENSUAL->value)->create();
        Donation::factory()->withCertificado()->withPayment()->recurrente(DonationFrequency::TRIMESTRAL->value)->create();
        Donation::factory()->withCertificado()->withPayment()->recurrente(DonationFrequency::ANUAL->value)->create();
    }
}
