<?php

use Database\Seeders\PagesSeeder;

it('returns a successful response', function () {

    $this->seed(PagesSeeder::class);
    $response = $this->get(route('home'));
    $response->assertStatus(200);

});

test('copio bien las imagenes en seeder', function () {

    Storage::fake('public');

    $images = [
        'elena.jpeg' => '01JRAKKVNNB0KSC82FS11STZHH.jpeg',
        'banner.jpg' => '01JRD1HM2Z5ABMG838T2G2QQJ2.jpg',
        'icons/social-support.svg' => '01JRB7NT8FD44KXSM8NEM6CFVX.svg',
        'icons/open-book.svg' => '01JRB7NT8J91CXTR18NG8WEHAH.svg',
        'icons/calendar.svg' => '01JRB7NT8J91CXTR18NG8WEHAJ.svg',
        'icons/rocket.svg' => '01JRCW8ANN7E4DMTR0AGR6PQN3.svg',
        'icons/heart-box.svg' => '01JRFM4781M7HPNT7BTF2WGW69.svg',
        'icons/global.svg' => '01JRFM47833PMEE0JPNXHCEFF0.svg',
        'icons/heart-box2.svg' => '01JRFM4784AQEGA233AC85YTZ3.svg',
    ];

    // Copio estas imagenes a la carpeta de storage
    foreach ($images as $name => $image) {

        copy(base_path('public/images/'.$name), public_path('storage/'.$image));
        expect(file_exists(public_path('storage/'.$image)))->toBeTrue();
    }

});
