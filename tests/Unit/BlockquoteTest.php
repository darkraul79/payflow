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

    $home = Page::factory()->create([
        'slug' => 'home',
        'title' => 'Home',
        'is_home' => true,
    ]);


    $this->get('/')
        ->assertSee($home->title)
        ->assertDontSee('blockquotes');

});


test('si no hay frases no devuelve error', function () {
    Page::factory()->create([
        'slug' => 'home',
        'title' => 'Home',
        'is_home' => true,
    ]);

    $this->get('/')->assertOk();
});
