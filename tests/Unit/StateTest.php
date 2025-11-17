<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Enums\OrderStatus;
use App\Models\State;

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
    if ($modelClass === 'App\\Models\\Donation') {
        $model = $modelClass::factory()->activa()->create();
    } else {
        $model = $modelClass::factory()->create();
    }
    $this->travel(1)->days();
    $model->states()->create([
        'name' => OrderStatus::FINALIZADO->value,
    ]);

    expect($model->state->name)->toBe(OrderStatus::FINALIZADO->value)
        ->and($model->states)->toHaveCount(2);

})->with([
    'Order',
    'Donation',
]);
