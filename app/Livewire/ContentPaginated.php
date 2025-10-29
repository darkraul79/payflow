<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\Cart;
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

    public string $sortBy;

    public string $gridClass = 'md:grid-cols-2 md:gap-6 md:space-y-0  lg:grid-cols-3 lg:gap-8';

    public string $sortDirection;

    public function setSortBy(string $value): void
    {
        $this->sortBy = $value;
        $this->resetPage();
    }

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
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
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
        $modelClass = resolve('App\\Models\\' . $this->type);

        switch ($this->filter) {
            default:
            case 'latest':
                $query = $modelClass::query()->latest_activities();
                break;
            case 'next_activities':
                $query = $modelClass::query()->next_activities();
                break;
            case 'manual':
                $query = $modelClass::query()->manual(ids: $this->ids);
                break;
            case 'all':
                $query = $modelClass::query()->all_activities();
                break;
        }

        // Aplicar ordenamiento genérico sobre la query resultante
        $sortBy = explode(',', $this->sortBy);
        $orderField = $sortBy[0];
        $direction = $sortBy[1] ?? 'desc';
        if (in_array($this->sortBy, ['price,asc', 'price,desc']) && method_exists($modelClass, 'scopeOrderByEffectivePrice')) {
            $query->orderByEffectivePrice($direction);
        } else {
            $allowed = ['name', 'title', 'created_at', 'updated_at'];
            $column = in_array($orderField, $allowed) ? $orderField : 'created_at';
            $query->orderBy($column, $direction ?? 'desc');
        }

        return $query->paginate($this->perPage);
    }

    // Resetear paginación al cambiar sortBy
    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function addToCart(Product $product): void
    {

        if ((Cart::getQuantityProduct($product->id) + 1) > $product->stock) {
            $this->dispatch('showAlert', type: 'error', title: 'No se puede agregar el producto', message: 'No hay suficiente stock');
        } else {
            Cart::addItem($product);
            $this->dispatch('updatedCart');
            $this->dispatch('showAlert', type: 'success', title: 'Producto agregado', message: 'El producto ha sido agregado al carrito.');
        }
    }
}
