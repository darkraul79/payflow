<?php

namespace App\Models;

use App\Models\Traits\HasBlockQuotes;
use App\Models\Traits\HasBreadcrumbs;
use App\Models\Traits\HasTags;
use App\Models\Traits\WithCommonAttributes;
use Datlechin\FilamentMenuBuilder\Concerns\HasMenuPanel;
use Datlechin\FilamentMenuBuilder\Contracts\MenuPanelable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Post
 *
 * @mixin Builder
 */
class Page extends \Z3d0X\FilamentFabricator\Models\Page implements HasMedia, MenuPanelable
{
    use HasBlockQuotes, HasBreadcrumbs, HasFactory, HasMenuPanel, HasTags, InteractsWithMedia, SoftDeletes, WithCommonAttributes;

    protected static array $parentsSlugs = [
    ];

    public $guarded = [];

    protected $fillable = [
        'title',
        'slug',
        'blocks',
        'layout',
        'is_home',
        'parent_id',
        'published_at',
    ];

    protected $with = ['parent', 'blockquotes', 'tags'];

    public function getMenuPanelTitleColumn(): string
    {
        return 'title';
    }

    public function getMenuPanelUrlUsing(): callable
    {
        return fn (self $model) => $model->getLink();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }

    #[Scope]
    protected function firstLevel(Builder $query): void
    {
        $query->where('parent_id', null);
    }

    //    #[Scope]
    //    protected function isHome(Builder $query): void
    //    {
    //        $query->where('is_home', true);
    //    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('published_at', '<>', null);
    }
}
