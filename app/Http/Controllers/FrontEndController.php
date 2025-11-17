<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\News;
use App\Models\Page;
use App\Models\Product;
use App\Models\Proyect;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FrontEndController extends Controller
{
    /**
     * Display the home page
     */
    public function index(): View
    {
        $page = Page::with(['children.parent', 'parent.children'])->first();

        return view('home', compact('page'));
    }

    /**
     * Display a specific resource by slug
     */
    public function show(Request $request, string $slug): View
    {
        $type = $request->route('type');

        [$page, $view] = $this->getPageAndView($type, $slug);

        return view($view, compact('page'));
    }

    /**
     * Get the page model and view based on type
     */
    private function getPageAndView(string $type, string $slug): array
    {

        return match ($type) {
            'activity' => [
                Activity::where('slug', $slug)->firstOrFail(),
                'activities.show',
            ],
            'proyect' => [
                Proyect::where('slug', $slug)->firstOrFail(),
                'activities.show',
            ],
            'news' => [
                News::where('slug', $slug)->firstOrFail(),
                'activities.show',
            ],
            'product' => [
                Product::where('slug', $slug)->firstOrFail(),
                'products.show',
            ],
            default => throw new NotFoundHttpException('Resource type not found'),
        };
    }
}
