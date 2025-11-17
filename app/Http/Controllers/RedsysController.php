<?php

namespace App\Http\Controllers;

use App\Events\CreateOrderEvent;
use App\Events\NewDonationEvent;
use App\Helpers\RedsysAPI;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class RedsysController extends Controller
{
    /**
     * Handle Redsys donation webhook response
     */
    public function store(Request $request): RedirectResponse
    {

        return match ($request->route('type')) {
            'donation' => $this->handleDonationResponse($request),
            'order' => $this->handleOrderResponse($request),
            'payment' => $this->handlePaymentResponse($request),
            default => $this->destroy(),
        };
    }

    /**
     * Handle donation response from Redsys
     */
    private function handleDonationResponse(Request $request): RedirectResponse
    {
        $redSys = new RedsysAPI;
        [$decodec, $firma] = $this->validateRedsysRequest($request, $redSys);

        $donacion = Payment::where('number', $decodec['Ds_Order'])->firstOrFail()->payable;

        if ($this->isSuccessfulPayment($redSys, $firma, $decodec)) {
            $donacion->payed($decodec);
        } else {
            $error = $this->getPaymentError($firma, $decodec);
            $donacion->error_pago($decodec, $error);
        }

        NewDonationEvent::dispatch($donacion);

        return redirect()->route('donacion.finalizada', [
            'donacion' => $donacion->number,
        ])->with('model', class_basename($donacion));
    }

    /**
     * Validate and decode Redsys request parameters
     */
    private function validateRedsysRequest(Request $request, RedsysAPI $redSys): array
    {
        $datos = $request->input('Ds_MerchantParameters');
        $signatureRecibida = $request->input('Ds_Signature');

        if (empty($datos) || empty($signatureRecibida)) {
            abort(404, 'Datos de Redsys no recibidos');
        }

        $decodec = json_decode($redSys->decodeMerchantParameters($datos), true);
        $firma = $redSys->createMerchantSignatureNotif(config('redsys.key'), $datos);

        return [$decodec, $firma];
    }

    /**
     * Check if payment was successful
     */
    private function isSuccessfulPayment(RedsysAPI $redSys, string $firma, array $decodec): bool
    {
        $signatureRecibida = request('Ds_Signature');

        return $redSys->checkSignature($firma, $signatureRecibida)
            && intval($decodec['Ds_Response']) <= 99;
    }

    /**
     * Get payment error message
     */
    private function getPaymentError(string $firma, array $decodec, ?string $signatureRecibida = null): string
    {
        $signatureRecibida = $signatureRecibida ?? request('Ds_Signature');

        return hash_equals($firma, $signatureRecibida)
            ? estado_redsys($decodec['Ds_Response'])
            : 'Firma no válida';
    }

    /**
     * Handle order response from Redsys
     */
    private function handleOrderResponse(Request $request): RedirectResponse
    {
        Session::forget('cart');
        $redSys = new RedsysAPI;
        [$decodec, $firma] = $this->validateRedsysRequest($request, $redSys);

        $pedido = Order::where('number', $decodec['Ds_Order'])->firstOrFail();

        CreateOrderEvent::dispatch($pedido);

        if ($this->isSuccessfulPayment($redSys, $firma, $decodec)) {
            $pedido->payed($decodec);
        } else {
            $error = $this->getPaymentError($firma, $decodec);
            $pedido->error($error, $decodec);
        }

        return redirect()->route('pedido.finalizado', [
            'pedido' => $pedido->number,
        ])->with('model', class_basename($pedido));
    }

    /**
     * Handle payment response from Redsys (alternative flow)
     */
    private function handlePaymentResponse(Request $request): RedirectResponse
    {
        $response = json_decode($request->input('response'), true);
        $redSys = new RedsysAPI;

        $datos = $response['Ds_MerchantParameters'];
        $signatureRecibida = $response['Ds_Signature'];

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
            $error = $this->getPaymentError($firma, $decodec, $signatureRecibida);
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

    public function destroy(): RedirectResponse
    {
        return redirect()->abort(404, 'Tipo de respuesta no válido');
    }

    /**
     * Show payment result page
     */
    public function show(string $number): View|RedirectResponse
    {
        $pago = Payment::where('number', $number)->firstOrFail();
        $modelo = $pago->payable;

        return view($modelo->getResultView(), $modelo->getStaticViewParams());
    }
}
