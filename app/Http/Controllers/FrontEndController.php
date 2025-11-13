<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\News;
use App\Models\Page;
use App\Models\Product;
use App\Models\Proyect;

class FrontEndController extends Controller
{
    public function index()
    {

        $page = Page::with(['children.parent', 'parent.children'])->first();

        return view('home', compact('page'));

    }

    public function activities($slug)
    {
        $page = Activity::where('slug', $slug)->first();

        return view('activities.show', compact('page'));
    }

    public function proyects($slug)
    {
        $page = Proyect::where('slug', $slug)->first();

        return view('activities.show', compact('page'));
    }

    public function news($slug)
    {
        $page = News::where('slug', $slug)->first();

        return view('activities.show', compact('page'));
    }

    public function products($slug)
    {
        $page = Product::where('slug', $slug)->first();

        return view('products.show', compact('page'));
    }

    private function getType($class)
    {
        return class_basename($class);
    }
}
