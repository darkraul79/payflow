<?php

namespace App\Http\Controllers;

use App\Helpers\RedsysAPI;
use App\Models\Order;
use App\Models\Page;
use App\Models\State;

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

    public function orderOK()
    {
        if (app()->isLocal() || app()->environment('testing')) {// En local obtengo la actualización de Redsys por parámetros
            $this->responseNotification();
        }

        return view('cart.ok', $this->getParams('Pedido'));

    }


    public function orderKO()
    {
        if (app()->isLocal() || app()->environment('testing')) { // En local obtengo la actualización de Redsys por parámetros
            $this->responseNotification();
        }

        return view('cart.ko', $this->getParams('Pedido'));
    }

    public function pagar_pedido(Order $pedido)
    {
        if ($pedido->state->name != State::PENDIENTE) {
            abort(404);
        }

        $redSys = new RedsysAPI;
        $data = $redSys->getFormDirectPay($pedido);

        return view('frontend.pagar-pedido', compact('data'));
    }


}
