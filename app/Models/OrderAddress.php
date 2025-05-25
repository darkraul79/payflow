<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{
    use HasFactory;

    public const BILLING = 'facturación';

    public const SHIPPING = 'envío';

    protected $fillable = [
        'type',
        'name',
        'last_name',
        'company',
        'nif',
        'address',
        'order_id',
        'province',
        'city',
        'cp',
        'email',
        'phone',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->name.' '.$this->last_name;
    }
}
