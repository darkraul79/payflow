<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumbs extends Component
{
    public function __construct(
        public $page,
        public $post
    )
    {
    }

    public function render(): View
    {

        return view('components.breadcrumbs', [
            'breadcrumbs' => $this->getBreadcrumbs(),
        ]);
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $breadcrumbs['Home'] = route('home');

        if ($this->page) {
            if ($this->page->parent) {
                $parent = $this->page->parent;
                $breadcrumbs[$parent->title] = $parent->getUrl();
            }

            $breadcrumbs[$this->page->title] = $this->page->getUrl();
        }
        if ($this->post) {
            $breadcrumbs = array_merge($breadcrumbs, $this->post->getBreadcrumbs());

            $breadcrumbs[$this->post->title] = $this->post->getUrl();
        }

        return $breadcrumbs;

    }
}
