<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different class or traits.
|
*/

use App\Enums\DonationType;
use App\Livewire\CardProduct;
use App\Livewire\FinishOrderComponent;
use App\Livewire\PageCartComponent;
use App\Models\Donation;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Carbon\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');

arch('globals')
    ->preset()
    ->laravel()
    ->ignoring([
        'App\Providers\Filament\AdminPanelProvider',
    ]);

function isCi(): bool
{
    // Detect CI environments like GitHub Actions or generic CI runners
    return getenv('GITHUB_ACTIONS') || getenv('CI');
}

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function asUser(): User
{

    $user = User::factory()->create([
        'name' => 'Raul',
        'email' => 'info@raulsebastian.es',
        'password' => 'aa',
    ]);
    actingAs($user);

    return $user;

}

function creaPedido(?Product $producto = null, ?ShippingMethod $shippingMethod = null): Order
{

    $producto = getProducto($producto);

    addProductToCart($producto);

    setShippingMethod($shippingMethod);

    livewire(FinishOrderComponent::class)
        ->assertOk()
        ->set([
            'payment_method' => 'tarjeta',
            'billing' => [
                'name' => 'Juan',
                'last_name' => 'Pérez',
                'last_name2' => 'Sánchez',
                'company' => 'Mi empresa',
                'address' => 'Calle Falsa 123',
                'province' => 'Madrid',
                'city' => 'Madrid',
                'cp' => '28001',
                'email' => 'info@raulsebastian.es',
            ],
        ])->call('submit')
        ->assertHasNoErrors();

    session()->flush();

    return Order::latest()->first();
}

function setShippingMethod(?ShippingMethod $shippingMethod = null): void
{
    $metodoEnvio = $shippingMethod ?? ShippingMethod::factory()->create();

    livewire(PageCartComponent::class)
        ->set('shipping_method', $metodoEnvio->id)
        ->call('submit');
}

function getProducto(?Product $producto = null): Product
{
    if (! $producto) {
        $producto = Product::factory()->create([
            'name' => 'Producto de prueba',
            'price' => 10,
            'stock' => 2,
        ]);
    }

    return $producto;
}

function addProductToCart(?Product $producto = null): void
{

    if (! $producto) {
        $producto = Product::factory()->create([
            'name' => 'Producto de prueba',
            'price' => 10,
            'stock' => 2,
        ]);
    }

    livewire(CardProduct::class, [
        'product' => $producto,
        'quantity' => 1,
    ])->call('addToCart');

}

function getMerchanParamsDonationReccurente(Donation $donacion, $ok = false): string
{
    $data = [
        'Ds_Date' => Carbon::now()->format('d%2m%2Y'),
        'Ds_Hour' => '17%3A47',
        'Ds_SecurePayment' => '1',
        'Ds_Amount' => convert_amount_to_redsys($donacion->amount),
        'Ds_Currency' => '978',
        'Ds_Order' => $donacion->number,
        'Ds_MerchantCode' => '357328590',
        'Ds_Terminal' => '001',
        'Ds_Response' => $ok ? '0000' : '9928',
        'Ds_TransactionType' => '0',
        'Ds_MerchantData' => '',
        'Ds_AuthorisationCode' => '191312',
        'Ds_ExpiryDate' => '4912',
        'Ds_Merchant_Identifier' => '625d3d2506fefefb9e79990f192fc3de74c08317',
        'Ds_ConsumerLanguage' => '1',
        'Ds_Card_Country' => '724',
        'Ds_Card_Brand' => '1',
        'Ds_Merchant_Cof_Txnid' => '2506031747470',
        'Ds_ProcessedPayMethod' => '78',
        'Ds_Control_1748965667893' => '1748965667893',

    ];

    return base64_encode(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function getMerchanParamsDonationUnica(Donation $donacion, $ok = false): string
{
    $data = [
        'Ds_Date' => Carbon::now()->format('d%2m%2Y'),
        'Ds_Hour' => '17%3A47',
        'Ds_SecurePayment' => '1',
        'Ds_Amount' => convert_amount_to_redsys($donacion->amount),
        'Ds_Currency' => '978',
        'Ds_Order' => $donacion->number,
        'Ds_MerchantCode' => '357328590',
        'Ds_Terminal' => '001',
        'Ds_Response' => $ok ? '0000' : '9928',
        'Ds_TransactionType' => '0',
        'Ds_MerchantData' => '',
        'Ds_AuthorisationCode' => '191312',
        'Ds_ConsumerLanguage' => '1',
        'Ds_Card_Country' => '724',
        'Ds_Card_Brand' => '1',
        'Ds_Control_1748965667893' => '1748965667893',
    ];

    return base64_encode(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function getResponseDonation(Donation $donacion, $state = true): array
{
    $amount = convert_amount_to_redsys($donacion->amount);
    $order = $donacion->number;

    $params = [
        'Ds_Date' => now()->format('d/m/Y'),
        'Ds_Hour' => now()->format('H:i'),
        'Ds_SecurePayment' => '1',
        'Ds_Amount' => $amount,
        'Ds_Currency' => '978',
        'Ds_Order' => $order,
        'Ds_MerchantCode' => config('redsys.merchantcode'),
        'Ds_Terminal' => '001',
        'Ds_Response' => $state ? '0000' : '9928',
        'Ds_TransactionType' => '0',
        'Ds_MerchantData' => '',
        'Ds_AuthorisationCode' => '191312',
        'Ds_ConsumerLanguage' => '1',
        'Ds_Card_Country' => '724',
        'Ds_Card_Brand' => '1',
        'Ds_Control_'.time() => (string) time(),
    ];

    if ($donacion->type === DonationType::RECURRENTE->value && $state) {
        $params['Ds_Merchant_Identifier'] = 'IDENTIFIER_'.Str::random(10);
        $params['Ds_Merchant_Cof_Txnid'] = Str::random(10);
        $params['Ds_Currency'] = '978';
        $params['Ds_SecurePayment'] = '1';
        $params['Ds_Terminal'] = '001';
        $params['Ds_ExpiryDate'] = '4912';
        $params['Ds_ConsumerLanguage'] = '1';
    }

    $merchantParams = getBase64_encode($params);
    $encryptedOrder = generateEncryptedData($order);

    $signatureBase64 = generateSignature($merchantParams, $encryptedOrder);

    return [
        'Ds_MerchantParameters' => $merchantParams,
        'Ds_Signature' => $signatureBase64,
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ];
}

function getBase64_encode(array $params): string
{
    return base64_encode(json_encode($params, JSON_UNESCAPED_SLASHES));
}

function generateEncryptedData(string $order): string
{
    // ✅ Encriptación EXACTA como en RedsysGateway
    $decodedKey = base64_decode(config('redsys.key'));
    $iv = "\0\0\0\0\0\0\0\0";

    $length = ceil(strlen($order) / 8) * 8;

    return substr(
        openssl_encrypt(
            $order.str_repeat("\0", $length - strlen($order)), // ✅ Padding manual
            'des-ede3-cbc',
            $decodedKey,
            OPENSSL_RAW_DATA,
            $iv
        ),
        0,
        $length
    );
}

function generateSignature(string $merchantParams, string $encryptedOrder): string
{
    $signature = hash_hmac('sha256', $merchantParams, $encryptedOrder, true);

    return strtr(base64_encode($signature), '+/', '-_');
}

function getResponseOrder(Order $order): array
{
    $amount = convert_amount_to_redsys($order->amount);
    $orderNumber = $order->number;
    // Usar el método interno de Redsys para crear firma
    $merchantParams = getMerchanParamasOrder($amount, $orderNumber);
    $encryptedOrder = generateEncryptedData($orderNumber);

    $signatureBase64 = generateSignature($merchantParams, $encryptedOrder);

    return [
        'Ds_MerchantParameters' => $merchantParams,
        'Ds_Signature' => $signatureBase64,
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ];
}

function getMerchanParamasOrder($amount, $order_number): string
{

    $data = [
        'Ds_Date' => now()->format('d/m/Y'),
        'Ds_Hour' => now()->format('H:i'),
        'Ds_SecurePayment' => '1',
        'Ds_Amount' => $amount,
        'Ds_Currency' => '978',
        'Ds_Order' => $order_number,
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
        'Ds_Control_'.time() => (string) time(),
    ];

    return getBase64_encode($data);
}
