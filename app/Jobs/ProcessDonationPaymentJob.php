<?php

namespace App\Jobs;

use App\Enums\DonationType;
use App\Enums\OrderStatus;
use App\Models\Donation;
use App\Support\SnapshotHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ProcessDonationPaymentJob implements ShouldQueue
{
    use Queueable;

    public int $donationId;

    public int $tries = 3;

    public int $timeout = 120;

    public string $stateName;

    public string $donationType;

    public ?string $identifier;

    public ?string $nextPayment;

    public function __construct(Donation $donation)
    {
        // Capturamos snapshot de los datos críticos en el momento del dispatch
        $snapshot = SnapshotHelper::fromDonation($donation);

        $this->donationId = $snapshot['id'];
        $this->stateName = $snapshot['stateName'] ?? OrderStatus::PENDIENTE->value;
        $this->donationType = $snapshot['type'];
        $this->identifier = $snapshot['identifier'];
        $this->nextPayment = $snapshot['nextPayment'];

        $this->onQueue('payments');
    }

    public function backoff(): array
    {
        return [30, 60, 120]; // 30s, 1min, 2min
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping($this->donationId)];
    }

    public function handle(): void
    {
        try {
            Log::info('Iniciando procesamiento de pago recurrente', [
                'donation_id' => $this->donationId,
                'attempt' => $this->attempts(),
                'state_snapshot' => $this->stateName,
            ]);

            // Recargamos la donación para procesar el pago con datos frescos
            $donation = Donation::findOrFail($this->donationId);

            if (! $this->isDonationValid()) {
                Log::warning('Donación no válida para procesar pago', [
                    'donation_id' => $this->donationId,
                    'type_snapshot' => $this->donationType,
                    'state_snapshot' => $this->stateName,
                    'identifier_snapshot' => $this->identifier,
                    'next_payment_snapshot' => $this->nextPayment,
                    'now' => now()->format('Y-m-d'),
                    'is_valid_check' => [
                        'type_is_recurrente' => $this->donationType === DonationType::RECURRENTE->value,
                        'state_is_active_or_pending' => in_array($this->stateName,
                            [OrderStatus::ACTIVA->value, OrderStatus::PENDIENTE->value]),
                        'has_identifier' => ! empty($this->identifier),
                        'next_payment_is_due' => $this->nextPayment <= now()->format('Y-m-d'),
                    ],
                ]);

                return;
            }

            $payment = $donation->processPay();

            Log::info('Pago recurrente procesado exitosamente', [
                'donation_id' => $this->donationId,
                'payment_id' => $payment->id,
                'amount_processed' => $payment->amount,
                'success' => $payment->amount > 0,
            ]);

        } catch (RuntimeException $e) {
            Log::error('Error de lógica de negocio al procesar pago recurrente', [
                'donation_id' => $this->donationId,
                'error_message' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error inesperado al procesar pago recurrente', [
                'donation_id' => $this->donationId,
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);
            throw $e;
        }
    }

    private function isDonationValid(): bool
    {
        // Usamos la snapshot capturada en el constructor para validar
        // Esto evita que cambios de estado posteriores al dispatch invaliden el job
        return $this->donationType === DonationType::RECURRENTE->value
            && in_array($this->stateName, [OrderStatus::ACTIVA->value, OrderStatus::PENDIENTE->value])
            && ! empty($this->identifier)
            && $this->nextPayment <= now()->format('Y-m-d');
    }

    public function failed(?Throwable $exception = null): void
    {
        Log::error('Job de pago recurrente falló definitivamente', [
            'donation_id' => $this->donationId,
            'error_message' => $exception?->getMessage(),
        ]);

        try {
            $donation = Donation::find($this->donationId);

            if (! $donation) {
                Log::error('No se pudo encontrar donación para marcar como error', [
                    'donation_id' => $this->donationId,
                ]);

                return;
            }

            // Obtenemos el estado actual desde la DB
            $currentState = $donation->states()->orderBy('id', 'desc')->first();

            if ($currentState && $currentState->name !== OrderStatus::ERROR->value) {
                $donation->error_pago([
                    'job_error' => true,
                    'failed_attempts' => $this->tries,
                    'state_at_dispatch' => $this->stateName,
                ], 'Error crítico en procesamiento automático de pago tras múltiples intentos');
            }
        } catch (Throwable $e) {
            Log::error('No se pudo marcar donación como error tras fallo del job', [
                'donation_id' => $this->donationId,
                'marking_error' => $e->getMessage(),
            ]);
        }
    }
}
