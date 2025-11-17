<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Helpers\RedsysAPI;
use App\Models\Order;
use App\Models\Page;

class CartController extends Controller
{
    private const string SHOP_SLUG = 'tienda-solidaria';

    /**
     * Display the shopping cart
     */
    public function index()
    {
        return view('cart.index', $this->getParams('Cesta'));
    }

    /**
     * Get common parameters for cart pages
     */
    private function getParams(string $title): array
    {
        return [
            'page' => Page::factory()->make([
                'title' => $title,
                'is_home' => false,
                'donation' => false,
                'parent_id' => Page::where('slug', self::SHOP_SLUG)->first() ?? null,
            ]),
            'static' => true,
        ];
    }

    /**
     * Show the checkout form
     */
    public function create()
    {
        if (! session()->has('cart') || empty(session('cart'))) {
            return redirect()->route('cart');
        }

        return view('cart.form', $this->getParams('Detalles de facturación'));
    }

    /**
     * Show a payment form for an order
     */
    public function show(Order $pedido)
    {
        if ($pedido->state->name != OrderStatus::PENDIENTE->value) {
            abort(404);
        }

        $redSys = new RedsysAPI;
        $data = $redSys->getFormDirectPay($pedido);

        return view('frontend.pagar-pedido', compact('data'));
    }
}
