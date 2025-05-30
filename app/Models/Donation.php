<?php

namespace App\Models;

use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasPayments;
use App\Models\Traits\HasStates;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @method addresses()
 */
class Donation extends Model
{
    use HasFactory, SoftDeletes, HasAddresses, HasPayments, HasStates;

    public const UNICA = 'Simple';

    public const RECURRENTE = 'Recurrente';

    protected $fillable = [
        'amount',
        'number',
        'info',
        'type',
        'identifier',
    ];

    protected $with = [
        'payments',
    ];


    public function totalRedsys(): Attribute
    {
        return Attribute::make(
            get: fn() => Str::replace('.', '', number_format($this->attributes['amount'], 2)),
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
                'title' => 'Pedido',
                'is_home' => false,
                'donation' => false,
                'parent_id' => Page::where('slug', 'tienda-solidaria')->first() ?? null,
            ]),
            'static' => true,
        ];
    }

    public function payed(array $redSysResponse)
    {

        $this->update([
            'identifier' => $this->type == Donation::RECURRENTE ? $redSysResponse['Ds_Merchant_Identifier'] : null,
            'info' => $redSysResponse,
        ]);

        $this->payments->where('number', $redSysResponse['Ds_Order'])->firstOrFail()->update(
            [
                'amount' => convertPriceFromRedsys($redSysResponse['Ds_Amount']),
                'info' => $redSysResponse,

            ]);


        // Si no existe el estado PAGADO, lo creo
        if (!$this->states()->where('name', State::PAGADO)->exists()) {
            $this->states()->create([
                'name' => State::PAGADO,
            ]);

        }


        $this->refresh();
    }

    public function error($mensaje = null, $redSysResponse): void
    {
        $estado = [
            'name' => State::ERROR,
        ];

        if (!$this->states()->where($estado)->exists()) {

            $estado['info'] = $redSysResponse;
            $estado['info']['Error'] = $mensaje ?? 'Error al procesar el pedido';

            $this->states()->create($estado);

        }

        if ($this->type === Donation::RECURRENTE) {
            $this->payments()->create([
                'amount' => 0,
                'info' => $redSysResponse,
            ]);
        }

        $this->refresh();

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
     * Devuelve el icono asociado al estado de pedido
     */
    public function colorType(): string
    {
        return match ($this->type) {
            self::RECURRENTE => 'warning',
            self::UNICA => 'success',
            default => 'primary',
        };
    }

    public function fechaHumanos(): string
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    protected function casts(): array
    {
        return [
            'info' => AsArrayObject::class,
        ];
    }


}
