<?php

namespace App\Models;

use App\Models\Traits\HasBreadcrumbs;
use App\Models\Traits\HasPublishedField;
use App\Models\Traits\HasTags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class News extends Model implements HasMedia
{
    use HasBreadcrumbs, HasFactory, HasPublishedField, HasTags, InteractsWithMedia, SoftDeletes;

    protected static array $parentsSlugs = [
        'que-hacemos',
        'noticias',
    ];

    protected $fillable = [
        'title',
        'slug',
        'content',
        'resume',
        'donacion',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'donacion' => 'boolean',
            'published' => 'boolean',
        ];
    }
}
