<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDonationPaymentJob;
use App\Models\Donation;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class GetPaymentsOfMonthCommand extends Command
{
    protected $signature = 'payments-of-month:process
        {--list : Muestra listado sin procesar}
        {--limit=100 : Límite máximo de donaciones}
        {--dry-run : Simulación sin procesar}';

    protected $description = 'Ejecuta los pagos de las donaciones recurrentes del mes';

    public function handle(): int
    {
        try {
            $startTime = microtime(true);

            Log::info('Iniciando proceso de pagos recurrentes', [
                'options' => $this->options(),
                'started_at' => now()->toIso8601String(),
            ]);

            $this->info('🔄 Iniciando proceso de pagos recurrentes...');

            $limit = (int) $this->option('limit');
            $isListMode = $this->option('list');
            $isDryRun = $this->option('dry-run');

            if ($isDryRun) {
                $this->warn('⚠️  MODO DRY-RUN: No se procesarán pagos reales');
            }

            $donaciones = $this->getDonacionesPendientes($limit);

            if ($donaciones->isEmpty()) {
                $this->warn('ℹ️ No hay donaciones recurrentes que procesar este mes.');
                Log::warning('No hay donaciones recurrentes que procesar');

                return self::SUCCESS;
            }

            $this->showDonationsInfo($donaciones, $isListMode, $isDryRun);

            if (! $isListMode && ! $isDryRun) {
                $processed = $this->processDonaciones($donaciones);

                $this->info("✅ Se han encolado $processed jobs de pago.");
                Log::info('Jobs de pago encolados exitosamente', [
                    'total_processed' => $processed,
                    'total_found' => $donaciones->count(),
                ]);
            }

            $executionTime = round(microtime(true) - $startTime, 2);

            Log::info('Proceso de pagos recurrentes completado', [
                'execution_time_seconds' => $executionTime,
                'total_donations_found' => $donaciones->count(),
                'mode' => $isListMode ? 'list' : ($isDryRun ? 'dry-run' : 'process'),
            ]);

            $this->info("✅ Proceso finalizado (⏱️  {$executionTime}s)");

            return self::SUCCESS;

        } catch (Throwable $e) {
            $this->error("❌ Error crítico: {$e->getMessage()}");

            Log::error('Error crítico en comando de pagos recurrentes', [
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ]);

            return self::FAILURE;
        }
    }

    private function getDonacionesPendientes(int $limit): Collection
    {
        if ($limit <= 0 || $limit > 1000) {
            $this->warn('⚠️ Límite ajustado a 100 donaciones por seguridad');
            $limit = 100;
        }

        return Donation::nextPaymentsDonations()
            ->with(['state', 'payments' => fn ($q) => $q->latest()->limit(1)])
            ->limit($limit)
            ->get();
    }

    private function showDonationsInfo(Collection $donaciones, bool $isListMode, bool $isDryRun): void
    {
        if ($isListMode || $isDryRun) {
            $this->info("📋 Donaciones encontradas: {$donaciones->count()}");

            $headers = ['ID', 'Número', 'Importe', 'Frecuencia', 'Próximo Pago', 'Estado', 'Identifier'];
            $rows = $donaciones->map(function (Donation $donacion) {
                return [
                    $donacion->id,
                    $donacion->number,
                    '€'.number_format($donacion->amount, 2),
                    $donacion->frequency ?? 'N/A',
                    $donacion->next_payment,
                    $donacion->state->name ?? 'UNKNOWN',
                    $donacion->identifier ? '✓' : '✗',
                ];
            })->toArray();

            $this->table($headers, $rows);

            // Mostrar advertencias sobre donaciones sin identifier
            $sinIdentifier = $donaciones->filter(fn ($d) => empty($d->identifier));
            if ($sinIdentifier->isNotEmpty()) {
                $this->warn("⚠️  {$sinIdentifier->count()} donación(es) sin identifier (se omitirán en procesamiento real)");
            }
        }

        $totalAmount = $donaciones->sum('amount');
        $this->info('💰 Importe total: €'.number_format($totalAmount, 2));
    }

    private function processDonaciones(Collection $donaciones): int
    {
        $processed = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($donaciones as $donacion) {
            try {
                // Validación adicional antes de encolar
                if (empty($donacion->identifier)) {
                    $skipped++;
                    $this->warn("⚠️  Donación $donacion->id sin identifier, omitida");

                    Log::warning('Donación sin identifier omitida en proceso de pago', [
                        'donation_id' => $donacion->id,
                        'donation_number' => $donacion->number,
                    ]);

                    continue;
                }

                ProcessDonationPaymentJob::dispatch($donacion);
                $processed++;

                Log::info("Job encolado para donación $donacion->id", [
                    'donation_id' => $donacion->id,
                    'donation_number' => $donacion->number,
                    'amount' => $donacion->amount,
                    'next_payment' => $donacion->next_payment,
                    'frequency' => $donacion->frequency,
                ]);

            } catch (Throwable $e) {
                $errors++;
                $this->error("❌ Error al encolar job para donación $donacion->id: {$e->getMessage()}");

                Log::error('Error al encolar job de pago', [
                    'donation_id' => $donacion->id,
                    'donation_number' => $donacion->number,
                    'error_message' => $e->getMessage(),
                    'error_class' => get_class($e),
                ]);
            }
        }

        if ($skipped > 0) {
            $this->warn("⚠️  Se omitieron $skipped donaciones sin identifier");
        }

        if ($errors > 0) {
            $this->warn("⚠️  Se encontraron $errors errores al encolar jobs");
        }

        return $processed;
    }
}
