<?php

namespace App\Http\Controllers;

use App\Helpers\RedsysAPI;
use App\Models\Order;
use App\Models\OrderState;
use App\Models\Page;
use App\Models\Pedido;
use App\Models\PedidoEstado;
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

    public function pagar_pedido(Order $pedido)
    {
        if ($pedido->state->name != OrderState::PENDIENTE) {
            abort(404);
        }

        $redSys = new RedsysAPI;
        $data = $redSys->actualizaDatosRedSys($pedido);

        return view('frontend.pagar-pedido', compact('data'));
    }

    public function response()
    {
        $redSys = new RedsysAPI;


        $datos = request('Ds_MerchantParameters');
        $signatureRecibida = request('Ds_Signature');

        $decodec = json_decode($redSys->decodeMerchantParameters($datos), true);
        $firma = $redSys->createMerchantSignatureNotif(config('redsys.key'), $datos);
        $DsResponse = intval($decodec['Ds_Response']);

        $pedido = Order::where('number', $decodec['Ds_Order'])->firstOrFail();

        if ($firma === $signatureRecibida && $DsResponse <= 99) {
            $pedido->states()->create([
                'name' => OrderState::PAGADO,
                'info' => json_encode($decodec)
            ]);


            // Elimino la cesta de la compra
            Session::forget('cart');

//            return view('frontend.cesta.finalizar-pedido');
        } else {

            Session::forget('cart');
            $pedido->states->create([
                'name' => OrderState::ERROR,
                'info' => $decodec
            ]);

            $pedido->error(estado_redsys($DsResponse));

//            return view('frontend.cesta.error-pedido');
        }
    }
}
