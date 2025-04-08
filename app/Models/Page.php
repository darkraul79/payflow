<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Statikbe\FilamentFlexibleContentBlocks\Models\Concerns\HasPageAttributesTrait;

class Page extends Model
{
    use HasFactory, SoftDeletes, HasPageAttributesTrait;

    protected $fillable = [
        'title',
        'slug',
        'published_at',
    ];

    public function contents(): HasMany
    {
        return $this->hasMany(PageContent::class);

    }
}
