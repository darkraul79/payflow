<?php

namespace App\Models\Traits;

use App\Models\Blockquote;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasBlockQuotes
{
    /**
     * Get the BlockQuote.
     */
    public function blockquotes(): MorphToMany
    {
        return $this->morphToMany(Blockquote::class, 'blockquoteable')->latest();
    }
}
