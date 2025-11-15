<?php

namespace App\Support;

use App\Enums\PaymentMethod;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @phpstan-type PaymentMethodArray array{
 *   code:string,
 *   label:string,
 *   supportsRecurring:bool,
 * }
 */
final class PaymentMethodData implements Arrayable
{
    public function __construct(
        public PaymentMethod $method
    ) {}

    public static function make(PaymentMethod $method): self
    {
        return new self($method);
    }

    /** @return PaymentMethodArray */
    public function toArray(): array
    {
        return [
            'code' => $this->method->value,
            'label' => $this->method->label(),
            'supportsRecurring' => $this->method->supportsRecurring(),
            'icon' => $this->method->getIcon(),
        ];
    }
}
