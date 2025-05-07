@aware(['page'])
<div class="flex flex-col gap-5 px-4 py-4 md:py-8 lg:flex-row">
    <div
        class="bg-azul-sky text-azul-mist flex w-full items-center justify-center px-4 py-4 lg:max-w-1/2 lg:px-6"
    >
        <div>
            <x-basic
                :title="$attributes['title']"
                :subtitle="$attributes['subtitle']"
                :text="$attributes['text']"
            />
        </div>
    </div>

    <div class="ms-auto w-full flex-1">
        <livewire:activities-calendar
            :day-click-enabled="false"
            :event-click-enabled="true"
            :drag-and-drop-enabled="false"
            week-starts-at="1"
            day-of-week-view="components/calendar/day-of-week"
            day-view="components/calendar/day"
            calendar-view="components/calendar/calendar"
            event-view="components/calendar/event"
        />
    </div>
</div>
