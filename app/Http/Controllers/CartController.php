<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Page;
use Darkraul79\Cartify\Facades\Cart;
use Darkraul79\Payflow\Facades\Gateway;

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
     *
     * @noinspection PhpDynamicAsStaticMethodCallInspection
     */
    private function getParams(string $title): array
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
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
        // Usar Cart facade y verificar presencia de items y totales
        if (Cart::isEmpty() || ! session()->has('cart.totals.subtotal')) {
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

        // Usar Payflow Gateway en lugar de RedsysAPI
        $payment = Gateway::withRedsys()->createPayment(
            amount: $pedido->amount,
            orderId: $pedido->number,
            options: [
                'url_ok' => route('pedido.response'),
                'url_ko' => route('pedido.response'),
                'url_notification' => route('pedido.response'),
            ]
        );

        // La estructura de $payment ahora incluye:
        // - Ds_MerchantParameters
        // - Ds_Signature
        // - Ds_SignatureVersion
        // - form_url (URL del formulario de Redsys)
        $data = $payment;

        return view('frontend.pagar-pedido', compact('data'));
    }
}
