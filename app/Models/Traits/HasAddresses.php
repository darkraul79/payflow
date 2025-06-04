<?php

namespace App\Models\Traits;


use App\Models\Address;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasAddresses
{
    public function address(): Attribute
    {
        switch (class_basename($this)) {
            case 'App\Models\Order':
                return Attribute::make(
                    get: function () {
                        return $this->addresses()->where('type', Address::BILLING)->first();
                    });
            case 'App\Models\Donation':
                return Attribute::make(
                    get: function () {
                        return $this->addresses()->where('type', Address::CERTIFICATE)->first();
                    });
            default:
                return Attribute::make(
                    get: function () {
                        return $this->addresses()->first();
                    }
                );
        }


    }


    public function addresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'addressable');
    }

    public function certificate(): Address
    {
        return $this->addresses()->where('type', Address::CERTIFICATE)->first();
    }


}
