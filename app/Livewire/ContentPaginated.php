<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ContentPaginated extends Component
{
    use WithPagination;

    /** @var string Filtro seleccionado */
    public string $filter;

    /** @var string Nombre del modelo (sin el namespace) */
    public string $type;

    /** @var array<int,int|string> IDs usados en modo manual */
    public array $ids = [];

    /** @var int Número de elementos por página */
    public int $perPage;

    /** @var string Campo y dirección (ej: created_at, price-asc) */
    public string $sortBy;

    /** @var string Clases CSS de la rejilla */
    public string $gridClass = 'md:grid-cols-2 md:gap-6 md:space-y-0  lg:grid-cols-3 lg:gap-8';

    /** @var string Dirección principal usada inicialmente */
    public string $sortDirection;

    /**
     * Inicializa el componente.
     */
    public function mount(
        string $filter = 'latest',
        string $typeContent = 'Activity',
        array $ids = [],
        int $perPage = 10
    ): void {
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

    /**
     * Renderiza la vista.
     */
    public function render(): View
    {
        return view('components.content-paginated', [
            'type' => $this->type,
            'data' => $this->getData(),
        ]);
    }

    /**
     * Obtiene los registros paginados según filtro y orden.
     */
    public function getData(): LengthAwarePaginator
    {
        $modelClass = 'App\\Models\\'.$this->type;
        /** @var class-string $modelClass */
        $model = resolve($modelClass);

        // Construir la query base según el filtro.
        $query = $this->buildFilteredQuery($modelClass);

        // Aplicar ordenamiento.
        $this->applySorting($query, $modelClass);

        return $query->paginate($this->perPage);
    }

    /**
     * Construye la query base dependiendo del filtro.
     */
    private function buildFilteredQuery(string $modelClass)
    {
        return match ($this->filter) {
            'next_activities' => $modelClass::query()->next_activities(),
            'manual' => $modelClass::query()->manual(ids: $this->ids),
            'all' => $modelClass::query()->all_activities(),
            default => $modelClass::query()->latest_activities(),
        };
    }

    /**
     * Aplica el ordenamiento requerido a la query.
     */
    private function applySorting($query, string $modelClass): void
    {
        $sortParts = explode('-', $this->sortBy);
        $orderField = $sortParts[0];
        $direction = $sortParts[1] ?? 'desc';

        // Ordenamiento por precio efectivo si está disponible.
        if (in_array($this->sortBy, ['price-asc', 'price-desc'], true) && method_exists($modelClass,
            'scopeOrderByEffectivePrice')) {
            $query->orderByEffectivePrice($direction);

            return;
        }

        $allowed = ['name', 'title', 'created_at', 'updated_at'];
        $column = in_array($orderField, $allowed, true) ? $orderField : 'created_at';
        $query->orderBy($column, $direction);
    }

    /**
     * Cambia el criterio de orden y reinicia la paginación.
     */
    public function setSortBy(string $value): void
    {
        $this->sortBy = $value;
        $this->resetPage();
    }

    /**
     * Hook de Livewire al actualizar el sortBy desde la vista.
     */
    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    /**
     * Añade un producto al carrito validando stock.
     */
    public function addToCart(Product $product): void
    {
        $cantidadActual = Cart::getQuantityProduct($product->id);
        if (($cantidadActual + 1) > $product->stock) {
            $this->dispatch('showAlert', type: 'error', title: 'No se puede agregar el producto',
                message: 'No hay suficiente stock');

            return;
        }

        Cart::addItem($product);
        $this->dispatch('updatedCart');
        $this->dispatch('showAlert', type: 'success', title: 'Producto agregado',
            message: 'El producto ha sido agregado al carrito.');
    }
}
