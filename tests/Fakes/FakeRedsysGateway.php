<?php

namespace Tests\Fakes;

use Darkraul79\Payflow\Gateways\RedsysGateway;

class FakeRedsysGateway extends RedsysGateway
{
    public function __construct(public bool $ok = true)
    {
        // Inicializa propiedades protegidas sin lanzar RuntimeException en tests.
        $this->merchantKey = config('redsys.key') ?? base64_encode(random_bytes(24));
        $this->merchantCode = config('redsys.merchantcode') ?? '999999999';
        $this->terminal = '001';
        $this->currency = '978';
        $this->transactionType = '0';
        $this->tradeName = 'Tests';
        $this->environment = 'test';
        $this->version = 'HMAC_SHA256_V1';
    }

    public function sendRestPayment(): array
    {
        // Usa los parámetros ya cargados en createPayment() para construir una respuesta Redsys válida.
        $order = $this->getOrder();
        $decoded = [
            'Ds_Response' => $this->ok ? '0000' : '9928',
            'Ds_Amount' => $this->parameters['DS_MERCHANT_AMOUNT'] ?? '0',
            'Ds_Order' => $order,
            'Ds_MerchantCode' => $this->parameters['DS_MERCHANT_MERCHANTCODE'] ?? $this->merchantCode,
            'Ds_Currency' => $this->parameters['DS_MERCHANT_CURRENCY'] ?? $this->currency,
            'Ds_Terminal' => $this->parameters['DS_MERCHANT_TERMINAL'] ?? $this->terminal,
            'Ds_TransactionType' => $this->parameters['DS_MERCHANT_TRANSACTIONTYPE'] ?? $this->transactionType,
            'Ds_Merchant_Identifier' => $this->parameters['DS_MERCHANT_IDENTIFIER'] ?? null,
            'Ds_ProcessedPayMethod' => '78',
        ];

        $merchantParameters = base64_encode(json_encode($decoded, JSON_UNESCAPED_SLASHES));
        $signature = $this->createMerchantSignatureNotification($merchantParameters);

        return [
            'Ds_MerchantParameters' => $merchantParameters,
            'Ds_Signature' => $signature,
            'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
        ];
    }
}
