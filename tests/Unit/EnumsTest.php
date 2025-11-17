<?php

use App\Enums\AddressType;
use App\Enums\DonationFrequency;
use App\Enums\DonationType;
use App\Enums\OrderStatus;

describe('OrderStatus Enum', function () {
    it('tiene todos los estados correctos', function () {
        expect(OrderStatus::cases())->toHaveCount(8);
    });

    it('devuelve el icono correcto para cada estado', function () {
        expect(OrderStatus::PENDIENTE->icon())->toBe('bi-clock-history');
        expect(OrderStatus::PAGADO->icon())->toBe('bi-check-circle');
        expect(OrderStatus::ENVIADO->icon())->toBe('bi-truck');
        expect(OrderStatus::FINALIZADO->icon())->toBe('bi-check-all');
        expect(OrderStatus::ERROR->icon())->toBe('bi-exclamation-triangle');
        expect(OrderStatus::CANCELADO->icon())->toBe('bi-x-circle');
        expect(OrderStatus::ACEPTADO->icon())->toBe('bi-check');
        expect(OrderStatus::ACTIVA->icon())->toBe('bi-play-circle');
    });

    it('devuelve el color correcto para cada estado', function () {
        expect(OrderStatus::PENDIENTE->color())->toBe('warning');
        expect(OrderStatus::PAGADO->color())->toBe('success');
        expect(OrderStatus::ENVIADO->color())->toBe('secondary');
        expect(OrderStatus::FINALIZADO->color())->toBe('info');
        expect(OrderStatus::ERROR->color())->toBe('danger');
        expect(OrderStatus::CANCELADO->color())->toBe('danger');
        expect(OrderStatus::ACEPTADO->color())->toBe('success');
        expect(OrderStatus::ACTIVA->color())->toBe('green');
    });

    it('devuelve el subject de email correcto', function () {
        expect(OrderStatus::PENDIENTE->emailSubject())->toContain('pendiente de pago');
        expect(OrderStatus::PAGADO->emailSubject())->toContain('preparación');
        expect(OrderStatus::ENVIADO->emailSubject())->toContain('en camino');
        expect(OrderStatus::FINALIZADO->emailSubject())->toContain('ola solidaria');
        expect(OrderStatus::ERROR->emailSubject())->toContain('problema');
        expect(OrderStatus::CANCELADO->emailSubject())->toContain('cancelado');
    });

    it('devuelve la vista de email correcta', function () {
        expect(OrderStatus::PENDIENTE->emailView())->toBe('emails.order-pending');
        expect(OrderStatus::PAGADO->emailView())->toBe('emails.order-paid');
        expect(OrderStatus::ENVIADO->emailView())->toBe('emails.order-shipped');
        expect(OrderStatus::FINALIZADO->emailView())->toBe('emails.order-completed');
        expect(OrderStatus::ERROR->emailView())->toBe('emails.order-error');
        expect(OrderStatus::CANCELADO->emailView())->toBe('emails.order-cancel');
    });

    it('devuelve todos los valores como array', function () {
        $values = OrderStatus::values();

        expect($values)->toBeArray()
            ->and($values)->toContain('Pendiente de pago')
            ->and($values)->toContain('Pagado')
            ->and($values)->toContain('Enviado')
            ->and($values)->toContain('Finalizado')
            ->and($values)->toContain('ERROR')
            ->and($values)->toContain('Cancelado')
            ->and($values)->toContain('Aceptado')
            ->and($values)->toContain('Activa');
    });

    it('devuelve estados disponibles para pedidos', function () {
        $orderStates = OrderStatus::orderStates();

        expect($orderStates)->toBeArray()
            ->and($orderStates)->toHaveCount(6)
            ->and($orderStates)->toContain('Pendiente de pago')
            ->and($orderStates)->toContain('Pagado')
            ->and($orderStates)->toContain('Enviado')
            ->and($orderStates)->toContain('Finalizado')
            ->and($orderStates)->toContain('ERROR')
            ->and($orderStates)->toContain('Cancelado')
            ->and($orderStates)->not->toContain('Activa')
            ->and($orderStates)->not->toContain('Aceptado');
    });

    it('devuelve estados disponibles para donaciones', function () {
        $donationStates = OrderStatus::donationStates();

        expect($donationStates)->toBeArray()
            ->and($donationStates)->toHaveCount(5)
            ->and($donationStates)->toContain('Pendiente de pago')
            ->and($donationStates)->toContain('Pagado')
            ->and($donationStates)->toContain('Activa')
            ->and($donationStates)->toContain('ERROR')
            ->and($donationStates)->toContain('Cancelado')
            ->and($donationStates)->not->toContain('Enviado')
            ->and($donationStates)->not->toContain('Finalizado');
    });
});

describe('AddressType Enum', function () {
    it('tiene todos los tipos correctos', function () {
        expect(AddressType::cases())->toHaveCount(3);
    });

    it('devuelve el icono correcto para cada tipo', function () {
        expect(AddressType::BILLING->icon())->toBe('bi-receipt');
        expect(AddressType::SHIPPING->icon())->toBe('bi-truck');
        expect(AddressType::CERTIFICATE->icon())->toBe('bi-file-earmark-text');
    });

    it('devuelve el color correcto para cada tipo', function () {
        expect(AddressType::BILLING->color())->toBe('primary');
        expect(AddressType::SHIPPING->color())->toBe('success');
        expect(AddressType::CERTIFICATE->color())->toBe('info');
    });

    it('devuelve todos los valores como array', function () {
        $values = AddressType::values();

        expect($values)->toBeArray()
            ->and($values)->toHaveCount(3)
            ->and($values)->toContain('Facturación')
            ->and($values)->toContain('Envío')
            ->and($values)->toContain('Certificado');
    });

    it('puede obtener enum desde valor string', function () {
        expect(AddressType::tryFrom('Facturación'))->toBe(AddressType::BILLING);
        expect(AddressType::tryFrom('Envío'))->toBe(AddressType::SHIPPING);
        expect(AddressType::tryFrom('Certificado'))->toBe(AddressType::CERTIFICATE);
        expect(AddressType::tryFrom('Invalid'))->toBeNull();
    });
});

describe('DonationType Enum', function () {
    it('tiene todos los tipos correctos', function () {
        expect(DonationType::cases())->toHaveCount(2);
    });

    it('devuelve el icono correcto para cada tipo', function () {
        expect(DonationType::UNICA->icon())->toBe('bi-heart');
        expect(DonationType::RECURRENTE->icon())->toBe('bi-arrow-repeat');
    });

    it('devuelve el color correcto para cada tipo', function () {
        expect(DonationType::UNICA->color())->toBe('primary');
        expect(DonationType::RECURRENTE->color())->toBe('success');
    });

    it('devuelve la descripción correcta', function () {
        expect(DonationType::UNICA->description())->toBe('Donación única');
        expect(DonationType::RECURRENTE->description())->toBe('Donación recurrente');
    });

    it('devuelve todos los valores como array', function () {
        $values = DonationType::values();

        expect($values)->toBeArray()
            ->and($values)->toHaveCount(2)
            ->and($values)->toContain('Simple')
            ->and($values)->toContain('Recurrente');
    });

    it('puede obtener enum desde valor string', function () {
        expect(DonationType::tryFrom('Simple'))->toBe(DonationType::UNICA);
        expect(DonationType::tryFrom('Recurrente'))->toBe(DonationType::RECURRENTE);
        expect(DonationType::tryFrom('Invalid'))->toBeNull();
    });
});

describe('DonationFrequency Enum', function () {
    it('tiene todas las frecuencias correctas', function () {
        expect(DonationFrequency::cases())->toHaveCount(3);
    });

    it('devuelve el icono correcto para cada frecuencia', function () {
        expect(DonationFrequency::MENSUAL->icon())->toBe('bi-calendar-month');
        expect(DonationFrequency::TRIMESTRAL->icon())->toBe('bi-calendar3');
        expect(DonationFrequency::ANUAL->icon())->toBe('bi-calendar-year');
    });

    it('devuelve el color correcto para cada frecuencia', function () {
        expect(DonationFrequency::MENSUAL->color())->toBe('primary');
        expect(DonationFrequency::TRIMESTRAL->color())->toBe('success');
        expect(DonationFrequency::ANUAL->color())->toBe('warning');
    });

    it('devuelve la descripción correcta', function () {
        expect(DonationFrequency::MENSUAL->description())->toBe('Donación mensual');
        expect(DonationFrequency::TRIMESTRAL->description())->toBe('Donación trimestral');
        expect(DonationFrequency::ANUAL->description())->toBe('Donación anual');
    });

    it('devuelve el número de meses correcto', function () {
        expect(DonationFrequency::MENSUAL->months())->toBe(1);
        expect(DonationFrequency::TRIMESTRAL->months())->toBe(3);
        expect(DonationFrequency::ANUAL->months())->toBe(12);
    });

    it('devuelve todos los valores como array', function () {
        $values = DonationFrequency::values();

        expect($values)->toBeArray()
            ->and($values)->toHaveCount(3)
            ->and($values)->toContain('Mensual')
            ->and($values)->toContain('Trimestral')
            ->and($values)->toContain('Anual');
    });

    it('devuelve array compatible con formato antiguo', function () {
        $frequencies = DonationFrequency::toArray();

        expect($frequencies)->toBeArray()
            ->and($frequencies)->toHaveKeys(['MENSUAL', 'TRIMESTRAL', 'ANUAL'])
            ->and($frequencies['MENSUAL'])->toBe('Mensual')
            ->and($frequencies['TRIMESTRAL'])->toBe('Trimestral')
            ->and($frequencies['ANUAL'])->toBe('Anual');
    });

    it('puede obtener enum desde valor string', function () {
        expect(DonationFrequency::tryFrom('Mensual'))->toBe(DonationFrequency::MENSUAL);
        expect(DonationFrequency::tryFrom('Trimestral'))->toBe(DonationFrequency::TRIMESTRAL);
        expect(DonationFrequency::tryFrom('Anual'))->toBe(DonationFrequency::ANUAL);
        expect(DonationFrequency::tryFrom('Invalid'))->toBeNull();
    });
});
