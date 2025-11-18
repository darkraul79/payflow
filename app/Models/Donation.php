<?php

/** @noinspection PhpUnused */

namespace App\Models;

use App\Enums\DonationFrequency;
use App\Enums\DonationType;
use App\Enums\OrderStatus;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasPayments;
use App\Models\Traits\HasStates;
use Darkraul79\Payflow\Gateways\RedsysGateway;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method addresses()
 *
 * @property mixed $payments_sum_amount
 * @property mixed $totalRedsys
 *
 * @mixin IdeHelperDonation
 */
class Donation extends Model implements HasMedia
{
    use HasAddresses, HasFactory, HasPayments, HasStates, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'amount',
        'number',
        'info',
        'type',
        'identifier',
        'frequency',
        'next_payment',
        'updated_at',
        'created_at',
        'payment_method',
    ];

    protected $with = [
        'payments',
    ];

    /**
     * Obtiene el enum DonationFrequency desde el string
     */
    public function frequency(): ?DonationFrequency
    {
        return $this->frequency ? DonationFrequency::tryFrom($this->frequency) : null;
    }

    public function totalRedsys(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::replace('.', '', number_format($this->attributes['amount'], 2)),
        );
    }

    /**
     * Devuelve la vista de resultado del pedido según su estado.
     */
    public function getResultView(): string
    {
        if ($this->state->name === OrderStatus::ERROR->value) {
            return 'donation.error';
        } else {
            return 'donation.success';
        }
    }

    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    public function getStaticViewParams(): array
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        return [
            'page' => Page::factory()->make([
                'title' => 'Donación',
                'is_home' => false,
                'donation' => false,
                'parent_id' => Page::where('slug', 'tienda-solidaria')->first() ?? null,
            ]),
            'static' => true,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('invoices');
    }

    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'invoiceable')->latest();
    }

    public function payed(array $redSysResponse): void
    {

        $this->update([
            'identifier' => $this->type == DonationType::RECURRENTE->value ? ($redSysResponse['Ds_Merchant_Identifier'] ?? null) : null,
            'info' => $redSysResponse,
            'next_payment' => $this->type === DonationType::RECURRENTE->value ? $this->updateNextPaymentDate() : null,
        ]);

        $this->payments->where('number', $redSysResponse['Ds_Order'])->firstOrFail()->update(
            [
                'amount' => convert_amount_from_redsys($redSysResponse['Ds_Amount']),
                'info' => $redSysResponse,

            ]);

        // Si no existe el estado ACEPTADO ni CANCELADO, creo estado ACTIVA
        if ($this->type === DonationType::RECURRENTE->value) {
            if (! $this->states()->where('name', OrderStatus::CANCELADO->value)->exists() &&
                ! $this->states()->where('name', OrderStatus::ACTIVA->value)->exists()) {

                $this->states()->create([
                    'name' => OrderStatus::ACTIVA->value,
                ]);

            }
        } else {
            if (! $this->states()->where('name', OrderStatus::PAGADO->value)->exists()) {
                $this->states()->create([
                    'name' => OrderStatus::PAGADO->value,
                ]);

            }
        }

        $this->refresh();
    }

    public function updateNextPaymentDate(): string
    {
        $this->touch();
        $updated_at = $this->updated_at;
        $date = match ($this->frequency) {
            DonationFrequency::MENSUAL->value => Carbon::parse($updated_at)->addMonth()->day(5),
            DonationFrequency::TRIMESTRAL->value => Carbon::parse($updated_at)
                ->addMonths(3 - (Carbon::parse($updated_at)->month - 1) % 3)
                ->startOfMonth()
//                ->addMonths(2)
                ->day(5),
            DonationFrequency::ANUAL->value => Carbon::parse($updated_at)->addYear()->day(5),
            default => null,
        };
        $this->update([
            'next_payment' => $date->format('Y-m-d'),
        ]);

        return $this->next_payment;
    }

    public function iconType(): string
    {
        return match ($this->type) {
            DonationType::UNICA->value => 'bi-credit-card',
            DonationType::RECURRENTE->value => 'heroicon-o-arrow-path',
            default => 'bi-cash-stack',
        };
    }

    /**
     * Devuelve el icono asociado al estado de pedido.
     */
    public function colorType(): string
    {
        return match ($this->type) {
            DonationType::RECURRENTE->value => 'purple',
            default => 'primary',
        };
    }

    /**
     * Devuelve el icono asociado al estado de pedido.
     */
    public function colorFrequency(): string
    {
        return match ($this->frequency) {
            DonationFrequency::MENSUAL->value => 'Purple',
            DonationFrequency::TRIMESTRAL->value => 'lime',
            DonationFrequency::ANUAL->value => 'rose',
            default => 'primary',
        };
    }

    public function fechaHumanos(): string
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function recurrentPay()
    {
        if ($this->state->name == OrderStatus::ACTIVA->value) {
            return $this->processPay();
        }
        abort(403, 'La donación ya NO está activa y no se puede volver a pagar');
    }

    /**
     * Procesa un cobro automático (recurrente) vía Redsys.
     *
     * Crea registro Payment con amount=0, solicita el pago (REST),
     * valida firma y actualiza estado / siguiente cobro.
     *
     * En éxito: actualiza amount y deja info completa.
     * En error: marca estado ERROR y añade mensaje en info['error'].
     */
    public function processPay(): Payment
    {
        if ($this->type !== DonationType::RECURRENTE->value) {
            throw new RuntimeException('processPay solo aplicable a donaciones recurrentes.');
        }

        if (empty($this->identifier)) {
            // No se puede hacer cobro directo sin identificador inicial (alta previa OK).
            throw new RuntimeException('Donación recurrente sin identifier para cobro automático.');
        }

        $paymentNumber = generatePaymentNumber($this);

        $payment = $this->payments()->create([
            'number' => $paymentNumber,
            'amount' => 0,
            'info' => [],
        ]);

        $gateway = app(RedsysGateway::class);

        // Preparar parámetros (direct payment recurrente)
        $gateway->createPayment($this->amount, $paymentNumber, [
            'recurring' => [
                'identifier' => $this->identifier,
                'direct_payment' => 'true', // DS_MERCHANT_DIRECTPAYMENT
            ],
            'payment_method' => 'tarjeta',
        ]);

        // Enviar petición REST (throws si falla HTTP)
        $response = $gateway->sendRestPayment(); // Redsys devuelve estructura con parámetros / firma

        if (! isset($response['Ds_MerchantParameters'], $response['Ds_Signature'])) {
            throw new RuntimeException('Respuesta Redsys inválida en cobro recurrente.');
        }

        $merchantParameters = $response['Ds_MerchantParameters'];
        $signatureReceived = $response['Ds_Signature'];

        // Validación y decodificación unificadas
        $decodedData = $gateway->processCallback([
            'Ds_MerchantParameters' => $merchantParameters,
            'Ds_Signature' => $signatureReceived,
        ]);

        $decoded = $decodedData['decoded_data'] ?? [];
        $isValid = $decodedData['is_valid'] ?? false;

        $amount = 0.0;
        $info = $decoded;

        if ($isValid && $gateway->isSuccessful([
            'Ds_MerchantParameters' => $merchantParameters,
            'Ds_Signature' => $signatureReceived,
        ])) {
            $amount = RedsysGateway::convertAmountFromRedsys($decoded['Ds_Amount'] ?? '0');
        } else {
            $errorMessage = $gateway->getErrorMessage([
                'Ds_MerchantParameters' => $merchantParameters,
                'Ds_Signature' => $signatureReceived,
            ]);
            $info['error'] = $errorMessage;
            $this->error_pago($info, $errorMessage);
        }

        $payment->update([
            'info' => $info,
            'amount' => $amount,
        ]);

        // Mantengo lógica actual: se reprograma incluso en KO (tests lo esperan)
        $this->updateNextPaymentDate();

        return $payment;
    }

    public function error_pago($redSysResponse, $mensaje = null): void
    {
        $estado = [
            'name' => OrderStatus::ERROR->value,
        ];
        $this->update([
            'next_payment' => $this->type === DonationType::RECURRENTE->value ? $this->updateNextPaymentDate() : null,
        ]);

        if (! $this->states()->where($estado)->exists()) {

            $estado['info'] = $redSysResponse;
            $estado['info']['Error'] = $mensaje ?? 'Error al procesar el pedido';

            $this->states()->create($estado);

        }

        $this->refresh();

    }

    public function cancel(): void
    {
        $this->states()->create([
            'name' => OrderStatus::CANCELADO->value,
        ]);
        $this->update([
            'next_payment' => null,
        ]);
    }

    public function statesWithStateInitial(): Collection
    {
        // devuelvo los estados y agrega un estado de reccibido
        return $this->states;

    }

    /**
     *  Devuelve los estados disponibles de una donación, sin contar los ya asignados.
     */
    public function available_states(): array
    {
        $allowedStates = [
            OrderStatus::PAGADO->value,
            OrderStatus::ERROR->value,
            OrderStatus::ACEPTADO->value,
            OrderStatus::ACTIVA->value,
            OrderStatus::CANCELADO->value,
        ];

        return collect(self::getStates())
            ->filter(fn ($label, $key) => in_array($label, $allowedStates))
            ->toArray();
    }

    public function getNextPayDateFormated(): string
    {

        return $this->next_payment ? Carbon::parse($this->next_payment)->format('d-m-Y') : 'No definido';
    }

    public function getFormatedAmount(): string
    {
        return convertPrice($this->amount);
    }

    public function isRecurrente(): bool
    {
        return $this->type == DonationType::RECURRENTE->value;
    }

    /**
     * Devuelve el total del pedido formateado para Redsys.
     */
    protected function taxes(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->calculateTaxes(),
        );
    }

    /**
     * Calcula el IVA del pedido.
     *
     * @param  bool  $amountIncludesVat  Indica si `amount` ya incluye IVA (por defecto true).
     */
    public function calculateTaxes(bool $amountIncludesVat = true): float
    {
        $rate = $this->vatRate(); // p. ej. 0.21 para 21%

        if ($amountIncludesVat) {
            // Si amount es bruto (incluye IVA): IVA = total - (total / (1 + rate))
            return round($this->amount - ($this->amount / (1 + $rate)), 2);
        }

        // Si amount es neto (excluye IVA): IVA = neto * rate
        return round($this->amount * $rate, 2);
    }

    public function vatRate(): float
    {
        // Read default from settings, fallback to 21%
        $default = (float) (setting('billing.vat.donations_default', 21) ?? 21);

        return round($default / 100, 4);
    }

    protected function casts(): array
    {
        return [
            'info' => AsArrayObject::class,
            //            'next_payment' => 'date:Y-m-d',
        ];
    }

    /**
     * Devuelve las donaciones que tienen un cobro a realizar.
     */
    #[Scope]
    protected function nextPaymentsDonations(Builder $query)
    {
        return $query->recurrents()
            ->activas()
            ->whereDate(
                'next_payment', '<=', now()->format('Y-m-d'),
            );
    }

    /**
     * Delimita el listado de modelos a los modelos con estado ACTIVA.
     */
    #[Scope]
    protected function recurrents(Builder $query): void
    {

        $query->where('type', DonationType::RECURRENTE->value);

    }
}
