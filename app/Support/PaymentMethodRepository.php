<?php

namespace App\Support;

use App\Enums\PaymentMethod;
use Illuminate\Support\Collection;

final class PaymentMethodRepository
{
    public function find(string $code): ?PaymentMethodData
    {
        $case = PaymentMethod::tryFrom($code);

        return $case !== null ? PaymentMethodData::make($case) : null;
    }

    public function exists(string $code): bool
    {
        return PaymentMethod::tryFrom($code) !== null;
    }

    /**
     * Devuelve los métodos de pago disponibles.
     */
    public function getPaymentsMethods(bool $includeRecurring = false): Collection
    {
        return $this->all()->filter(
            fn (PaymentMethodData $method): bool => ! $includeRecurring || $method->method->supportsRecurring()
        );
    }

    public function all(): Collection
    {
        return collect(PaymentMethod::cases())
            ->map(fn (PaymentMethod $m): PaymentMethodData => PaymentMethodData::make($m));
    }
}
