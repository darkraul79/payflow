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

class Activity extends Model implements HasMedia
{
    use HasBlockQuotes, HasBreadcrumbs, HasFactory, HasPublishedField, HasTags, InteractsWithMedia, SoftDeletes, WithCommonAttributes;

    protected static array $parentsSlugs = [
        [
            'url' => '/actualidad/actividades',
            'title' => 'Actividades',
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
        'date',
        'address',
        'resume',
        'published',
        'donacion',
    ];

    public static function getFooterActivities()
    {
        return Activity::query()
            ->published()
            ->orderBy('date', 'desc')
            ->limit(6)
            ->get();
    }

    public function getDateCalendar(): string
    {
        return Carbon::parse($this->date)->format('Y-m-d');
    }

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

    public function getFormatDate(): string
    {
        return Str::apa(Carbon::parse($this->date)->translatedFormat('l d \d\e F'));
    }

    public function getFormatDateBlog(): string
    {
        return Str::apa(Carbon::parse($this->date)->translatedFormat('F d, Y'));
    }

    public function getFormatDateTime(): string
    {
        return Carbon::parse($this->date)->format('h:i \h\r\s.');
    }

    /**
     * Devuelve el resumen de la noticia,
     * si no existe generla los últimos 200 char del contenido.
     */
    public function getResume(): string
    {
        return $this->resume ?? Str::limit(strip_tags($this->content), 200);
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
