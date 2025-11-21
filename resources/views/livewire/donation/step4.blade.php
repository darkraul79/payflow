@php
    use App\Models\Donation;
@endphp

<div class="{{ $step === 4 ? 'block' : 'hidden' }}">
    <div class="flex justify-between items-top">
        <x-bi-arrow-left-short class="h-7 w-7 mt-0 cursor-pointer inline-block"
                               wire:click="toStep({{ $needsCertificate ? 3 : 2 }})" />
        <h5 class="text-left text-xl text-pretty flex-1">
            Método de pago
        </h5>
    </div>

    <div class="form-xs mt-6 form-style text-sm  mb-12">
        <p class="font-medium">Pagar con:</p>
        @if($payments_methods && count($payments_methods) > 0)
            <x-payments-methods :payment_methods="$payments_methods" :prefix="$prefix" />
        @endif
    </div>

    <div class="my-6">
        <button
            class="btn bg-amarillo text-azul-mist! hover:bg-amarillo/70 flex w-full cursor-pointer font-semibold"
            wire:click="submit()"

            data-test="{{$prefix}}-button-pay"
        >
            <span class="mx-auto flex items-center">
Pagar {{ convertPrice($amount) }}
                <x-bi-arrow-right-short class="h-7 w-7" />
            </span>
        </button>
    </div>
</div>
