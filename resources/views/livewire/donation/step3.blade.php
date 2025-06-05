@php
    use App\Models\Donation;
@endphp

<div class="{{ $step === 3 ? 'block' : 'hidden' }}">
    <h5 class="text-center text-xl text-pretty">
        Datos para certificado de donaciones
    </h5>

    <div class="form-style group form-xs mt-6 grid grid-cols-6 gap-1.5 text-xs">
        @include('frontend.elements.order.fields', ['prefix' => 'certificate.'])
    </div>

    <div class="my-6">
        <button
            class="btn bg-amarillo text-azul-mist! hover:bg-amarillo/70 flex w-full cursor-pointer font-semibold"
            wire:click="submit()"
        >
            <span class="mx-auto flex items-center">
                Realizar pago
                <x-bi-arrow-right-short class="h-7 w-7" />
            </span>
        </button>
    </div>
</div>
