<?php

namespace App\Http\Controllers;

use App\Models\Page;

class FrontEndController extends Controller
{
    public function index()
    {

        $page = Page::with(['children.parent', 'parent.children'])->first();

        return view('home', compact('page'));

    }
}
