@if ($info)
    <div
        {{ $attributes->class(['my-2 flex flex-row justify-end']) }}
        x-data="{ openInfo: false }"
    >
        <div
            class="text-secondary left-6 z-20 flex w-full flex-col rounded-lg bg-zinc-100 p-3 text-[11px] text-wrap text-gray-600 italic"
            x-show="openInfo"
            @click.away="openInfo = false"
            x-cloak
        >
            @if (is_object($info))
                @foreach ($info as $key => $value)
                    @if (Str::lower($key) == 'error')
                        <span class="text-secondary text-error block text-sm">
                            <strong>{{ $key }}:</strong>
                            {{ $value }}
                        </span>
                    @else
                        <span class="text-secondary block">
                            <strong>{{ $key }}:</strong>
                            {{ is_array($value) ? json_encode($value) : $value }}
                        </span>
                    @endif
                @endforeach
            @endif
        </div>
        <x-bi-info-circle
            x-on:click="openInfo = ! openInfo"
            class="text-primary mx-2 inline h-4 w-4 cursor-pointer"
        />
        {{ $slot }}
    </div>
@endif
