<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use ReflectionClass;

/**
 * @property mixed $items
 * @property mixed $total
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'shipping',
        'shipping_cost',
        'subtotal',
        'total',
        'taxes',
        'payment_method',
    ];

    public static function getStates(): array
    {
        $reflector = new ReflectionClass(OrderState::class);
        $constants = $reflector->getConstants();

        unset($constants['CREATED_AT']);
        unset($constants['UPDATED_AT']);

        return $constants;
    }

    public function state(): HasOne
    {
        return $this->hasOne(OrderState::class)->latestOfMany();
    }

    public function states(): HasMany
    {
        return $this->hasMany(OrderState::class);
    }

    public function address(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->addresses()->where('type', OrderAddress::BILLING)->first();
            }
        );
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function billing_adress()
    {
        return $this->addresses()->where('type', OrderAddress::BILLING)->first();
    }

    public function shipping_adress()
    {
        return $this->addresses()->where('type', OrderAddress::SHIPPING)->first();
    }

    public function Items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
