<?php

use App\Helpers\RedsysAPI;
use App\Http\Classes\PaymentProcess;
use App\Models\Donation;
use App\Models\State;

test('confirmo pedido cambia a pagado despues de llegar a ok', function () {

    $pedido = creaPedido();
    $redSys = new RedsysAPI;

    $this->travel(10)->seconds();
    $this->get(route('pedido.response', [
        'Ds_Signature' => $redSys->createMerchantSignatureNotif(config('redsys.key'), getMerchanParamasOrderOk($pedido->totalRedsys, $pedido->number)),
        'Ds_MerchantParameters' => getMerchanParamasOrderOk($pedido->totalRedsys, $pedido->number),
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ]))
        ->assertRedirect(route('pedido.finalizado', $pedido->number));
    //    $pedido->refresh();
    expect($pedido->state->name)->toBe(State::PAGADO);

});

test('puruebo send a redsys pago automatico', function () {

    $paymentProcess = new PaymentProcess(Donation::class, [
        'amount' => convertPriceNumber(10.53),
        'type' => Donation::UNICA,
    ]);
    $formData = $paymentProcess->getFormRedSysData();
    $redSys = new RedsysAPI();
    $redSys->vars_pay = $formData['Raw'];

    // DEVULVE "errorCode":"SIS0218"
    dd($redSys->send());


})->skip();
