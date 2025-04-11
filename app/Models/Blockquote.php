<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blockquote extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
    ];

    public static function getRandom(): ?string
    {
        // Return a random item from database
        return Blockquote::inRandomOrder()->take(1)->first()?->text;

    }
}
