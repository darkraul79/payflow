<?php

namespace App\Http\Controllers;

use App\Events\CreateOrderEvent;
use App\Events\NewDonationEvent;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use LaravelGateway\Facades\Gateway;

/**
 * NUEVO RedsysController usando el paquete Laravel Gateway
 *
 * Este es un ejemplo de cómo refactorizar el controlador actual
 * para usar el nuevo paquete laravel-gateway en lugar de RedsysAPI
 */
class RedsysControllerRefactored extends Controller
{
    /**
     * Handle Redsys webhook response
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
        // Procesar callback usando Laravel Gateway
        $result = Gateway::withRedsys()->processCallback($request->all());
        $decodedData = $result['decoded_data'];

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $donacion = Payment::where('number', $decodedData['Ds_Order'])->firstOrFail()->payable;

        // Verificar si el pago fue exitoso
        if (Gateway::withRedsys()->isSuccessful($request->all())) {
            $donacion->payed($decodedData);
        } else {
            $error = Gateway::withRedsys()->getErrorMessage($request->all());
            $donacion->error_pago($decodedData, $error);
        }

        NewDonationEvent::dispatch($donacion);

        return redirect()->route('donacion.finalizada', [
            'donacion' => $donacion->number,
        ])->with('model', class_basename($donacion));
    }

    /**
     * Handle order response from Redsys
     */
    private function handleOrderResponse(Request $request): RedirectResponse
    {
        Session::forget('cart');

        // Procesar callback usando Laravel Gateway
        $result = Gateway::withRedsys()->processCallback($request->all());
        $decodedData = $result['decoded_data'];

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $pedido = Order::where('number', $decodedData['Ds_Order'])->firstOrFail();

        CreateOrderEvent::dispatch($pedido);

        // Verificar si el pago fue exitoso
        if (Gateway::withRedsys()->isSuccessful($request->all())) {
            $pedido->payed($decodedData);
        } else {
            $error = Gateway::withRedsys()->getErrorMessage($request->all());
            $pedido->error($error, $decodedData);
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

        // Validar que existan los datos necesarios
        if (! isset($response['Ds_MerchantParameters']) || ! isset($response['Ds_Signature'])) {
            abort(404, 'Datos de Redsys no recibidos');
        }

        // Procesar callback usando Laravel Gateway
        $result = Gateway::withRedsys()->processCallback($response);
        $decodedData = $result['decoded_data'];

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $pago = Payment::where('number', $decodedData['Ds_Order'])->firstOrFail();

        $cantidad = 0;
        $info = $decodedData;

        // Verificar si el pago fue exitoso
        if (Gateway::withRedsys()->isSuccessful($response)) {
            $cantidad = convert_amount_from_redsys($decodedData['Ds_Amount']);
        } else {
            $error = Gateway::withRedsys()->getErrorMessage($response);
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
     *
     * @noinspection PhpDynamicAsStaticMethodCallInspection
     */
    public function show(string $number): View|RedirectResponse
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $pago = Payment::where('number', $number)->firstOrFail();
        $modelo = $pago->payable;

        return view($modelo->getResultView(), $modelo->getStaticViewParams());
    }
}
