<?php

/** @noinspection PhpUndefinedMethodInspection */

/** @noinspection PhpUndefinedMethodInspection */

use function Pest\Livewire\livewire;

test('puedo crear una nueva actividad noticia, y proyecto', function ($model) {

    Storage::fake('public');
    $modelClass = "App\\Models\\$model";
    $livewireClass = "App\\Filament\\Resources\\{$model}Resource\\Pages\\Create$model";

    $actividad = $modelClass::factory()->make();

    asUser();

    livewire($livewireClass)
        ->fillForm($actividad->toArray())
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    expect($modelClass::count())->toBe(1);
})->with([
    'Activity',
    'News',
    'Proyect',
]);

test('puedo editar una actividad', function ($model) {
    Storage::fake('public');

    $modelClass = "App\\Models\\$model";
    $livewireClass = "App\\Filament\\Resources\\{$model}Resource\\Pages\\Edit$model";

    $actividad = $modelClass::factory()->create();

    livewire($livewireClass, ['record' => $actividad->getRouteKey()])
        ->fillForm([
            'title' => 'Actividad editada',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    expect($modelClass::find(1)->title)->toBe('Actividad editada');
})->with([
    'Activity',
    'News',
    'Proyect',
]);

test('urlPrefix es correcto', function ($model, $urlPrefix) {
    expect($model::factory()->make()->getUrlPrefix())->toBe($urlPrefix);

})->with([
    [
        'App\\Models\\Activity',
        '/actualidad/actividades/',
    ],
    [
        'App\\Models\\News',
        '/actualidad/noticias/',
    ],
    [
        'App\\Models\\Proyect',
        '/que-hacemos/proyectos/',
    ],
]);
