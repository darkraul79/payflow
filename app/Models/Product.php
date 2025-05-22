<?php

namespace App\Models;

use App\Models\Traits\HasBreadcrumbs;
use App\Models\Traits\HasTags;
use App\Models\Traits\WithCommonAttributes;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasBreadcrumbs, HasFactory, HasTags, InteractsWithMedia, SoftDeletes, WithCommonAttributes;

    protected static array $parentsSlugs = [
        [
            'url' => '/tienda-solidaria',
            'title' => 'Tienda solidaria',
        ]
    ];


    protected $fillable = [
        'name',
        'slug',
        'price',
        'stock',
        'description',
        'offer_price',
        'published',
        'donacion'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_images');

    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('gallery')
            ->fit(Fit::Crop, 600, 600)
            ->withResponsiveImages()
            ->quality(90)
            ->nonQueued();
        $this
            ->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->withResponsiveImages()
            ->quality(90)
            ->nonQueued();
    }

    public function getFormatedPriceWithDiscount($inverse = false): string
    {
        $precio_original = $this->getFormatedPrice();

        if ($this->offer_price) {
            return convertPrice($this->offer_price) . ' <span class="text-xs font-light text-gray-400 line-through mx-1">' . $precio_original . '</span>';
        }

        return $precio_original;
    }

    public function getFormatedPrice(): string
    {
        return convertPrice($this->price);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function getPrice()
    {
        if ($this->offer_price) {
            return $this->offer_price;
        }
        return $this->price;
    }

    protected function blockquotes(): Attribute
    {
        return Attribute::make(
            get: fn() => collect([]),
        );
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->name,
        );
    }

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
        ];
    }

    #[Scope]
    protected function next_activities(Builder $query): void
    {
        $query->where('date', '>=', now())->published()
            ->orderBy('date', 'desc');
    }

    #[Scope]
    protected function latest_activities(Builder $query): void
    {
        $query->where('date', '>=', now())->published()
            ->orderBy('date', 'asc');
    }

    #[Scope]
    protected function manual(Builder $query, array $ids): void
    {
        $query->published()
            ->whereIn('id', $ids)
            ->orderBy('date', 'desc');
    }

    #[Scope]
    protected function all_activities(Builder $query): void
    {
        $query->published()
            ->orderBy('date', 'desc');
    }
}
