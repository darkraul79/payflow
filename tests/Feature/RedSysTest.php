<?php

use App\Helpers\RedsysAPI;
use App\Models\OrderState;

test('confirmo pedido cambia a pagado despues de llegar a ok', function () {

    $pedido = creaPedido();
    $data = [
        'Ds_Date' => '27%2F05%2F2025',
        'Ds_Hour' => '14%3A18',
        'Ds_SecurePayment' => '1',
        'Ds_Amount' => $pedido->totalRedsys,
        'Ds_Currency' => '978',
        'Ds_Order' => $pedido->number,
        'Ds_MerchantCode' => config('redsys.merchant_code'),
        'Ds_Terminal' => config('redsys.terminal'),
        'Ds_Response' => '0000',
        'Ds_TransactionType' => config('redsys.transaction_type'),
        'Ds_MerchantData' => '',
        'Ds_AuthorisationCode' => '025172',
        'Ds_ConsumerLanguage' => '1',
        'Ds_Card_Country' => '724',
        'Ds_Card_Brand' => '1',
        'Ds_ProcessedPayMethod' => '78',
        'Ds_Control_1748348283917' => '1748348283917',
    ];

    $redSys = new RedsysAPI;

    $redSys->actualizaDatosRedSys($pedido);
    $dsMerchantParameters = $redSys->encodeBase64(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

    $this->travel(10)->seconds();
    $this->get(route('checkout.ok', [
        'Ds_Signature' => $redSys->createMerchantSignatureNotif(config('redsys.key'), $dsMerchantParameters),
        'Ds_MerchantParameters' => $dsMerchantParameters,
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ]));
    $pedido->refresh();
    expect($pedido->state->name)->toBe(OrderState::PAGADO);

});
