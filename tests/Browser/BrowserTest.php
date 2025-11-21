<?php

use App\Enums\DonationType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Livewire\DonacionBanner;
use App\Models\Donation;
use App\Models\Page;
use Pest\Browser\Api\ArrayablePendingAwaitablePage;
use Pest\Browser\Api\PendingAwaitablePage;
use Tests\TestCase;

pest()->beforeEach(function () {
    Page::factory()->isHome()->create();
});

test('puedo hacer donación única en modal', function ($paymentMethod) {

    $page = preparePage();

    $page
        ->press('@modal-type-donacion-unica');

    assertStep1($page);
    assertStep2($page, false);
    assertStep4($page, $paymentMethod);

    $donacion = assertDonationProcesed($this);

    expect($donacion->state->name)->toBe(OrderStatus::PAGADO->value)
        ->and($donacion->payments->first()->amount)->toBe(10.0)
        ->and($donacion->type)->toBe(DonationType::UNICA->value);

})->with([
    PaymentMethod::BIZUM->value,
    PaymentMethod::TARJETA->value,
])->group('lentos');

test('puedo hacer donación única en modal con certificado', function ($paymentMethod) {

    $page = preparePage();

    $page
        ->press('@modal-type-donacion-unica');

    assertStep1($page);
    assertStep2($page, true);
    assertStep4($page, $paymentMethod);

    $donacion = assertDonationProcesed($this);

    expect($donacion->state->name)->toBe(OrderStatus::PAGADO->value)
        ->and($donacion->payments->first()->amount)->toBe(10.0)
        ->and($donacion->type)->toBe(DonationType::UNICA->value);

})->with([
    PaymentMethod::BIZUM->value,
    PaymentMethod::TARJETA->value,
])->group('lentos');

test('puedo hacer donación recurrente en modal', function ($paymentMethod) {

    $page = preparePage();

    $page
        ->press('@modal-type-hazte-socio')
        ->press('@modal-frequency-mensual');

    assertStep1($page);
    assertStep2($page, false);
    assertStep4($page, $paymentMethod);

    $donacion = assertDonationProcesed($this);

    expect($donacion->state->name)->toBe(OrderStatus::ACTIVA->value)
        ->and($donacion->payments->first()->amount)->toBe(10.0)
        ->and($donacion->type)->toBe(DonationType::RECURRENTE->value);

})->with([
    PaymentMethod::TARJETA->value, // SOLO ADMITE TARJETA
])->group('lentos');

test('puedo hacer donación única en modal recurrente con certificado', function ($paymentMethod) {

    $page = preparePage();

    $page
        ->press('@modal-type-hazte-socio')
        ->press('@modal-frequency-mensual');

    assertStep1($page);
    assertStep2($page, true);
    assertStep4($page, $paymentMethod);

    $donacion = assertDonationProcesed($this);

    expect($donacion->state->name)->toBe(OrderStatus::ACTIVA->value)
        ->and($donacion->payments->first()->amount)->toBe(10.0)
        ->and($donacion->type)->toBe(DonationType::RECURRENTE->value);

})->with([
    PaymentMethod::TARJETA->value,
])->group('lentos');

test('cada vez que abro ventana de donación se resetea el componente', function () {

    $this->get('/')
        ->assertSeeLivewire(DonacionBanner::class);
    $page = visit(['/']);
    $page->click('@DonacionButtonModal')
        ->click('Hazte Socio')
        ->assertRadioSelected('type', DonationType::RECURRENTE->value)
        ->type('amount', '10,35')
        ->click('@DonacionButtonModalClose')
        ->click('@DonacionButtonModal')
        ->assertRadioNotSelected('type', DonationType::RECURRENTE->value)
        ->assertValue('amount', 0);

})->group('lentos');

function assertDonationProcesed(
    TestCase|\PHPUnit\Framework\TestCase $testcase
): ?Donation {
    $donacion = Donation::first();

    expect(Donation::count())->toBe(1);

    // Obtener la respuesta de Redsys y seguir la redirección
    $response = $testcase->get(route('donation.response', getResponseDonation($donacion, true)));

    // Verificar que redirige correctamente
    $response->assertRedirectToRoute('donacion.finalizada', $donacion->number);

    // Seguir la redirección y verificar el contenido
    $finalPage = $testcase->followingRedirects()->get(route('donation.response', getResponseDonation($donacion, true)));

    // Verificar que contiene el mensaje de agradecimiento
    $finalPage->assertSee('Gracias por tu donación', false);

    $donacion->refresh();

    return $donacion;
}

function assertStep1(ArrayablePendingAwaitablePage|PendingAwaitablePage $page
): void {
    $page
        ->press('@modal-amount_select-10-eur')
        ->press('@modal-donation-step-1-next-button')
        ->assertSee('¿Necesitas un certificado de donaciones?');
}

function assertStep2(ArrayablePendingAwaitablePage|PendingAwaitablePage $page, bool $certificate = false): void
{

    if ($certificate) {
        $page
            ->press('@modal-needsCertificate-si')
            ->press('@modal-donation-step-2-next-button')
            ->assertSee('Datos para certificado de donaciones')
            ->type('modal_certificate_name', 'Juan')
            ->type('modal_certificate_last_name', 'Sebastian')
            ->type('modal_certificate_last_name2', 'Pérez')
            ->type('modal_certificate_company', 'Empresa')
            ->type('modal_certificate_nif', '123456789A')
            ->type('modal_certificate_address', 'Calle Falsa 123')
            ->type('modal_certificate_cp', '28001')
            ->select('modal_certificate_province', 'Madrid')
            ->type('modal_certificate_city', 'Madrid')
            ->type('modal_certificate_email', 'example@emaple.com')
            ->type('modal_certificate_phone', '666666666')
            ->press('@modal-donation-step-3-next-button');

    } else {
        $page
            ->press('@modal-needsCertificate-no')
            ->press('@modal-donation-step-2-next-button');
    }

}

function assertStep4(ArrayablePendingAwaitablePage|PendingAwaitablePage $page, string $paymentMethod): void
{
    $page
        ->assertSee('Método de pago')
        ->press('@modal-payment-method-'.$paymentMethod)
        ->pressAndWaitFor('@modal-button-pay', 5)
        ->assertSourceMissing('<div class="text-error/80 w-full text-[11px]">');
}

function preparePage(): PendingAwaitablePage|ArrayablePendingAwaitablePage
{
    $page = visit('/');
    $page->click('@DonacionButtonModal')
        ->assertSee('Dona a la FUNDACIÓN Elena Tertre');

    return $page;
}
