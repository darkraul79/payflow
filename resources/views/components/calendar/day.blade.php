<div
    ondragenter="onLivewireCalendarEventDragEnter(event, '{{ $componentId }}', '{{ $day }}', '{{ $dragAndDropClasses }}');"
    ondragleave="onLivewireCalendarEventDragLeave(event, '{{ $componentId }}', '{{ $day }}', '{{ $dragAndDropClasses }}');"
    ondragover="onLivewireCalendarEventDragOver(event);"
    ondrop="onLivewireCalendarEventDrop(event, '{{ $componentId }}', '{{ $day }}', {{ $day->year }}, {{ $day->month }}, {{ $day->day }}, '{{ $dragAndDropClasses }}');"
    class="border-azul-cobalt -mt-px -ml-px aspect-square border"
>
    {{-- Wrapper for Drag and Drop --}}
    <div class="h-full w-full" id="{{ $componentId }}-{{ $day }}">
        <div
            @if ($dayClickEnabled)
                wire:click="onDayClick({{ $day->year }}, {{ $day->month }}, {{ $day->day }})"
            @endif
            class="{{ $dayInMonth ? ($isToday ? "bg-azul-sky" : ($events->count() ? " bg-azul-mist text-white " : " bg-white ")) : "bg-azul-cobalt/20" }} relative flex h-full w-full flex-col p-0.5 md:p-2"
        >
            {{-- Number of Day --}}
            <div class="flex items-center">
                <p
                    class="{{ $dayInMonth ? "   " : " text-azul-gray " }} mb-0 text-sm"
                >
                    {{ $day->format("j") }}
                </p>
            </div>

            {{-- Events --}}
            <div
                class="absolute bottom-0 left-0 flex h-full w-full flex-1 flex-col items-end justify-end md:p-2"
            >
                @foreach ($events as $event)
                    <div
                        @if ($dragAndDropEnabled)
                            draggable="true"
                        @endif
                        ondragstart="onLivewireCalendarEventDragStart(event, '{{ $event["id"] }}')"
                        class="block"
                    >
                        @include(
                            $eventView,
                            [
                                "event" => $event,
                            ]
                        )
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
