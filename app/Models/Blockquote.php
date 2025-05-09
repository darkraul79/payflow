<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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

    /**
     * Get all the pages that are assigned this BlockQuote.
     */
    public function pages(): MorphToMany
    {
        return $this->morphedByMany(Page::class, 'blockquoteable');
    }


}
