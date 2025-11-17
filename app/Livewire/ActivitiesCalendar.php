<?php

namespace App\Livewire;

use App\Models\Activity;
use Illuminate\Support\Collection;
use Omnia\LivewireCalendar\LivewireCalendar;

class ActivitiesCalendar extends LivewireCalendar
{
    //    public $dayOfWeekView = 'components/calendar/day-of-week.blade.php';

    public function events(): Collection
    {
        return Activity::query()
            ->get()
            ->map(function (Activity $activity) {
                return [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'description' => $activity->getResume(),
                    'date' => $activity->getDateCalendar(),
                    'url' => $activity->getLink(),
                ];
            });

    }

    public function onEventClick($eventId): void
    {
        $this->redirect($eventId);
    }
}
