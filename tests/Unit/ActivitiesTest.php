<?php

/** @noinspection PhpUndefinedMethodInspection */

/** @noinspection PhpUndefinedMethodInspection */

use App\Models\Activity;

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

test('latest_activities devuelve las últimas actividades ordenadas por fecha del evento', function () {

    $actividadUltima = Activity::factory()->create(['date' => '2023-10-01']);
    $actividadPrimera = Activity::factory()->create(['date' => '2023-11-01']);

    $actividades = Activity::latest_activities()->get();

    expect($actividades->count())->toBe(2)
        ->and($actividades->first()->id)->toBe($actividadPrimera->id)
        ->and($actividades->last()->id)->toBe($actividadUltima->id);

});
