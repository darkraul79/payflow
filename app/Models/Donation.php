<?php

/** @noinspection PhpUnused */

namespace App\Models;

use App\Models\Invoice;

use App\Helpers\RedsysAPI;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasPayments;
use App\Models\Traits\HasStates;
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
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method addresses()
 *
 * @property mixed $payments_sum_amount
 */
class Donation extends Model implements HasMedia
{
    use HasAddresses, HasFactory, HasPayments, HasStates, SoftDeletes, InteractsWithMedia;

    public const string UNICA = 'Simple';

    public const string RECURRENTE = 'Recurrente';

    public const array FREQUENCY = [
        'MENSUAL' => 'Mensual',
        'TRIMESTRAL' => 'Trimestral',
        'ANUAL' => 'Anual',
    ];

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
    ];

    protected $with = [
        'payments',
    ];

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
        if ($this->state->name === State::ERROR) {
            return 'donation.ko';
        } else {
            return 'donation.ok';
        }
    }

    public function getStaticViewParams(): array
    {
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

    public function vatRate(): float
    {
        $default = (float) (setting('billing.vat.donations_default', 0) ?? 0);
        return round($default / 100, 4);
    }

    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'invoiceable')->latest('created_at');
    }

    public function payed(array $redSysResponse): void
    {

        $this->update([
            'identifier' => $this->type == Donation::RECURRENTE ? $redSysResponse['Ds_Merchant_Identifier'] : null,
            'info' => $redSysResponse,
            'next_payment' => $this->type === Donation::RECURRENTE ? $this->updateNextPaymentDate() : null,
        ]);

        $this->payments->where('number', $redSysResponse['Ds_Order'])->firstOrFail()->update(
            [
                'amount' => convertPriceFromRedsys($redSysResponse['Ds_Amount']),
                'info' => $redSysResponse,

            ]);

        // Si no existe el estado ACEPTADO ni CANCELADO, creo estado ACTIVA
        if ($this->type === Donation::RECURRENTE) {
            if (! $this->states()->where('name', State::CANCELADO)->exists() &&
                ! $this->states()->where('name', State::ACTIVA)->exists()) {

                $this->states()->create([
                    'name' => State::ACTIVA,
                ]);

            }
        } else {
            if (! $this->states()->where('name', State::PAGADO)->exists()) {
                $this->states()->create([
                    'name' => State::PAGADO,
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
            self::FREQUENCY['MENSUAL'] => Carbon::parse($updated_at)->addMonth()->day(5),
            self::FREQUENCY['TRIMESTRAL'] => Carbon::parse($updated_at)
                ->addMonths(3 - (Carbon::parse($updated_at)->month - 1) % 3)
                ->startOfMonth()
//                ->addMonths(2)
                ->day(5),
            self::FREQUENCY['ANUAL'] => Carbon::parse($updated_at)->addYear()->day(5),
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
            self::UNICA => 'bi-credit-card',
            self::RECURRENTE => 'heroicon-o-arrow-path',
            default => 'bi-cash-stack',
        };
    }

    /**
     * Devuelve el icono asociado al estado de pedido.
     */
    public function colorType(): string
    {
        return match ($this->type) {
            self::RECURRENTE => 'purple',
            default => 'primary',
        };
    }

    /**
     * Devuelve el icono asociado al estado de pedido.
     */
    public function colorFrequency(): string
    {
        return match ($this->frequency) {
            self::FREQUENCY['MENSUAL'] => 'Purple',
            self::FREQUENCY['TRIMESTRAL'] => 'lime',
            self::FREQUENCY['ANUAL'] => 'rose',
            default => 'primary',
        };
    }

    public function fechaHumanos(): string
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function recurrentPay()
    {
        if ($this->state->name == State::ACTIVA) {
            return $this->processPay();
        }
        abort(403, 'La donación ya NO está activa y no se puede volver a pagar');
    }

    public function processPay(): Payment
    {
        $number = generatePaymentNumber($this);

        $pago = $this->payments()->create([
            'number' => $number,
            'amount' => 0,
            'info' => [],
        ]);

        $redsys = new RedsysAPI;

        $redsys->getFormPagoAutomatico($this, $number);
        $response = $redsys->send();

        $response = json_decode($response, true);
        $datos = $response['Ds_MerchantParameters'];
        $signatureRecibida = $response['Ds_Signature'];

        if (empty($datos) || empty($signatureRecibida)) {
            abort(404, 'Datos de Redsys no recibidos');
        }

        $decodec = json_decode($redsys->decodeMerchantParameters($datos), true);
        $firma = $redsys->createMerchantSignatureNotif(config('redsys.key'), $datos);

        $cantidad = 0;
        $info = $decodec;

        if ($redsys->checkSignature($firma, $signatureRecibida) && intval($decodec['Ds_Response']) <= 99) {

            $cantidad = convertPriceFromRedsys($decodec['Ds_Amount']);

        } else {
            $error = hash_equals($firma, $signatureRecibida)
                ? estado_redsys($decodec['Ds_Response'])
                : 'Firma no válida';
            $info['error'] = $error;

            // ESTABLEZCO ESTADO DE ERROR
            $this->error_pago($info, $error);

        }
        $pago->update([
            'info' => $info,
            'amount' => $cantidad,
        ]);

        $this->updateNextPaymentDate();

        return $pago;
    }

    public function error_pago($redSysResponse, $mensaje = null): void
    {
        $estado = [
            'name' => State::ERROR,
        ];
        $this->update([
            'next_payment' => $this->type === Donation::RECURRENTE ? $this->updateNextPaymentDate() : null,
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
            'name' => State::CANCELADO,
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
     *  Devuelve los estados disponibles de un pedido, sin contar los ya asignados.
     */
    public function available_states(): array
    {

        $estados = collect(self::getStates());

        return $estados->only(['PAGADO', 'ERROR', 'ACEPTADO', 'ACTIVA', 'CANCELADO'])->toArray();
    }

    public function getNextPayDateFormated(): string
    {

        return Carbon::parse($this->next_payment)->format('d-m-Y');
    }

    public function getFormatedAmount(): string
    {
        return convertPrice($this->amount);
    }

    public function getNextPaymentFormated(): string
    {
        return Carbon::parse($this->next_payment)->format('d-m-Y');
    }

    public function isRecurrente(): bool
    {
        return $this->type == self::RECURRENTE;
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

        $query->where('type', self::RECURRENTE);

    }
}
