<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ContentPaginated extends Component
{
    use WithPagination;

    public $filter;

    public $type;

    public $ids;

    public $perPage;

    public string $gridClass = 'md:grid-cols-2 md:gap-6 md:space-y-0  lg:grid-cols-3 lg:gap-8';

    public function mount($filter = 'latest', $typeContent = 'Activity', $ids = [], $perPage = 10): void
    {
        $this->filter = $filter;
        $this->type = $typeContent;
        $this->ids = $ids;
        $this->perPage = $perPage;
        $this->gridClass = match ($this->type) {
            'Product' => 'md:grid-cols-2 md:gap-6 md:space-y-0  lg:grid-cols-4 lg:gap-8',
            default => $this->gridClass,
        };

    }

    public function render(): View
    {

        return view('components.content-paginated', [
            'type' => $this->type,
            'data' => $this->getData(),
        ]);
    }

    public function getData()
    {

        switch ($this->filter) {
            default:
            case 'latest':
                return resolve('App\\Models\\' . $this->type)::query()
                    ->latest_activities()
                    ->paginate($this->perPage);
            case 'next_activities':
                return resolve('App\\Models\\' . $this->type)::query()
                    ->next_activities()
                    ->paginate($this->perPage);
            case 'manual':
                return resolve('App\\Models\\' . $this->type)::query()
                    ->manual(ids: $this->ids)
                    ->paginate($this->perPage);
            case 'all':
                return resolve('App\\Models\\' . $this->type)::query()
                    ->all_activities()
                    ->paginate($this->perPage);
        }
    }
}
