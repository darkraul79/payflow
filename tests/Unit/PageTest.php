<?php

use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Models\Page;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

test('puedo crear páginas', function () {

    Storage::fake('public');

    $page = Page::factory()->make();

    asUser();

    livewire(CreatePage::class)
        ->fillForm([...$page->getAttributes(), 'layout' => 'default'])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    expect(Page::count())->toBe(1);
});

test('puedo editar páginas', function () {

    Storage::fake('public');

    $page = Page::factory()->create();

    asUser();

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'title' => 'Pagina editada',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    expect(Page::first()->title)->toBe('Pagina editada');
});
