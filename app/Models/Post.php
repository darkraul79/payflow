<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 364, 190)
            ->nonQueued();
    }

    public function getFormatDate(): string
    {
        return Str::apa(Carbon::parse($this->date)->translatedFormat('l d \d\e F'));
    }

    public function getFormatTime(): string
    {
        return Carbon::parse($this->date)->format('h:i \h\r\s.');
    }
}
