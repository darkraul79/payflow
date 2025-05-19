<?php

namespace App\Models\Traits;

use App\Models\Page;
use Datlechin\FilamentMenuBuilder\Models\MenuItem;
use Illuminate\Support\Collection;

trait HasBreadcrumbs
{
    public static function getStaticUrlPrefix(): string
    {
        $instance = new self; // Crear una instancia de la clase
        $parentsFromMenu = $instance->getParentsFromMenu(); // Llamar al método no estático

        if ($parentsFromMenu && $parentsFromMenu->isNotEmpty()) {
            return array_keys($parentsFromMenu->last())[0];
        }

        return collect(self::$parentsSlugs)->keys()->last();
    }

    public function getParentsFromMenu()
    {
        $parents = collect();
        switch (class_basename($this)) {
            case 'Activity':
                $item = $this->getMenuItems('Actividades');
                if (! $item) {
                    return false;
                }
                $parents->push([$item->url => $item->title]);
                if ($item->parent) {
                    $parents->push([$item->parent?->url => $item->parent?->title]);
                }
                $parents = $parents->reverse();
                break;
            case 'News':
                $item = $this->getMenuItems('Noticias');
                if (! $item) {
                    return false;
                }
                $parents->push([$item->url => $item->title]);
                if ($item->parent) {
                    $parents->push([$item->parent?->url => $item->parent?->title]);
                }
                $parents = $parents->reverse();
                break;
            case 'Proyect':

                $item = $this->getMenuItems('Proyectos');
                if (! $item) {
                    return false;
                }
                $parents->push([$item->url => $item->title]);
                if ($item->parent) {
                    $parents->push([$item->parent?->url => $item->parent?->title]);
                }
                $parents = $parents->reverse();

                break;
            case 'Product':
                $item = $this->getMenuItems('Tienda solidaria');
                if (! $item) {
                    return false;
                }
                $parents->push([$item->url => $item->title]);
                if ($item->parent) {
                    $parents->push([$item->parent?->url => $item->parent?->title]);
                }
                $parents = $parents->reverse();
                break;

            case 'Page':

                //                $parents->push([$this->getLink() => $this->title]);

                if ($this->parent) {
                    $parents->push([$this->parent?->getLink() => $this->parent?->title]);
                }
                $parents = $parents->reverse();

                break;
        }

        /*  foreach ($parents as $parentSlug) {
              $parents->push(Page::query()->where('slug', $parentSlug)->first() ?? Page::make(['slug' => $parentSlug]));
          }*/

        return $parents;

    }

    public function getMenuItems($url): mixed
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        return MenuItem::where('title', $url)->first() ?? false;
    }

    public function getLink()
    {

        switch (class_basename($this)) {
            case 'Activity':
                return route('activities.show', ['slug' => $this->slug]);
            case 'News':
                return route('news.show', ['slug' => $this->slug]);
            case 'Proyect':
                return route('proyects.show', ['slug' => $this->slug]);
            case 'Product':
                return route('products.show', ['slug' => $this->slug]);
        }

        return null;
    }

    public function getBreadcrumbs(): array
    {
        $array = $this->getParentsFromMenu()->collapse()->toArray();
        $array[] = $this->title;

        return $array;
    }

    public function getUrlFromSlug(): string
    {
        return config('app.url').$this->getUrlPrefix().'/'.$this->slug;
    }

    public function getUrlPrefix(bool $completa = false): string
    {
        // si estoy ejecutando test creo un slug manualmente
        if (app()->runningUnitTests()) {
            return ($completa ? config('app.url') : '/').implode('/', self::$parentsSlugs);
        }

        $url = '';

        foreach ($this->getParents() as $parent) {
            $url .= $parent?->slug ? '/'.$parent->slug : '';
        }

        return $completa ? config('app.url').$url : $url;
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
        return $this->getUrlPrefix().$this->slug;
    }
}
