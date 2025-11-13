<?php

/** @noinspection PhpUnused */

namespace App\Models;

use App\Models\Traits\HasBlockQuotes;
use App\Models\Traits\HasBreadcrumbs;
use App\Models\Traits\HasPublishedField;
use App\Models\Traits\HasTags;
use App\Models\Traits\WithCommonAttributes;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class News extends Model implements HasMedia
{
    use HasBlockQuotes, HasBreadcrumbs, HasFactory, HasPublishedField, HasTags, InteractsWithMedia, SoftDeletes, WithCommonAttributes;

    protected static array $parentsSlugs = [
        [
            'url' => '/actualidad/noticias',
            'title' => 'Noticias',
        ],
        [
            'url' => '/actualidad',
            'title' => 'Actualidad',
        ],
    ];

    protected $fillable = [
        'title',
        'slug',
        'content',
        'resume',
        'donacion',
        'published',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('principal');

        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('card-thumb')
            ->fit(Fit::Crop, 364, 190)
            ->withResponsiveImages()
            ->quality(90)
            ->nonQueued();
        $this
            ->addMediaConversion('activity-title')
            ->fit(Fit::Contain, 795, 530)
            ->withResponsiveImages()
            ->quality(90)
            ->nonQueued();
        $this
            ->addMediaConversion('thumb')
            ->quality(90)
            ->fit(Fit::Crop, 300, 200)
            ->nonQueued();
    }

    public function getFormatDateBlog(): string
    {
        return Str::apa(Carbon::parse($this->updated_at)->translatedFormat('F d, Y'));
    }

    /**
     * Devuelve el resumen de la noticia,
     * si no existe generla los últimos 200 char del contenido.
     */
    public function getResume(): string
    {
        return $this->resume ?? Str::limit(strip_tags($this->content), 200);
    }

    protected function casts(): array
    {
        return [
            'donacion' => 'boolean',
            'published' => 'boolean',
        ];
    }

    #[Scope]
    protected function next_activities(Builder $query): void
    {
        $query->published()
            ->orderBy('created_at', 'desc');
    }

    #[Scope]
    protected function latest_activities(Builder $query): void
    {
        $query->published()
            ->orderBy('updated_at', 'desc');
    }

    #[Scope]
    protected function manual(Builder $query, array $ids): void
    {
        $query->published()
            ->whereIn('id', $ids)
            ->orderBy('updated_at', 'desc');
    }

    #[Scope]
    protected function all_activities(Builder $query): void
    {
        $query->published()
            ->orderBy('updated_at', 'desc');
    }
}
