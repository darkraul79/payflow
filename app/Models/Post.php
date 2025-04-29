<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property mixed $slug
 */
class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

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
        return Post::query()
            ->published()
            ->orderBy('date', 'desc')
            ->limit(6)
            ->get();
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('published', true);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('card-thumb')
            ->fit(Fit::Contain, 364, 190)
            ->nonQueued();
        $this
            ->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
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

    public function getFormatTime(): string
    {
        return Carbon::parse($this->date)->format('h:i \h\r\s.');
    }

    public function getUrl()
    {
        return $this->getUrlPrefix() . $this->slug;
    }

    public function getUrlPrefix()
    {
        return config('app.url') . '/que-hacemos/actividades/';
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(tag::class, 'post_tag', 'post_id', 'tag_id');
    }

    #[Scope]
    protected function next_activities(Builder $query): void
    {
        $query->where('date', '>=', now());
    }
}
