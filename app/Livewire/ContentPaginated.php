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

    public function mount($filter = 'latest', $typeContent = 'Activity', $ids = [], $perPage = 10): void
    {
        $this->filter = $filter;
        $this->type = $typeContent;
        $this->ids = $ids;
        $this->perPage = $perPage;

    }

    public function render(): View
    {

        return view('components.content-paginated', [
            'data' => $this->getData(),
        ]);
    }

    public function getData()
    {

        switch ($this->filter) {
            default:
            case 'latest':
                return resolve('App\\Models\\'.$this->type)::query()
                    ->latest_activities()
                    ->paginate($this->perPage);
            case 'next_activities':
                return resolve('App\\Models\\'.$this->type)::query()
                    ->next_activities()
                    ->paginate($this->perPage);
            case 'manual':
                return resolve('App\\Models\\'.$this->type)::query()
                    ->published()
                    ->whereIn('id', $this->ids)
                    ->orderBy('date', 'desc')
                    ->paginate($this->perPage);
        }
    }
}
