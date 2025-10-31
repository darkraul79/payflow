<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed $amount
 * @property mixed $info
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'amount',
        'number',
        'info',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }


    protected function casts(): array
    {
        return [
            'info' => AsArrayObject::class,
        ];
    }


}
