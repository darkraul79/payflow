<?php

namespace Tests\Models;

use App\Models\Blockquote;
use App\Models\Page;
use Database\Seeders\BlockquotesSeeder;

test('get random devuelve una frase', function () {
    $this->seed(BlockquotesSeeder::class);

    $random = Blockquote::getRandom();

    expect($random)->toBeString();
})->group('models', 'blockquotes');

test('si no hay frases el bloque blanco no aparece', function () {

    Page::factory()->create([
        'slug' => 'home',
        'title' => 'Home',
        'is_home' => true,
    ]);

    $this->get('/')
        ->assertDontSeeHtml('<blockquotes');

});

test('si no hay frases no devuelve error', function () {
    Page::factory()->create([
        'slug' => 'home',
        'title' => 'Home',
        'is_home' => true,
    ]);

    $this->get('/')->assertOk();
});

test('puedo asignar una frase a una página', function () {

    $pagina = Page::factory()->published()->create();
    $block = Blockquote::factory()->create();
    $pagina->blockquotes()->sync($block);

    expect($pagina->blockquotes()->count())->toBe(1)
        ->and($block->pages()->count())->toBe(1);
});
