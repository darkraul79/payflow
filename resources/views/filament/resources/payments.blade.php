<x-filament::section
    icon="bi-cash-stack"
    heading="Pagos"
    :compact="true"
    class="w-full"
>
    <ul>
        @foreach ($record->payments as $payment)
            <li
                class="block overflow-x-auto border-gray-400 py-3 not-last:border-b-1! lg:px-6"
            >
                <div
                    class="relative z-10 flex flex-row items-center justify-between"
                >
                    <div>
                        <span class="block text-xs font-semibold">
                            {{ $payment->number }}
                        </span>
                        <span class="block text-xs text-gray-400 italic">
                            {{ $payment->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    <span class="text-azul-sea font-base font-semibold">
                        {{ convertPrice($payment->amount) }}
                    </span>
                </div>
                <x-info-bullet-collapsible
                    :info="$payment->info"
                    id="{{$payment->id}}"
                />
            </li>
        @endforeach
    </ul>
</x-filament::section>
