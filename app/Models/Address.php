<?php

namespace App\Models;

use App\Enums\AddressType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperAddress
 */
class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'last_name',
        'last_name2',
        'company',
        'nif',
        'address',
        'province',
        'city',
        'cp',
        'email',
        'phone',
        'notes',
    ];

    /**
     * Obtiene el enum AddressType correspondiente al tipo actual
     */
    public function addressType(): ?AddressType
    {
        return AddressType::tryFrom($this->type);
    }

    public function getFullNameAttribute(): string
    {
        return $this->name.' '.$this->last_name.' '.$this->last_name2;
    }

    public function getFullAddress(): string
    {
        return $this->address.'.'.' '.$this->cp.' '.$this->city.' ('.$this->province.')';
    }
}
