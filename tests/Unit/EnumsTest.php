<?php

/** @noinspection PhpCaseWithValueNotFoundInEnumInspection */

use App\Enums\AddressType;
use App\Enums\DonationFrequency;
use App\Enums\DonationType;
use App\Enums\OrderStatus;

describe('OrderStatus Enum', function () {
    it('tiene todos los estados correctos', function () {
        expect(OrderStatus::cases())->toHaveCount(8);
    });

    it('devuelve el icono correcto para cada estado', function () {
        expect(OrderStatus::PENDIENTE->icon())->toBe('bi-clock-history')
            ->and(OrderStatus::PAGADO->icon())->toBe('bi-check-circle')
            ->and(OrderStatus::ENVIADO->icon())->toBe('bi-truck')
            ->and(OrderStatus::FINALIZADO->icon())->toBe('bi-check-all')
            ->and(OrderStatus::ERROR->icon())->toBe('bi-exclamation-triangle')
            ->and(OrderStatus::CANCELADO->icon())->toBe('bi-x-circle')
            ->and(OrderStatus::ACEPTADO->icon())->toBe('bi-check')
            ->and(OrderStatus::ACTIVA->icon())->toBe('bi-play-circle');
    });

    it('devuelve el color correcto para cada estado', function () {
        expect(OrderStatus::PENDIENTE->color())->toBe('warning')
            ->and(OrderStatus::PAGADO->color())->toBe('success')
            ->and(OrderStatus::ENVIADO->color())->toBe('secondary')
            ->and(OrderStatus::FINALIZADO->color())->toBe('info')
            ->and(OrderStatus::ERROR->color())->toBe('danger')
            ->and(OrderStatus::CANCELADO->color())->toBe('danger')
            ->and(OrderStatus::ACEPTADO->color())->toBe('success')
            ->and(OrderStatus::ACTIVA->color())->toBe('green');
    });

    it('devuelve el subject de email correcto', function () {
        expect(OrderStatus::PENDIENTE->emailSubject())->toContain('pendiente de pago')
            ->and(OrderStatus::PAGADO->emailSubject())->toContain('preparación')
            ->and(OrderStatus::ENVIADO->emailSubject())->toContain('en camino')
            ->and(OrderStatus::FINALIZADO->emailSubject())->toContain('ola solidaria')
            ->and(OrderStatus::ERROR->emailSubject())->toContain('problema')
            ->and(OrderStatus::CANCELADO->emailSubject())->toContain('cancelado');
    });

    it('devuelve la vista de email correcta', function () {
        expect(OrderStatus::PENDIENTE->emailView())->toBe('emails.order-pending')
            ->and(OrderStatus::PAGADO->emailView())->toBe('emails.order-paid')
            ->and(OrderStatus::ENVIADO->emailView())->toBe('emails.order-shipped')
            ->and(OrderStatus::FINALIZADO->emailView())->toBe('emails.order-completed')
            ->and(OrderStatus::ERROR->emailView())->toBe('emails.order-error')
            ->and(OrderStatus::CANCELADO->emailView())->toBe('emails.order-cancel');
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
        expect(AddressType::BILLING->icon())->toBe('bi-receipt')
            ->and(AddressType::SHIPPING->icon())->toBe('bi-truck')
            ->and(AddressType::CERTIFICATE->icon())->toBe('bi-file-earmark-text');
    });

    it('devuelve el color correcto para cada tipo', function () {
        expect(AddressType::BILLING->color())->toBe('primary')
            ->and(AddressType::SHIPPING->color())->toBe('success')
            ->and(AddressType::CERTIFICATE->color())->toBe('info');
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
        expect(AddressType::tryFrom('Facturación'))->toBe(AddressType::BILLING)
            ->and(AddressType::tryFrom('Envío'))->toBe(AddressType::SHIPPING)
            ->and(AddressType::tryFrom('Certificado'))->toBe(AddressType::CERTIFICATE)
            ->and(AddressType::tryFrom('Invalid'))->toBeNull();
    });
});

describe('DonationType Enum', function () {
    it('tiene todos los tipos correctos', function () {
        expect(DonationType::cases())->toHaveCount(2);
    });

    it('devuelve el icono correcto para cada tipo', function () {
        expect(DonationType::UNICA->icon())->toBe('bi-heart')
            ->and(DonationType::RECURRENTE->icon())->toBe('bi-arrow-repeat');
    });

    it('devuelve el color correcto para cada tipo', function () {
        expect(DonationType::UNICA->color())->toBe('primary')
            ->and(DonationType::RECURRENTE->color())->toBe('success');
    });

    it('devuelve la descripción correcta', function () {
        expect(DonationType::UNICA->description())->toBe('Donación única')
            ->and(DonationType::RECURRENTE->description())->toBe('Donación recurrente');
    });

    it('devuelve todos los valores como array', function () {
        $values = DonationType::values();

        expect($values)->toBeArray()
            ->and($values)->toHaveCount(2)
            ->and($values)->toContain('Simple')
            ->and($values)->toContain('Recurrente');
    });

    it('puede obtener enum desde valor string', function () {
        expect(DonationType::tryFrom('Simple'))->toBe(DonationType::UNICA)
            ->and(DonationType::tryFrom('Recurrente'))->toBe(DonationType::RECURRENTE)
            ->and(DonationType::tryFrom('Invalid'))->toBeNull();
    });
});

describe('DonationFrequency Enum', function () {
    it('tiene todas las frecuencias correctas', function () {
        expect(DonationFrequency::cases())->toHaveCount(3);
    });

    it('devuelve el icono correcto para cada frecuencia', function () {
        expect(DonationFrequency::MENSUAL->icon())->toBe('bi-calendar-month')
            ->and(DonationFrequency::TRIMESTRAL->icon())->toBe('bi-calendar3')
            ->and(DonationFrequency::ANUAL->icon())->toBe('bi-calendar-year');
    });

    it('devuelve el color correcto para cada frecuencia', function () {
        expect(DonationFrequency::MENSUAL->color())->toBe('primary')
            ->and(DonationFrequency::TRIMESTRAL->color())->toBe('success')
            ->and(DonationFrequency::ANUAL->color())->toBe('warning');
    });

    it('devuelve la descripción correcta', function () {
        expect(DonationFrequency::MENSUAL->description())->toBe('Donación mensual')
            ->and(DonationFrequency::TRIMESTRAL->description())->toBe('Donación trimestral')
            ->and(DonationFrequency::ANUAL->description())->toBe('Donación anual');
    });

    it('devuelve el número de meses correcto', function () {
        expect(DonationFrequency::MENSUAL->months())->toBe(1)
            ->and(DonationFrequency::TRIMESTRAL->months())->toBe(3)
            ->and(DonationFrequency::ANUAL->months())->toBe(12);
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
        expect(DonationFrequency::tryFrom('Mensual'))->toBe(DonationFrequency::MENSUAL)
            ->and(DonationFrequency::tryFrom('Trimestral'))->toBe(DonationFrequency::TRIMESTRAL)
            ->and(DonationFrequency::tryFrom('Anual'))->toBe(DonationFrequency::ANUAL)
            ->and(DonationFrequency::tryFrom('Invalid'))->toBeNull();
    });
});
