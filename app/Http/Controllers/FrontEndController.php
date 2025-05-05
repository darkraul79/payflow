<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\News;
use App\Models\Page;
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
        $post = Activity::where('slug', $slug)->first();
        $page = false;

        return view('activities.show', compact('post', 'page'));
    }

    public function proyects($slug)
    {
        $post = Proyect::where('slug', $slug)->first();
        $page = false;

        return view('activities.show', compact('post', 'page'));
    }

    public function news($slug)
    {
        $post = News::where('slug', $slug)->first();
        $page = false;

        return view('activities.show', compact('post', 'page'));
    }
}
