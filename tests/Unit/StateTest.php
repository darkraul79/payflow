<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Sequence;

test('puedo crear estados en diferentes modelos', function ($modelo) {

    $modelClass = "App\\Models\\$modelo";
    $model = $modelClass::factory()->hasStates()->create();
    expect($model->state)->toBeInstanceOf(State::class)
        ->and($model->state->stateable_type)->toBe($modelClass)
        ->and($model->state->stateable_id)->toBe($model->id);

})->with([
    'Order',
    'Donation',
]);

test('el metodo state devuelve el último estado del modelo', function ($modelo) {
    $modelClass = "App\\Models\\$modelo";
    $model = $modelClass::factory()->hasStates(new Sequence(
        ['name' => State::PENDIENTE],
        ['name' => State::ACEPTADO],
    ))->create();

    $this->travel(1)->days();
    $model->states()->create([
        'name' => State::FINALIZADO,
    ]);
    expect($model->state->name)->toBe(State::FINALIZADO)
        ->and($model->states)->toHaveCount(3);

})->with([
    'Order',
    'Donation',
]);
