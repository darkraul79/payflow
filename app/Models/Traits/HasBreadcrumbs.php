<?php

namespace App\Models\Traits;

use App\Models\Page;
use Illuminate\Support\Collection;

trait HasBreadcrumbs
{
    public static function getStaticUrlPrefix(): string
    {
        return collect(self::$parentsSlugs)->first()['url'] ?? '';
    }

    public function getBreadcrumbs(): array
    {
        $arr = [];
        if (class_basename($this) == 'Page') {
            if ($this->parent_id) {
                $arr[] = [
                    'url' => $this->parent->getLink(),
                    'title' => $this->parent->title,
                ];
                $arr[] = [
                    'url' => $arr[0]['url'] . '/' . $this->slug,
                    'title' => $this->title,
                ];
            } else {
                $arr[] = [
                    'url' => '/' . $this->slug,
                    'title' => $this->title,
                ];
            }

            $arr = collect($arr);
        } else {

            $arr = collect(self::$parentsSlugs)->reverse();
            $arr->push([
                'url' => $arr->first()['url'] . '/' . $this->slug,
                'title' => $this->title,
            ]);
        }

        return $arr->toArray();
    }

    public function getLink(): ?string
    {
        if (collect(self::$parentsSlugs)->first()) {
            return collect(self::$parentsSlugs)->first()['url'] . '/' . $this->slug;
        }
        if (class_basename($this) == 'Page') {
            if ($this->parent_id) {
                return $this->parent->getLink() . '/' . $this->slug;
            }
        }

        return '/' . $this->slug;
    }

    public function getParentsFromMenu()
    {

        $parents = self::$parentsSlugs;

        return $parents;

    }

    public function getUrlFromSlug(): string
    {
        return config('app.url') . $this->getUrlPrefix() . '/' . $this->slug;
    }

    public function getUrlPrefix(bool $completa = false): string
    {
        // si estoy ejecutando test creo un slug manualmente
        if (app()->runningUnitTests()) {
            return ($completa ? config('app.url') : '/') . implode('/', self::$parentsSlugs);
        }

        $url = '';

        foreach ($this->getParents() as $parent) {
            $url .= $parent?->slug ? '/' . $parent->slug : '';
        }

        return $completa ? config('app.url') . $url : $url;
    }

    public function getParents(): Collection
    {

        $parents = collect();

        foreach (self::$parentsSlugs as $parentSlug) {
            $parents->push(Page::query()->where('slug', $parentSlug)->first() ?? Page::make(['slug' => $parentSlug]));
        }

        return $parents;
    }

    public function getUrlComplete(): string
    {
        return $this->getUrlPrefix() . $this->slug;
    }
}
