<?php

namespace App\Http\Controllers;

use App\Models\Page;

class FrontEndController extends Controller
{
    public function index()
    {
        $page = Page::with('children')->where('is_home', true)->first();

        return view('home', compact('page'));

    }
}
