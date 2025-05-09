<?php

namespace App\Models\Traits;

use App\Models\Page;
use Illuminate\Support\Collection;

trait HasBreadcrumbs
{
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];

        foreach ($this->getParents() as $parent) {
            $breadcrumbs[$parent->title] = $this->getUrl();
        }

        return $breadcrumbs;
    }

    public function getParents(): Collection
    {

        $parents = collect();

        foreach (self::$parentsSlugs as $parentSlug) {
            $parents->push(Page::query()->where('slug', $parentSlug)->first() ?? Page::make(['slug' => $parentSlug]));
        }

        return $parents;
    }

    public function getUrl(): string
    {
        return config('app.url').$this->getUrlPrefix().'/'.$this->slug;
    }

    public function getUrlPrefix(bool $completa = false): string
    {
        // si estoy ejecutando test creo un slug manualmente
        if (app()->runningUnitTests()) {
            return ($completa ? config('app.url') : '').implode('/', self::$parentsSlugs);
        }

        $url = '';

        foreach ($this->getParents() as $parent) {
            $url .= $parent?->slug ? '/'.$parent->slug : '';
        }

        return $completa ? config('app.url').$url : $url;
    }

    public function getUrlComplete(): string
    {
        return $this->getUrlPrefix().$this->slug;
    }
}
