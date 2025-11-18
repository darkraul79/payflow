<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
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
    return getenv('GITHUB_ACTIONS') || getenv('CI');
}

/*
|--------------------------------------------------------------------------
| User & Authentication Helpers
|--------------------------------------------------------------------------
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

/*
|--------------------------------------------------------------------------
| Product & Cart Helpers
|--------------------------------------------------------------------------
*/

function getProducto(?Product $producto = null): Product
{
    return $producto ?? Product::factory()->create([
        'name' => 'Producto de prueba',
        'price' => 10,
        'stock' => 2,
    ]);
}

function addProductToCart(?Product $producto = null): void
{
    $producto = getProducto($producto);

    livewire(CardProduct::class, [
        'product' => $producto,
        'quantity' => 1,
    ])->call('addToCart');
}

function setShippingMethod(?ShippingMethod $shippingMethod = null): void
{
    $metodoEnvio = $shippingMethod ?? ShippingMethod::factory()->create();

    livewire(PageCartComponent::class)
        ->set('shipping_method', $metodoEnvio->id)
        ->call('submit');
}

/*
|--------------------------------------------------------------------------
| Order Creation Helpers
|--------------------------------------------------------------------------
*/

function creaPedido(?Product $producto = null, ?ShippingMethod $shippingMethod = null): Order
{
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

/*
|--------------------------------------------------------------------------
| Redsys Response Helpers
|--------------------------------------------------------------------------
| Funciones para simular respuestas válidas de Redsys en tests
*/

/**
 * Genera una respuesta completa de Redsys para una donación
 */
function getResponseDonation(Donation $donacion, bool $state = true): array
{
    $params = buildRedsysParams(
        amount: convert_amount_to_redsys($donacion->amount),
        order: $donacion->number,
        response: $state ? '0000' : '9928'
    );

    // Campos adicionales para donaciones recurrentes
    if ($donacion->type === DonationType::RECURRENTE->value && $state) {
        $params['Ds_Merchant_Identifier'] = 'IDENTIFIER_'.Str::random(10);
        $params['Ds_Merchant_Cof_Txnid'] = Str::random(10);
        $params['Ds_ExpiryDate'] = '4912';
    }

    return generateRedsysResponse($params, $donacion->number);
}

/**
 * Genera una respuesta completa de Redsys para un pedido
 */
function getResponseOrder(Order $order): array
{
    $params = buildRedsysParams(
        amount: convert_amount_to_redsys($order->amount),
        order: $order->number,
        response: '0000'
    );

    $params['Ds_ProcessedPayMethod'] = '78';

    return generateRedsysResponse($params, $order->number);
}

/**
 * Construye los parámetros base para Redsys
 */
function buildRedsysParams(string $amount, string $order, string $response): array
{
    return [
        'Ds_Date' => now()->format('d/m/Y'),
        'Ds_Hour' => now()->format('H:i'),
        'Ds_SecurePayment' => '1',
        'Ds_Amount' => $amount,
        'Ds_Currency' => '978',
        'Ds_Order' => $order,
        'Ds_MerchantCode' => config('redsys.merchantcode') ?? config('redsys.merchant_code'),
        'Ds_Terminal' => config('redsys.terminal', '001'),
        'Ds_Response' => $response,
        'Ds_TransactionType' => config('redsys.transactiontype') ?? config('redsys.transaction_type', '0'),
        'Ds_MerchantData' => '',
        'Ds_AuthorisationCode' => '191312',
        'Ds_ConsumerLanguage' => '1',
        'Ds_Card_Country' => '724',
        'Ds_Card_Brand' => '1',
        'Ds_Control_'.time() => (string) time(),
    ];
}

/**
 * Genera respuesta completa de Redsys con firma válida
 */
function generateRedsysResponse(array $params, string $order): array
{
    $merchantParams = base64_encode(json_encode($params, JSON_UNESCAPED_SLASHES));
    $encryptedOrder = encryptRedsysOrder($order);
    $signature = signRedsysParams($merchantParams, $encryptedOrder);

    return [
        'Ds_MerchantParameters' => $merchantParams,
        'Ds_Signature' => $signature,
        'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
    ];
}

/**
 * Encripta el orden usando el algoritmo 3DES de Redsys
 */
function encryptRedsysOrder(string $order): string
{
    $decodedKey = base64_decode(config('redsys.key'));
    $iv = "\0\0\0\0\0\0\0\0";
    $length = (int) ceil(strlen($order) / 8) * 8;

    return substr(
        openssl_encrypt(
            $order.str_repeat("\0", $length - strlen($order)),
            'des-ede3-cbc',
            $decodedKey,
            OPENSSL_RAW_DATA,
            $iv
        ),
        0,
        $length
    );
}

/**
 * Genera la firma HMAC-SHA256 en formato URL-safe base64
 */
function signRedsysParams(string $merchantParams, string $encryptedOrder): string
{
    $signature = hash_hmac('sha256', $merchantParams, $encryptedOrder, true);

    return strtr(base64_encode($signature), '+/', '-_');
}

/*
|--------------------------------------------------------------------------
| Legacy Functions (mantener para compatibilidad con tests antiguos)
|--------------------------------------------------------------------------
*/

/**
 * @deprecated Usar getResponseOrder() en su lugar
 */
function getMerchanParamasOrder(string $amount, string $order_number): string
{
    $params = buildRedsysParams($amount, $order_number, '0000');
    $params['Ds_ProcessedPayMethod'] = '78';

    return base64_encode(json_encode($params, JSON_UNESCAPED_SLASHES));
}
