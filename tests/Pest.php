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
use App\Helpers\RedsysAPI;
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

function getMerchanParamasOrderOk($amount, $order_number): string
{

    $data = [
        'Ds_Date' => '27%2F05%2F2025',
        'Ds_Hour' => '14%3A18',
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
        'Ds_Control_1748348283917' => '1748348283917',
    ];
    $redSys = new RedsysAPI;

    return $redSys->encodeBase64(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function getMerchanParamsDonationReccurente(Donation $donacion, $ok = false): string
{
    $data = [
        'Ds_Date' => Carbon::now()->format('d%2m%2Y'),
        'Ds_Hour' => '17%3A47',
        'Ds_SecurePayment' => '1',
        'Ds_Amount' => convertNumberToRedSys($donacion->amount),
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
    $redSys = new RedsysAPI;

    return $redSys->encodeBase64(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function getMerchanParamsDonationResponse(Donation $donacion, $ok = false): string
{
    $data = [
        'Ds_Amount' => convertNumberToRedSys($donacion->amount),
        'Ds_Date' => Carbon::now()->format('d%2m%2Y'),
        'Ds_Hour' => Carbon::now()->format('h%3Ali'),
        'Ds_Order' => $donacion->number,
        'Ds_MerchantCode' => '357328590',
        'Ds_Terminal' => '1',
        'Ds_Response' => $ok ? '0000' : '9928',
        'Ds_AuthorisationCode' => '191312',
        'Ds_TransactionType' => '0',
        'Ds_SecurePayment' => '0',
        'Ds_Language' => '1',
        'Ds_Merchant_Identifier' => '625d3d2506fefefb9e79990f192fc3de74c08317',
        'Ds_MerchantData' => '',
        'Ds_Card_Country' => '724',
        'Ds_Card_Brand' => '1',
        'Ds_ProcessedPayMethod' => '3',
        'Ds_Control_1748965667893' => '1748965667893',

    ];
    $redSys = new RedsysAPI;

    return $redSys->encodeBase64(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function getMerchanParamsDonationUnica(Donation $donacion, $ok = false): string
{
    $data = [
        'Ds_Date' => Carbon::now()->format('d%2m%2Y'),
        'Ds_Hour' => '17%3A47',
        'Ds_SecurePayment' => '1',
        'Ds_Amount' => convertNumberToRedSys($donacion->amount),
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
    $redSys = new RedsysAPI;

    return $redSys->encodeBase64(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function getMerchanParamsOrder(Order $order, $ok = false): string
{
    $data = [
        'Ds_Date' => Carbon::now()->format('d%20m%20Y'),
        'Ds_Hour' => Carbon::now()->format('H:i'),
        'Ds_SecurePayment' => '1',
        'Ds_Amount' => convertNumberToRedSys($order->amount).'',
        'Ds_Currency' => '978',
        'Ds_Order' => $order->number,
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
    $redSys = new RedsysAPI;

    return $redSys->encodeBase64(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function getResponseDonation(Donation $donacion, $ok = false): array
{
    $redSys = new RedsysAPI;
    if ($donacion->type === DonationType::RECURRENTE->value) {
        $merchantParams = getMerchanParamsDonationReccurente($donacion, $ok);
    } else {

        $merchantParams = getMerchanParamsDonationUnica($donacion, $ok);
    }

    return [
        'Ds_MerchantParameters' => $merchantParams,
        'Ds_Signature' => $redSys->createMerchantSignatureNotif(config('redsys.key'), $merchantParams),
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ];
}

function getResponseOrder(Order $order, $ok = false): array
{
    $redSys = new RedsysAPI;

    $merchantParams = getMerchanParamsOrder($order, $ok);

    return [
        'Ds_MerchantParameters' => $merchantParams,
        'Ds_Signature' => $redSys->createMerchantSignatureNotif(config('redsys.key'), $merchantParams),
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ];
}
