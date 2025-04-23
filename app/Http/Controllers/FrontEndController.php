<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;

class FrontEndController extends Controller
{
    public function index()
    {

        $page = Page::with(['children.parent', 'parent.children'])->first();

        return view('home', compact('page'));

    }

    public function activities($slug)
    {
        $post = Post::where('slug', $slug)->first();
        $page = false;

//        dd($post->getMedia());

        return view('activities.show', compact('post', 'page'));
    }
}
