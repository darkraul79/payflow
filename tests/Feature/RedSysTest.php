<?php

use App\Helpers\RedsysAPI;
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
