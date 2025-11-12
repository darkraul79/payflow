<?php

use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Filament\Resources\PageResource\Pages\ListPages;
use App\Models\Page;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('redirige a login si el invitado visita el índice de PageResource', function () {
    get(PageResource::getUrl('index'))
        ->assertRedirect('/admin/login');
});

it('un usuario con acceso al panel puede ver el índice de PageResource', function () {
    asUser();

    get(PageResource::getUrl('index'))
        ->assertOk();
});

it('CreatePage válida para campos requeridos y la regla del slug', function () {
    asUser();

    // Requeridos vacíos
    livewire(CreatePage::class)
        ->fillForm([
            'title' => '',
            'slug' => '',
            'layout' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'title' => null,
            'slug' => null,
            'layout' => null,
        ]);

    // Slug no debe empezar con '/'
    livewire(CreatePage::class)
        ->fillForm([
            'title' => 'Titulo X',
            'slug' => '/inicio',
            'layout' => 'default',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'slug' => null,
        ]);

    // Slug no debe terminar con '/'
    livewire(CreatePage::class)
        ->fillForm([
            'title' => 'Titulo Y',
            'slug' => 'inicio/',
            'layout' => 'default',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'slug' => null,
        ]);
});

it('CreatePage impide duplicar slug (único por parent)', function () {
    asUser();

    $existing = Page::factory()->create([
        'slug' => 'duplicado',
    ]);

    livewire(CreatePage::class)
        ->fillForm([
            'title' => 'Otra página',
            'slug' => $existing->slug,
            'layout' => 'default',
            'parent_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'slug' => null,
        ]);
});

it('EditPage impide cambiar a un slug ya usado por otra página', function () {
    asUser();

    $a = Page::factory()->create(['title' => 'A', 'slug' => 'a']);
    $b = Page::factory()->create(['title' => 'B', 'slug' => 'b']);

    livewire(EditPage::class, ['record' => $b->getKey()])
        ->fillForm([
            'slug' => $a->slug,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'slug' => null,
        ]);
});

it('ListPages permite buscar por título y ordenar por título', function () {
    asUser();

    $alpha = Page::factory()->create(['title' => 'Alpha', 'slug' => 'alpha']);
    $bravo = Page::factory()->create(['title' => 'Bravo', 'slug' => 'bravo']);
    $charlie = Page::factory()->create(['title' => 'Charlie', 'slug' => 'charlie']);

    // Puede ver los registros y buscar por título
    livewire(ListPages::class)
        ->assertCanSeeTableRecords([$alpha, $bravo, $charlie])
        ->searchTable('Bravo')
        ->assertCanSeeTableRecords([$bravo])
        ->assertCanNotSeeTableRecords([$alpha, $charlie]);

    // Limpiamos búsqueda y probamos ordenación ascendente
    livewire(ListPages::class)
        ->searchTable('')
        ->sortTable('title')
        ->assertSeeInOrder(['Alpha', 'Bravo', 'Charlie'])
        // Orden descendente por título
        ->sortTable('title', 'desc')
        ->assertSeeInOrder(['Charlie', 'Bravo', 'Alpha']);
});
