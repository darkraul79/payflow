<?php /** @noinspection PhpUnused */

namespace App\Models;

use App\Models\Traits\HasBreadcrumbs;
use App\Models\Traits\HasPublishedField;
use App\Models\Traits\HasTags;
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
    use HasBreadcrumbs, HasFactory, HasPublishedField, HasTags, InteractsWithMedia, SoftDeletes;

    protected static array $parentsSlugs = [
        'que-hacemos',
        'actividades',
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('principal');

        $this->addMediaCollection('gallery');
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
}
