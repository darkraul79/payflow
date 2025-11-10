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

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return [

            'emailed_to' => 'array',
            'sent_at' => 'datetime',
        ];
    }
}
