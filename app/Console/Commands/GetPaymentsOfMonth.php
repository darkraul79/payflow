<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDonationPaymentJob;
use App\Models\Donation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetPaymentsOfMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments-of-month:process
    {--list : Muestra un listado de las donaciones recurrentes que se van a procesar sin ejecutar los pagos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta los pagos de las donaciones recurrentes del mes';

    protected string $initMessage = 'Iniciando proceso de pagos de donaciones recurrentes del mes...';

    protected string $finishMessage = 'Proceso finalizado';

    protected string $noDonationsMessage = 'No hay donaciones recurrentes que procesar este mes.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        Log::info($this->initMessage);
        $this->info($this->initMessage);

        $donaciones = Donation::nextPaymentsDonations()->get();

        foreach ($donaciones as $donacion) {
            if ($this->option('list') === false) {
                ProcessDonationPaymentJob::dispatch($donacion);
            }
            $message = "Jobs creados para la donación $donacion->id con fecha de pago $donacion->next_payment";
            $this->warn($message);
            Log::info($message);
        }

        if ($donaciones->isEmpty()) {
            $this->warn($this->noDonationsMessage);
            Log::warning($this->noDonationsMessage);
        }

        $this->info($this->finishMessage);
        Log::info($this->finishMessage);

    }
}
