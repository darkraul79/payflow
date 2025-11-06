<?php

use App\Filament\Resources\DonationResource;
use App\Filament\Resources\DonationResource\Pages\Listdonations;
use App\Models\Donation;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('redirige a login si el invitado visita el índice de DonationResource', function () {
    get(DonationResource::getUrl())
        ->assertRedirect('/admin/login');
});

it('un usuario con acceso al panel puede ver el índice de DonationResource', function () {
    asUser();

    get(DonationResource::getUrl())
        ->assertOk();
});

it('Listdonations muestra registros y permite buscar por número y ordenar por importe y fecha', function () {
    asUser();

    // Creamos 3 donaciones con importes distintos
    $d1 = Donation::factory()->create(['amount' => 5.00]);
    $d2 = Donation::factory()->create(['amount' => 15.50]);
    $d3 = Donation::factory()->create(['amount' => 10.00]);

    // Aseguramos números distintos para búsqueda
    expect($d1->number)->not()->toBe($d2->number);

    // Puede ver y buscar por número
    livewire(Listdonations::class)
        ->assertCanSeeTableRecords([$d1, $d2, $d3])
        ->searchTable($d2->number)
        ->assertCanSeeTableRecords([$d2])
        ->assertCanNotSeeTableRecords([$d1, $d3]);

    // Limpiar búsqueda y ordenar por amount asc/desc
    livewire(Listdonations::class)
        ->searchTable('')
        ->sortTable('amount')
        ->sortTable('amount', 'desc')
        ->sortTable('created_at', 'desc'); // verifica que la ordenación se puede aplicar sin errores
});

it('La acción cancelar es visible solo para donación recurrente activa', function () {
    asUser();

    $unica = Donation::factory()->create(['type' => Donation::UNICA]);
    $recurrenteActiva = Donation::factory()->recurrente()->activa()->create();

    // En la tabla, la acción "cancelar" debería ser visible para la recurrente activa
    livewire(Listdonations::class)
        ->assertTableActionHidden('cancelar', $unica)
        ->assertTableActionVisible('cancelar', $recurrenteActiva);
});
