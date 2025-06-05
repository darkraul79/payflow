@php
    use App\Models\Donation;
@endphp

<div class="{{ $step === 1 ? 'block' : 'hidden' }}">
    <h5 class="text- px-6 text-center text-xl text-pretty">
        !Dona a la fundación ELENA TERTRE!
    </h5>

    <div class="my-6 flex w-full">
        <x-radiobutton-donacion
            name="type"
            :default="$type"
            :options="[
                [
                    'text' => 'Donación única',
                    'value' => Donation::UNICA,
                ],
                [
                    'text' => 'Hazte Socio',
                    'value' => Donation::RECURRENTE,
                ],
            ]"
        />
    </div>
    @if ($type === Donation::RECURRENTE)
        <div class="my-6 flex w-full" wire:model="frequency">
            <x-radiobutton-donacion
                name="frequency"
                :default="$frequency"
                :options="[
                    [
                        'text' => 'Mensual',
                        'value' => Donation::FREQUENCY['MENSUAL'],
                    ],
                    [
                        'text' => 'Trimestral',
                        'value' => Donation::FREQUENCY['TRIMESTRAL'],
                    ],
                    [
                        'text' => 'Anual',
                        'value' => Donation::FREQUENCY['ANUAL'],
                    ],
                ]"
            />
        </div>
    @endif

    <div class="my-6 flex w-full" wire:model="amount_select">
        <x-radiobutton-donacion
            name="amount_select"
            :default="$amount_select"
            :options="[
                [
                    'text' => '10',
                    'value' => 10,
                ],
                [
                    'text' => '50',
                    'value' => 50,
                ],
                [
                    'text' => '100',
                    'value' => 100,
                ],
            ]"
        />
    </div>

    <div>
        <div
            class="card outline-azul-gray text-azul-mist has-[input:focus-within]:outline-azul-mist flex items-center p-2 shadow-none has-[input:focus-within]:outline-2"
        >
            <input
                type="text"
                name="amount"
                id="amount"
                class="w-full border-0 p-1 text-end font-semibold shadow-none focus:border-0! focus:ring-0 focus:outline-0!"
                wire:model.live="amount"
            />
            <div
                class="text-azul-mist shrink-0 px-2 text-base font-semibold select-none"
            >
                €
            </div>
        </div>
        <small class="text-azul-gray block w-full text-[11px]">
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
