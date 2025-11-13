<?php

namespace App\Models\Traits;

use App\Models\Address;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasAddresses
{
    public function address(): Attribute
    {
        return match (class_basename($this)) {
            'App\Models\Order' => Attribute::make(
                get: function () {
                    return $this->addresses()->where('type', Address::BILLING)->first();
                }),
            'App\Models\Donation' => Attribute::make(
                get: function () {
                    return $this->addresses()->where('type', Address::CERTIFICATE)->first();
                }),
            default => Attribute::make(
                get: function () {
                    return $this->addresses()->first();
                }
            ),
        };

    }

    public function addresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'addressable');
    }

    public function certificate(): Address|bool
    {
        return $this->addresses()->where('type', Address::CERTIFICATE)->first() ?? false;
    }
}
