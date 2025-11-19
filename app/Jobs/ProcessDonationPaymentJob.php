<?php

namespace App\Jobs;

use App\Enums\DonationType;
use App\Enums\OrderStatus;
use App\Models\Donation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ProcessDonationPaymentJob implements ShouldQueue
{
    use Queueable;

    public Donation $donation;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
        $this->onQueue('payments');
    }

    public function backoff(): array
    {
        return [30, 60, 120]; // 30s, 1min, 2min
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping($this->donation->id)];
    }

    public function handle(): void
    {
        try {
            Log::info('Iniciando procesamiento de pago recurrente', [
                'donation_id' => $this->donation->id,
                'attempt' => $this->attempts(),
            ]);

            $this->donation->refresh();

            if (! $this->isDonationValid()) {
                Log::warning('Donación no válida para procesar pago', [
                    'donation_id' => $this->donation->id,
                    'type' => $this->donation->type,
                    'state' => $this->donation->state->name ?? 'unknown',
                    'identifier' => $this->donation->identifier ?? 'null',
                    'next_payment' => $this->donation->next_payment,
                    'now' => now()->format('Y-m-d'),
                    'is_valid_check' => [
                        'type_is_recurrente' => $this->donation->type === DonationType::RECURRENTE->value,
                        'state_is_active_or_pending' => in_array($this->donation->state->name ?? '',
                            [OrderStatus::ACTIVA->value, OrderStatus::PENDIENTE->value]),
                        'has_identifier' => ! empty($this->donation->identifier),
                        'next_payment_is_due' => $this->donation->next_payment <= now()->format('Y-m-d'),
                    ],
                ]);

                return;
            }

            $payment = $this->donation->processPay();

            Log::info('Pago recurrente procesado exitosamente', [
                'donation_id' => $this->donation->id,
                'payment_id' => $payment->id,
                'amount_processed' => $payment->amount,
                'success' => $payment->amount > 0,
            ]);

        } catch (RuntimeException $e) {
            Log::error('Error de lógica de negocio al procesar pago recurrente', [
                'donation_id' => $this->donation->id,
                'error_message' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error inesperado al procesar pago recurrente', [
                'donation_id' => $this->donation->id,
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);
            throw $e;
        }
    }

    private function isDonationValid(): bool
    {
        return $this->donation->type === DonationType::RECURRENTE->value
            && in_array($this->donation->state->name ?? '', [OrderStatus::ACTIVA->value, OrderStatus::PENDIENTE->value])
            && ! empty($this->donation->identifier)
            && $this->donation->next_payment <= now()->format('Y-m-d');
    }

    public function failed(?Throwable $exception = null): void
    {
        Log::error('Job de pago recurrente falló definitivamente', [
            'donation_id' => $this->donation->id,
            'error_message' => $exception?->getMessage(),
        ]);

        try {
            $this->donation->refresh();
            if ($this->donation->state->name !== OrderStatus::ERROR->value) {
                $this->donation->error_pago([
                    'job_error' => true,
                    'failed_attempts' => $this->tries,
                ], 'Error crítico en procesamiento automático de pago tras múltiples intentos');
            }
        } catch (Throwable $e) {
            Log::error('No se pudo marcar donación como error tras fallo del job', [
                'donation_id' => $this->donation->id,
                'marking_error' => $e->getMessage(),
            ]);
        }
    }
}
