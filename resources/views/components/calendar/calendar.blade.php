@php
    use Illuminate\Support\Carbon;
@endphp

<div
    @if ($pollMillis !== null && $pollAction !== null)
        wire:poll.{{ $pollMillis }}ms="{{ $pollAction }}"
    @elseif ($pollMillis !== null)
        wire:poll.{{ $pollMillis }}ms
    @endif
    class="ms-auto lg:max-w-xl"
>
    <div>
        @includeIf($beforeCalendarView)
    </div>
    <div class="mb-5 flex items-center justify-between md:mb-10 md:px-10">
        <div class="text-xl font-semibold text-black">
            {{-- Month and Year --}}
            {{ Str::title(Carbon::parse($this->startsAt)->translatedFormat("F Y")) }}
        </div>
        <div class="flex gap-10">
            <button
                wire:click="goToPreviousMonth()"
                class="hover:bg-azul-sky hover:fill-azul-mist w-8 cursor-pointer rounded-full p-1.5"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 40 40"
                    class="h-auto w-full rotate-180"
                    focusable="false"
                >
                    <path
                        d="m15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z"
                    ></path>
                </svg>
            </button>
            <button
                wire:click="goToNextMonth()"
                class="hover:bg-azul-sky hover:fill-azul-mist w-8 cursor-pointer rounded-full p-1.5"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 40 40"
                    focusable="false"
                    class="h-auto w-full"
                >
                    <path
                        d="m15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z"
                    ></path>
                </svg>
            </button>
        </div>
    </div>

    <div class="flex">
        <div class="w-full">
            <div class="flex w-full flex-row">
                @foreach ($monthGrid->first() as $day)
                    @include($dayOfWeekView, ["day" => $day])
                @endforeach
            </div>

            @foreach ($monthGrid as $week)
                <div class="grid grid-cols-7">
                    @foreach ($week as $day)
                        @include(
                            $dayView,
                            [
                                "componentId" => $componentId,
                                "day" => $day,
                                "dayInMonth" => $day->isSameMonth($startsAt),
                                "isToday" => $day->isToday(),
                                "events" => $getEventsForDay($day, $events),
                            ]
                        )
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <div>
        @includeIf($afterCalendarView)
    </div>
</div>
