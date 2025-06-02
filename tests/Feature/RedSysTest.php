<?php

use App\Helpers\RedsysAPI;
use App\Models\Donation;
use App\Models\Payment;
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

    $donacion = Donation::factory()->create([
        'number' => '9V97ISQQ',
        'amount' => 10.00,
        'type' => Donation::RECURRENTE,
        'identifier' => '625d3d2506fefefb9e79990f192fc3de74c08317',
        'info' => json_decode('{"Ds_Date":"31%2F05%2F2025","Ds_Hour":"00%3A39","Ds_SecurePayment":"1","Ds_Amount":"1000","Ds_Currency":"978","Ds_Order":"9V97ISQQ","Ds_MerchantCode":"357328590","Ds_Terminal":"001","Ds_Response":"0000","Ds_TransactionType":"0","Ds_MerchantData":"","Ds_AuthorisationCode":"031853","Ds_ExpiryDate":"4912","Ds_Merchant_Identifier":"625d3d2506fefefb9e79990f192fc3de74c08317","Ds_ConsumerLanguage":"1","Ds_Card_Country":"724","Ds_Card_Brand":"1","Ds_Merchant_Cof_Txnid":"2505310039010","Ds_ProcessedPayMethod":"78","Ds_Control_1748644741093":"1748644741093"}'),
    ]);

    Payment::factory()->create([
        'payable_type' => 'App\\Models\\Donation',
        'payable_id' => $donacion->id,
        'amount' => $donacion->totalRedsys,
        'number' => generatePaymentNumber($donacion),
        'info' => [
        ],
    ]);

    $redSys = new RedsysAPI();
    $redSys->getFormPagoRecurrente($donacion, false);
    dd(config('redsys.merchantcode'));
});
