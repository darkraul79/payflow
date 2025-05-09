<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];


    /**
     * Get all of the videos that are assigned this tag.
     */
    public function activities(): MorphToMany
    {
        return $this->morphedByMany(Activity::class, 'taggable');
    }
}
