@php
    use App\Models\Donation;
@endphp

<div class="{{ $step === 2 ? 'block' : 'hidden' }}">
    <h5 class="text-center text-xl text-pretty">
        ¿Necesitas un certificado de donaciones?
    </h5>

    <p class="my-3 text-center">
        Para acceder a las deducciones fiscales en tu declaración de renta,
        asegúrate de contar con el Certificado de Donaciones, emitido por la
        organización.
    </p>

    <div class="my-6 w-full">
        <div class="mx-auto flex w-full max-w-[110px]">
            <x-radiobutton-boolean
                id="certificate_yes"
                name="needsCertificate"
                :value="true"
                default="Si"
                :options="[
                    [
                        'text' => 'Si',
                        'value' => true,
                    ],
                    [
                        'text' => 'No',
                        'value' => false,
                    ],
                ]"
            />
        </div>
        <x-error class="form-error" field="needsCertificate" />
    </div>

    <div class="my-6">
        <button
            class="btn bg-amarillo text-azul-mist! hover:bg-amarillo/70 flex w-full cursor-pointer font-semibold"
            wire:click="toStep(3)"
        >
            <span class="mx-auto flex items-center">
                Seguir
                <x-bi-arrow-right-short class="h-7 w-7" />
            </span>
        </button>
    </div>
</div>
