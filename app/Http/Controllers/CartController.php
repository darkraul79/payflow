<?php

namespace App\Http\Controllers;

use App\Models\Page;

class CartController extends Controller
{
    public array $params;

    public function index()
    {

        return view('cart.index',
            $this->getParams('Cesta')
        );
    }

    public function getParams(string $title): array
    {
        return [
            'page' => Page::factory()->make([
                'title' => $title,
                'is_home' => false,
                'donation' => false,
                'parent_id' => Page::where('slug', 'tienda-solidaria')->first() ?? null,
            ]),
            'static' => true,
        ];

    }

    public function form()
    {
        if (!session()->has('cart') || empty(session('cart'))) {
            return redirect()->route('cart');
        }

        return view('cart.form',
            $this->getParams('Detalles de facturación')
        );
    }

    public function finalizado()
    {
        $response = true;

        if ($response) {
            return view('cart.ok',
                $this->getParams('Pedido realizado')
            );
        }

        return view('cart.ko',
            $this->getParams('Error')
        );
    }
}
