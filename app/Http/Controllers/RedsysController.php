<?php

namespace App\Http\Controllers;

use App\Events\CreateOrderEvent;
use App\Helpers\RedsysAPI;
use App\Models\Donation;
use App\Models\Order;
use App\Models\Page;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Response;

class RedsysController extends Controller
{
    public function donationResponse(): RedirectResponse|Response
    {

        $redSys = new RedsysAPI;

        $datos = request('Ds_MerchantParameters');
        $signatureRecibida = request('Ds_Signature');

        if (empty($datos) || empty($signatureRecibida)) {
            abort(404, 'Datos de Redsys no recibidos');
        }

        $decodec = json_decode($redSys->decodeMerchantParameters($datos), true);
        $firma = $redSys->createMerchantSignatureNotif(config('redsys.key'), $datos);

        $donacion = Payment::where('number', $decodec['Ds_Order'])->firstOrFail()->payable;

        if ($redSys->checkSignature($firma, $signatureRecibida) && intval($decodec['Ds_Response']) <= 99) {

            $donacion->payed($decodec);

        } else {
            $error = hash_equals($firma, $signatureRecibida)
                ? estado_redsys($decodec['Ds_Response'])
                : 'Firma no válida';
            $donacion->error_pago($decodec, $error);

        }

        return redirect()->route('donacion.finalizada', [
            'donacion' => $donacion->number,
        ])->with('model', class_basename($donacion));

    }

    public function pagoResponse($response): RedirectResponse|Response
    {

        $response = json_decode($response, true);
        $redSys = new RedsysAPI;

        $datos = $response->Ds_MerchantParameters;
        $signatureRecibida = $response->Ds_Signature;

        if (empty($datos) || empty($signatureRecibida)) {
            abort(404, 'Datos de Redsys no recibidos');
        }

        $decodec = json_decode($redSys->decodeMerchantParameters($datos), true);
        $firma = $redSys->createMerchantSignatureNotif(config('redsys.key'), $datos);

        $pago = Payment::where('number', $decodec['Ds_Order'])->firstOrFail();
        $cantidad = 0;
        $info = $decodec;

        if ($redSys->checkSignature($firma, $signatureRecibida) && intval($decodec['Ds_Response']) <= 99) {

            $cantidad = convertPriceFromRedsys($decodec['Ds_Amount']);

        } else {
            $error = hash_equals($firma, $signatureRecibida)
                ? estado_redsys($decodec['Ds_Response'])
                : 'Firma no válida';
            $info['error'] = $error;

        }
        $pago->update([
            'info' => $info,
            'amount' => $cantidad,
        ]);

        return redirect()->route('donacion.finalizada', [
            'donacion' => $pago->number,
        ]);

    }

    public function responseOrder(): RedirectResponse
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

        CreateOrderEvent::dispatch($pedido);

        if ($redSys->checkSignature($firma, $signatureRecibida) && intval($decodec['Ds_Response']) <= 99) {
            $pedido->payed($decodec);
        } else {
            $error = hash_equals($firma, $signatureRecibida)
                ? estado_redsys($decodec['Ds_Response'])
                : 'Firma no válida';
            $pedido->error($error, $decodec);

        }

        return redirect()->route('pedido.finalizado', [
            'pedido' => $pedido->number,
        ])->with('model', class_basename($pedido));

        //        return redirect()->route('pedido.finalizado', ['ok' => $complete]);

    }

    /**
     * Muestra la vista de resultado de donación.
     */
    public function donacionResult(bool $ok = false): View
    {

        return view('donation.'($ok ? 'ko' : 'ko'), $this->getParams('Donación'));
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

    /**
     * Muestra la vista de resultado del pedido.
     */
    public function result($number): View|RedirectResponse
    {

        $pago = Payment::where('number', $number)->firstOrFail();
        $modelo = $pago->payable;

        return view($modelo->getResultView(), $modelo->getStaticViewParams());
    }

    public function kk()
    {

        $donacion = Donation::find(2);
        $par = $donacion->recurrentPay();
        //        $redSys = new RedsysAPI();
        //        $par = $redSys->getFormPagoAutomatico($donacion, false, $nu);
        dd($par);

        return view('kk', [
            'form' => $par,
            'donacion' => $donacion,
        ]);
    }
}
