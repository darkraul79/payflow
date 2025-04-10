<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    public function run(): void
    {

        $images = [
            'elena.jpeg' => '01JRAKKVNNB0KSC82FS11STZHH.jpeg',
            'banner.jpg' => '01JRD1HM2Z5ABMG838T2G2QQJ2.jpg',
            'icons/social-support.svg' => '01JRB7NT8FD44KXSM8NEM6CFVX.svg',
            'icons/open-book.svg' => '01JRB7NT8J91CXTR18NG8WEHAH.svg',
            'icons/calendar.svg' => '01JRB7NT8J91CXTR18NG8WEHAJ.svg',
            'icons/rocket.svg' => '01JRCW8ANN7E4DMTR0AGR6PQN3.svg',
            'icons/heart-box.svg' => '01JRFT38JJQXF7ZBDDA161BZ8R.svg',
            'icons/global.svg' => '01JRFT38JMHH5DXEKJQ7PHYZHW.svg',
            'icons/heart-box2.svg' => '01JRFT38JMHH5DXEKJQ7PHYZHX.svg',
        ];

        // Copio estas imagenes a la carpeta de storage
        foreach ($images as $name => $image) {

            copy(base_path('public/images/' . $name), public_path('storage/' . $image));
        }

        Page::factory()
            ->isHome()
            ->published()
            ->create([
                'title' => 'Home',
                'slug' => 'home',
                'blocks' => json_decode('[{"type":"slider","data":{"sliders":[{"image":"01JRAKKVNNB0KSC82FS11STZHH.jpeg","title":"\u201cEl momento es Hoy\u201d","content":"<p>D\u00e9jate llevar por la canci\u00f3n de Elena, un canto profundo al valor de la vida. Sus palabras, llenas de poder y significado, son el legado que nos dej\u00f3 y la inspiraci\u00f3n que gu\u00eda nuestra misi\u00f3n.&nbsp;<\/p>","align":"left"},{"image":"01JRAKKVNNB0KSC82FS11STZHH.jpeg","title":"\u201cEl momento es Hoy\u201d","content":"<p>D\u00e9jate llevar por la canci\u00f3n de Elena, un canto profundo al valor de la vida. Sus palabras, llenas de poder y significado, son el legado que nos dej\u00f3 y la inspiraci\u00f3n que gu\u00eda nuestra misi\u00f3n.&nbsp;<\/p>","align":"center"},{"image":"01JRAKKVNNB0KSC82FS11STZHH.jpeg","title":"\u201cEl momento es Hoy\u201d","content":"<p>D\u00e9jate llevar por la canci\u00f3n de Elena, un canto profundo al valor de la vida. Sus palabras, llenas de poder y significado, son el legado que nos dej\u00f3 y la inspiraci\u00f3n que gu\u00eda nuestra misi\u00f3n.&nbsp;<\/p>","align":"right"}]}},{"type":"texto-dos-columnas","data":{"subtitle":"Nuestra misi\u00f3n","title":"Ayudamos en la Investigaci\u00f3n del OSTEOSARCOMA y al c\u00e1ncer infantil","text":"<p>Dedicado a todas las personas que so\u00f1amos con el d\u00eda en que se encuentre la cura del osteosarcoma, un c\u00e1ncer que muchos ni\u00f1os no logran superar.<\/p>"}},{"type":"items","data":{"items":[{"title":"Nuestros proyectos","description":"Conoce m\u00e1s acerca de los proyectos que realizamos con la ayuda que nos brindas","link":"http:\/\/www.google.es","text":"Descubrir","icon":"01JRB7NT8FD44KXSM8NEM6CFVX.svg","button_text":"Descubrir","button_link":"https:\/\/fundacionelenatertre.test\/"},{"title":"Carta de los fundadores","description":"<p>Te invitamos a conocer este legado de amor, un proyecto nacido del coraz\u00f3n<\/p>","text":"Descubrir","link":"http:\/\/www.google.es","icon":"01JRB7NT8J91CXTR18NG8WEHAH.svg","button_text":"Descubrir","button_link":"https:\/\/fundacionelenatertre.test\/"},{"title":"Nuestras actividades","description":"<p>Asiste a nuestros eventos deportivos, culturales y solidarios<\/p>","text":"Descubrir","link":"http:\/\/www.google.es","icon":"01JRB7NT8J91CXTR18NG8WEHAJ.svg","button_text":"Ver productos","button_link":"https:\/\/fundacionelenatertre.test\/"}]}},{"type":"items-numericos","data":{"items":[{"icon":"01JRCW8ANN7E4DMTR0AGR6PQN3.svg","number":"12","title":"Actividades con \u00e9xito","text":null,"link":null,"color":"#d1f6ff"},{"icon":"01JRFT38JJQXF7ZBDDA161BZ8R.svg","number":"153","color":"#d1f6ff","title":"Donaciones mensuales"},{"icon":"01JRFT38JMHH5DXEKJQ7PHYZHW.svg","number":"80","color":"#d1f6ff","title":"Vinculos de amistad"},{"icon":"01JRFT38JMHH5DXEKJQ7PHYZHX.svg","number":"1200","color":"#d1f6ff","title":"Productos entregados"}]}},{"type":"banner","data":{"title":"Tienda online","alignment":"center","description":"<p>Tu gesto, por peque\u00f1o que parezca, marca una gran diferencia. Juntos, podemos seguir construyendo un legado de amor, generosidad y solidaridad.<\/p><p>\u00a1Gracias por ser parte de esta causa!<\/p>","subtitle":"Nuestra Tienda Solidaria, donde cada producto tiene un prop\u00f3sito","text":"Ver productos","link":"http:\/\/www.google.es","image":"01JRD1HM2Z5ABMG838T2G2QQJ2.jpg","button_text":"Ver productos","button_link":"https:\/\/fundacionelenatertre.test\/"}},{"type":"actividades","data":{"title":"\u00daltimas actividades","subtitle":"Actividades importantes que te podr\u00edan interesar","number":"3"}},{"type":"sponsors","data":{"subtitle":"Patrocinio","title":"\u00danete a nuestra causa y marca la diferencia","text":null,"button_text":"Quiero unirme","button_link":"https:\/\/fundacionelenatertre.test\/"}}]'
                )]);
        Page::factory()
            ->count(2)
            ->published()
            ->create();

        Page::factory()
            ->count(2)
            ->create();
    }
}
