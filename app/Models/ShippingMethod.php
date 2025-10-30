<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static available()
 * @method static forAmount(float $param)
 * @method static active()
 * @property mixed $greater
 */
class ShippingMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'active',
        'from',
        'until',
        'greater',
    ];


    #[Scope]
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /*
     * Devuelve los métodos de envío que están activos por las fechas desde y hasta
     */

    #[Scope]
    public function scopeAvailable($query)
    {
        $today = now()->format('Y-m-d');

        return $query->whereDate('from', '<=', $today)->whereDate('until', '>=', $today)
            ->orWhere(function ($q) {
                $q->whereNull('from')->whereNull('until');
            });
    }

    /*
     * Devuelve los métodos de envío que están disponibles en la fecha actual
     */

    #[Scope]
    public function scopeForAmount($query, $amount)
    {
        return $query->active()
            ->available()
            ->where('greater', '<=', $amount)
            ->orWhereNull('greater');
    }

    /*
     * Devuelve los métodos de envío cuyo umbral 'greater' es nulo o menor o igual al total de la compra
     */

    public function getFormatedPrice(): string
    {

        return $this->price == 0 ? 'Gratis' : convertPrice($this->price);

    }

    public function isVisibleToday()
    {
        return $this->from <= now() && $this->until >= now();
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'from' => 'date',
            'until' => 'date',
        ];
    }
}
