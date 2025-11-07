<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('authenticated users can visit the dashboard', function () {
    asUser();

    $this->get('/admin/menus')->assertStatus(200);
});
