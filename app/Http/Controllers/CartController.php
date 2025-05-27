<?php

namespace App\Http\Controllers;

use App\Helpers\RedsysAPI;
use App\Models\Order;
use App\Models\OrderState;
use App\Models\Page;
use Illuminate\Support\Facades\Session;

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
        if (! session()->has('cart') || empty(session('cart'))) {
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

    public function responseNotification(): void
    {
        Session::forget('cart');
        $redSys = new RedsysAPI;

        $datos = request('Ds_MerchantParameters');
        $signatureRecibida = request('Ds_Signature');

        if (empty($datos) || empty($signatureRecibida)) {
            abort(404, 'Datos de Redsys no recibidos');
        }

        $decodec = json_decode($redSys->decodeMerchantParameters($datos), true);
        $firma = $redSys->createMerchantSignatureNotif(config('redsys.key'), $datos);
        $pedido = Order::where('number', $decodec['Ds_Order'])->firstOrFail();

        if ($redSys->checkSignature($firma, $signatureRecibida) && intval($decodec['Ds_Response']) <= 99) {
            $pedido->payed($decodec);
        } else {
            $error = hash_equals($firma, $signatureRecibida)
                ? estado_redsys($decodec['Ds_Response'])
                : 'Firma no válida';
            $pedido->error($error);
        }

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
        if ($pedido->state->name != OrderState::PENDIENTE) {
            abort(404);
        }

        $redSys = new RedsysAPI;
        $data = $redSys->actualizaDatosRedSys($pedido);

        return view('frontend.pagar-pedido', compact('data'));
    }
}
