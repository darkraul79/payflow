<?php

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Order;

use function Pest\Livewire\livewire;

it('redirige a login para invitado y permite acceso a usuario', function () {
    // Invitado -> redirección al login del panel
    $this->get(OrderResource::getUrl('index'))
        ->assertRedirectContains('/admin/login');

    // Usuario con acceso -> 200 OK
    asUser();
    $this->get(OrderResource::getUrl('index'))
        ->assertOk();
});

it('ListOrders muestra registros y permite buscar por estado y ordenar por fecha', function () {
    asUser();

    // Creamos tres pedidos con distintos estados utilizando las factories helper
    $pagado = Order::factory()->pagado()->create(['number' => 'ORD-PAG-'.fake()->unique()->randomNumber(5)]);
    $enviado = Order::factory()->enviado()->create(['number' => 'ORD-ENV-'.fake()->unique()->randomNumber(5)]);
    $error = Order::factory()->error()->create(['number' => 'ORD-ERR-'.fake()->unique()->randomNumber(5)]);

    // Sin filtros, debe poder ver los registros
    livewire(ListOrders::class)
        ->assertCanSeeTableRecords([$pagado, $enviado, $error])
        // Buscar por nombre del estado (columna searchable: state.name)
        ->searchTable(OrderStatus::PAGADO->value)
        ->assertCanSeeTableRecords([$pagado])
        ->assertCanNotSeeTableRecords([$enviado, $error])
        // Ordenar por fecha de actualización (columna sortable: updated_at)
        ->sortTable('updated_at')
        ->sortTable('updated_at', 'asc');
});
