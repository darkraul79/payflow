<?php

namespace App\Models\Traits;


use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasPublishedField
{
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('published', true);
    }


}
