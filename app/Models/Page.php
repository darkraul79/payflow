<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Page extends \Z3d0X\FilamentFabricator\Models\Page implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'blocks',
        'is_home',
        'parent_id',
        'published_at',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }

    /**
     * Scope a query to only include popular users.
     */
    #[Scope]
    protected function firstLevel(Builder $query): void
    {
        $query->where('parent_id', null);
    }

    /**
     * Scope a query to only include popular users.
     */
    #[Scope]
    protected function isHome(Builder $query): void
    {
        $query->where('is_home', true);
    }


    /**
     * Scope a query to only include popular users.
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('published_at', '<>', null);
    }

}
