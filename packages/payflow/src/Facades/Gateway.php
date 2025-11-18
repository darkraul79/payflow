<?php

namespace Darkraul79\Payflow\Facades;

use Darkraul79\Payflow\Contracts\GatewayInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static GatewayInterface gateway(?string $name = null)
 * @method static GatewayInterface withRedsys()
 * @method static GatewayInterface withStripe()
 * @method static GatewayInterface withPaypal()
 * @method static array createPayment(float $amount, string $orderId, array $options = [])
 * @method static array processCallback(array $data)
 * @method static bool verifySignature(array $data)
 * @method static string getPaymentUrl()
 * @method static bool isSuccessful(array $data)
 * @method static string getErrorMessage(array $data)
 * @method static bool refund(string $transactionId, float $amount)
 *
 * @see \LaravelGateway\GatewayManager
 */
class Gateway extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'gateway';
    }
}
