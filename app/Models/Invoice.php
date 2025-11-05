<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Invoice extends Model
{
    protected $fillable = [
        'invoiceable_type',
        'invoiceable_id',
        'series',
        'year',
        'sequence',
        'number',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'total',
        'currency',
        'storage_path',
        'sent_at',
        'emailed_to',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'vat_rate' => 'decimal:4',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'emailed_to' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }
}
