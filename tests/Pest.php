<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use App\Helpers\RedsysAPI;
use App\Livewire\CardProduct;
use App\Livewire\FinishOrderComponent;
use App\Livewire\PageCartComponent;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Providers\Filament\AdminPanelProvider;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');

arch()->preset()->laravel()
    ->ignoring(AdminPanelProvider::class);

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

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

function something()
{
    // ..
}

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

function creaPedido(?Product $producto = null): Order
{
    if (!$producto) {
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

    livewire(PageCartComponent::class)->call('submit');

    livewire(FinishOrderComponent::class)
        ->assertOk()
        ->set([
            'payment_method' => 'tarjeta',
            'billing' => [
                'name' => 'Juan',
                'last_name' => 'Pérez',
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

/**
 * @param array $data
 * @return array
 */
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
