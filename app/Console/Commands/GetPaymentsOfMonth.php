<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDonationPaymentJob;
use App\Models\Donation;
use Illuminate\Console\Command;

class GetPaymentsOfMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments-of-month:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta los pagos de las donaciones recurrentes del mes';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $donaciones = Donation::recurrents()
            ->activas()
            ->whereDate(
                'next_payment', now()->format('Y-m-d'),
            )->get();

        foreach ($donaciones as $donacion) {
            ProcessDonationPaymentJob::dispatch($donacion);
            $this->info('Jobs creados para las donacion ' . $donacion->id . ' con fecha de pago ' . $donacion->next_payment);
        }

    }
}
