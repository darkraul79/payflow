@php
    use App\Enums\DonationFrequency;use App\Enums\DonationType;use App\Models\Donation;
@endphp

<div class="{{ $step === 1 ? 'block' : 'hidden' }}">
    <h5 class="text- px-6 text-center text-xl text-pretty">
        !Dona a la FUNDACIÓN Elena Tertre!
    </h5>

    <div class="my-6 flex flex-col w-full">
        <x-radiobutton-donacion
            name="type"
            :prefix="$prefix"
            :default="$type"
            :options="[
                [
                    'text' => 'Donación única',
                    'value' => DonationType::UNICA->value,
                ],
                [
                    'text' => 'Hazte Socio',
                    'value' => DonationType::RECURRENTE->value,
                ],
            ]"
        />

        <x-error class="form-error" field="type" />
    </div>
    @if ($type === DonationType::RECURRENTE->value)
        <div class="my-6 flex flex-col w-full">
            <x-radiobutton-donacion
                name="frequency"
                :prefix="$prefix"
                :default="$frequency"
                :is-grouped="true"
                :options="[
                    [
                        'text' => 'Mensual',
                        'value' => DonationFrequency::MENSUAL->value,
                    ],
                    [
                        'text' => 'Trimestral',
                        'value' => DonationFrequency::TRIMESTRAL->value,
                    ],
                    [
                        'text' => 'Anual',
                        'value' => DonationFrequency::ANUAL->value,
                    ],
                ]"
            />

            <small class="text-gray-400 block w-full text-[10px]">
                Puedes cancelar en cualquier momento
            </small>
            <x-error class="form-error" field="frequency" />
        </div>
    @endif

    <div class="my-6 flex flex-col w-full">
        <x-radiobutton-donacion
            name="amount_select"
            :prefix="$prefix"
            :default="$amount_select"
            :options="[
                [
                    'text' => '10 €',
                    'value' => 10,
                ],
                [
                    'text' => '50 €' ,
                    'value' => 50,
                ],
                [
                    'text' => '100 €',
                    'value' => 100,
                ],
            ]"
        />
        <x-error class="form-error" field="amount_select" />
    </div>

    <div>
        <div
            class="card outline-azul-gray text-azul-mist has-[input:focus-within]:outline-azul-mist flex items-center p-2 shadow-none has-[input:focus-within]:outline-2"
        >
            <input
                type="text"

                name="amount"
                id="{{ $prefix . '-amount' }}"
                class="w-full border-0 p-1 text-end font-semibold shadow-none focus:border-0! focus:ring-0 focus:outline-0!"
                wire:model.live="amount"
            />
            <div
                class="text-azul-mist shrink-0 px-2 text-base font-semibold select-none"
            >
                €
            </div>
        </div>
        <small class="text-primary block w-full text-[11px]">
            O si lo prefieres, puedes escribir otra cantidad
        </small>

        <x-error class="form-error" field="amount" />
    </div>

    <div class="my-6">
        <button
            class="btn bg-amarillo text-azul-mist! hover:bg-amarillo/70 flex w-full cursor-pointer font-semibold"
            wire:click="toStep(2)"
        >
            <span class="mx-auto flex items-center">
                Hacer una donacion
                <x-bi-arrow-right-short class="h-7 w-7" />
            </span>
        </button>
    </div>
</div>
