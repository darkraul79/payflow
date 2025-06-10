<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    public const BILLING = 'Facturación';

    public const SHIPPING = 'Envío';

    public const CERTIFICATE = 'Certificado';

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

    public function getFullNameAttribute(): string
    {
        return $this->name.' '.$this->last_name.' '.$this->last_name2;
    }
}
