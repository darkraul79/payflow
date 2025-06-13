@php
    use App\Models\Address;
@endphp

@foreach ($record->addresses as $address)
    <x-filament::section
        class="mb-6 gap-2 mt-5 {{ $record->addresses->count()>1?' w-1/2 ': 'w-full' }} text-xs"
    >
        <x-slot name="description">
            <span
                class="flex flex-row items-center gap-1 font-semibold text-gray-400!"
            >
                @if ($address->type === Address::CERTIFICATE)
                    <x-heroicon-m-check-badge class="fill-lime h-6 w-6" />
                    {{ $address->type }}
                @else
                    Dirección de {{ $address->type }}
                @endif
            </span>
        </x-slot>

        <div class="flex flex-col">
            <div class="ms-auto flex flex-col">
                @if ($address->company)
                    <span class="text-sm font-semibold">
                        {{ $address->company }}
                    </span>
                @endif

                @if ($address->nif)
                    <span class="ms-3 mb-4 text-sm text-gray-600">
                        {{ $address->nif }}
                    </span>
                @endif
            </div>
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold">
                    {!! $address->full_name !!}
                </div>
            </div>

            <div class="my-1 mb-2">
                {{ $address->address }}.
                <br />
                {{ $address->cp }} - {{ $address->city }}.
                {{ $address->province }}.
            </div>

            <div class="my-1 mt-2 flex items-start justify-between gap-1">
                <div class="flex-basis-1/2">
                    <span class="font-semibold">Email:</span>
                    <span class="font-normal">
                        {{ $address->email }}
                    </span>
                </div>
                <div class="">
                    @if ($address->phone)
                        <span class="font-semibold">Teléfono:</span>
                        <span class="font-normal">
                            {{ $address->phone }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- @include('filament.resources.order-resource.pages.order-info') --}}
    </x-filament::section>
@endforeach
