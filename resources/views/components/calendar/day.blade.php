<div
    ondragenter="onLivewireCalendarEventDragEnter(event, '{{ $componentId }}', '{{ $day }}', '{{ $dragAndDropClasses }}');"
    ondragleave="onLivewireCalendarEventDragLeave(event, '{{ $componentId }}', '{{ $day }}', '{{ $dragAndDropClasses }}');"
    ondragover="onLivewireCalendarEventDragOver(event);"
    ondrop="onLivewireCalendarEventDrop(event, '{{ $componentId }}', '{{ $day }}', {{ $day->year }}, {{ $day->month }}, {{ $day->day }}, '{{ $dragAndDropClasses }}');"
    class="border-azul-cobalt -mt-px -ml-px aspect-square h-15 w-full border md:aspect-auto"
>
    {{-- Wrapper for Drag and Drop --}}
    <div class="h-full w-full" id="{{ $componentId }}-{{ $day }}">
        <div
            @if ($dayClickEnabled)
                wire:click="onDayClick({{ $day->year }}, {{ $day->month }}, {{ $day->day }})"
            @endif
            class="{{ $dayInMonth ? ($isToday ? "bg-azul-sky" : ($events->count() ? " bg-azul-mist text-white " : " bg-white ")) : "bg-azul-cobalt/20" }} relative flex h-full w-full flex-col items-center justify-center p-0.5 md:p-2"
        >
            @if ($events->isEmpty())
                {{-- Number of Day --}}
                <div class="flex items-center justify-center text-center">
                    <p
                        class="{{ $dayInMonth ? "   " : " text-azul-gray " }} mb-0 block h-full w-full text-center text-sm"
                    >
                        {{ $day->format("j") }}
                    </p>
                </div>
            @endif

            {{-- Events --}}
            <div
                class="absolute bottom-0 left-0 flex h-full w-full flex-1 flex-col items-center justify-center md:p-2"
            >
                <div class="group relative">
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
                        <span
                            class="bg-amarillo text-azul-mist absolute left-1/2 z-30 m-4 mx-auto -translate-x-1/2 rounded-md px-2 text-sm text-nowrap opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            {{ $event["title"] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
