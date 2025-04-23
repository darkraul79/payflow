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
            'fundacion.jpg' => '01JRG93691V98CBN7CKE7Q00QB.jpg',
            'logo-fundacion-vertical.svg' => '01JRGDCVN6E9FH00DAT9K4RT2P.svg',
            'icons/wave.svg' => '01JRGDG7T55K1J1NVC8ER0BG70.svg',
            'icons/heart-people.svg' => '01JRGDG7T7JAMHGC2BAVMRPMTN.svg',
            'icons/heart-charity.svg' => '01JRGMTSS7963YQX6EJN3DYD20.svg',
            'icons/heart-hand-outline.svg' => '01JRGMTSS9AVKVM7P1RSB2WAPQ.svg',
            'firma1.png' => 'Rge1Tb7xzKSsfUnaZJ9OIi7KITMEyUxt0Ae0oDNU.png',
            'bea.webp' => '01JRJ53KQ84F4N2T7DR6073S9P.webp',
            'fundacion.pdf' => '01JRJ9VRXZ1P2J3R7RP829QJCP.pdf',
            'investigacion.jpg' => 'lRO7zXI26QjvK4Y5xExpcfHSvxGo2OTuHDS4Ntp5.jpg',
        ];

        // Copio estas imágenes a la carpeta de storage
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
        $quienes = Page::factory()
            ->published()->create([
                'title' => 'Quiénes somos',
                'slug' => 'quienes-somos',
            ]);

        Page::factory()
            ->published()
            ->create([
                'title' => 'Fundación',
                'parent_id' => $quienes->id,
                'slug' => 'fundacion',
                'blocks' => json_decode('[{"type":"carta","data":{"image":"01JRG93691V98CBN7CKE7Q00QB.jpg","subtitle":"Carta de los fundadores","title":"Fundaci\u00f3n","text":"<p>Han pasado tres a\u00f1os desde que Elena est\u00e1 en el cielo y, aunque ya no est\u00e9 f\u00edsicamente, su imborrable presencia ha permanecido entre nosotros ayud\u00e1ndonos a reponernos.<br><br>Ahora, de forma inexplicable, sentimos la necesidad de movernos y empezar a devolver toda la ayuda que nos dieron cuando lo necesitamos. Esta llamada solo puede venir de ELENA. Quiere unirnos en un generoso proyecto que lleve su nombre para ayudar a quienes est\u00e9n pasando por lo mismo que ella. Porque esto es lo que nos va a sanar y lo que dar\u00e1 sentido a nuestras vidas: una vida m\u00e1s generosa en honor a ella.<br><br>As\u00ed nace la Fundaci\u00f3n Elena Tertre, este es su legado. Un lugar para unir a todas las personas que han conocido a Elena y colaborar por una causa com\u00fan que es ELLA. Porque quienes la conocimos, sabemos que era alegr\u00eda, amistad, familia y entrega. Porque ella siempre quiso ayudar, le encantaba llegar a las personas y unirlas.<\/p><p><br>Su proyecto necesita nuestras manos y nuestro trabajo. Somos sus instrumentos y la dejaremos hacer a trav\u00e9s de nosotros. Estamos creando una red maravillosa para recaudar fondos y AYUDAR al mayor n\u00famero de personas. Y nuestra labor dar\u00e1 frutos, porque ELENA est\u00e1 detr\u00e1s de todo.<br><br>La Fundaci\u00f3n Elena Tertre tiene dos fines bien definidos:<br><br>Ayudar en la investigaci\u00f3n del osteosarcoma para que la ciencia consiga encontrar una cura a este c\u00e1ncer que muchos ni\u00f1os no logran superar.<br>Ayudar a mejorar la calidad de vida de los ni\u00f1os enfermos y en las necesidades que tengan sus familias.<br>Millones de gracias. Elena cuenta con todos. \u00a1Elena cuenta contigo!<br><br>17 octubre 2022<\/p><p><\/p>"}},{"type":"la-ola","data":{"subtitle":"La Ola ","title":"Una met\u00e1fora  de la vida","text":null,"items":[{"icon":"01JRGDG7T55K1J1NVC8ER0BG70.svg","title":"La Ola","text":"<p>Cuando Elena estaba muy enferma decidi\u00f3 tatuarse una ola con su amiga Laura. Para ella, la ola era una met\u00e1fora de LA VIDA. En los momentos buenos, est\u00e1s en la cresta, y en los malos, est\u00e1s en la parte baja de la ola. Pero todos los momentos, buenos y malos, son pasajeros, no duran mucho y, por eso, hay que saber disfrutarlos, porque pasan muy r\u00e1pido. Despu\u00e9s de una ola, llega otra y luego, otra, y otra, y otra\u2026 Elena evitaba ahondar en ello, se limitaba a decirnos: \u201cTodo pasa y hay que saber disfrutarlo, mi enfermedad tambi\u00e9n pasar\u00e1\u201d.<br><br>Su padrino Juli, su t\u00eda Bea y su hermano David se tatuaron la ola tambi\u00e9n siguiendo su ejemplo. \u00a1Y Elena estaba feliz!<br><br>A los pocos d\u00edas, muri\u00f3 y, a partir de aquel momento, sus amigos empezaron a hacerse el mismo tatuaje. Se cre\u00f3 un movimiento donde la ola nos recordaba a ella y nos uni\u00f3 para siempre a Elena. La ola es la mejor manera de representar a su fundaci\u00f3n.<\/p>"}],"image":"01JRGDCVN6E9FH00DAT9K4RT2P.svg","items2":[{"icon":"01JRGDG7T7JAMHGC2BAVMRPMTN.svg","title":"El Azul","text":"<p>Significa mar-cielo-infinito. Nos acerca a donde est\u00e1.<\/p>"},{"icon":"01JRGMTSS7963YQX6EJN3DYD20.svg","title":"El logotipo","text":"<p>Lo han dise\u00f1ado los hermanos Chillida, amigos de Elena. Quer\u00edamos que, adem\u00e1s de ser bonito y cuidado, transmitiera el mensaje de la ola que ella nos dej\u00f3.<\/p>"},{"icon":"01JRGMTSS9AVKVM7P1RSB2WAPQ.svg","title":"El nombre","text":"<p>Para nosotros, los fundadores, su familia, esta fundaci\u00f3n es su legado. Nuestra inspiraci\u00f3n ha venido de ella y no podr\u00eda llevar otro nombre que no fuera el suyo.<br>La palabra FUNDACI\u00d3N est\u00e1 en may\u00fasculas porque nos hace so\u00f1ar en grande, porque este proyecto nos llena de ilusi\u00f3n. Su nombre, Elena Tertre, est\u00e1 en min\u00fasculas porque nos recuerda su adolescencia, nos acerca a la infancia y a la dulzura de los ni\u00f1os que son nuestro objetivo.<\/p>"}]}},{"type":"texto-video","data":{"subtitle":"Canci\u00f3n por Elena Terte","title":"\u201cEl legado\u201d","text":"<h3>Acepta y ama en los momentos m\u00e1s dif\u00edciles<\/h3><p>Este es un mensaje de transformaci\u00f3n y crecimiento personal, recordando que la vida puede cambiar en un instante, llev\u00e1ndonos de la abundancia a la nada. A trav\u00e9s de esta p\u00e9rdida o cambio, la canci\u00f3n invita a aceptar y amar incluso los momentos dif\u00edciles, pues de ellos se extrae aprendizaje y madurez.&nbsp;<br>Con una actitud positiva y consciente, el dolor se convierte en luz y en una oportunidad para evolucionar.<\/p>","video":"https:\/\/www.youtube.com\/embed\/pluLPATYWUc?si=YegWtkyhn18smqF5"}}]'
                )]);
        Page::factory()
            ->published()->create([
                'title' => 'Patronato',
                'slug' => 'patronato',
                'parent_id' => $quienes->id,
                'blocks' => json_decode('[{"type":"patronato","data":{"subtitle":"Conoce a nuestros voluntarios","title":"Patronato","text":null,"items":[{"image":"01JRJ53KQ84F4N2T7DR6073S9P.webp","name":"Elena Boy\u00e9 Delgado","position":"Fundadora","bio":"<p>L\u00f6rem ipsum us trevis. Sore vorar men kassa. Dan prer best: mobill\u00e5ngfilm. Pavangen ot hurad. Gigalig kontraskap.&nbsp;<br>F\u00e4v sasoren. Tribel prokrati. Tregen sede och penera dektigt, or. Vinysade pavis i multina g\u00f6lingar dimina. \u00c4vis. Du kan vara drabbad.&nbsp;<\/p>","button_text":"sd","button_link":"https:\/\/fundacionelenatertre.test"},{"image":"01JRAKKVNNB0KSC82FS11STZHH.jpeg","name":"David Tertre Tor\u00e1n","position":"Fundador","bio":"<p>L\u00f6rem ipsum us trevis. Sore vorar men kassa. Dan prer best: mobill\u00e5ngfilm. Pavangen ot hurad. Gigalig kontraskap.&nbsp;<br>F\u00e4v sasoren. Tribel prokrati. Tregen sede och penera dektigt, or. Vinysade pavis i multina g\u00f6lingar dimina. \u00c4vis. Du kan vara drabbad.&nbsp;<\/p>","button_text":"sd","button_link":"https:\/\/fundacionelenatertre.test"}]}}]'
                )]);

        Page::factory()
            ->published()->create([
                'title' => 'Transparencia',
                'slug' => 'transparencia',
                'parent_id' => $quienes->id,
                'blocks' => json_decode('[{"type":"basico","data":{"subtitle":"Somos transparentes","title":"Transparencia","text":"<p>Es una prioridad de la fundaci\u00f3n la absoluta transparencia en cada una de nuestras actuaciones, para que todos los que colaboran y ayudan tengan plena seguridad en el buen fin de sus esfuerzos.<br><br>Se publicar\u00e1n puntualmente nuestras cuentas, planes de actuaci\u00f3n, auditorias, resoluciones del Patronato, estatutos y sus modificaciones, as\u00ed como cualquier otra documentaci\u00f3n que consideremos de inter\u00e9s.<br><br>La fundaci\u00f3n est\u00e1 bajo control del Protectorado de Fundaciones de la Comunidad Aut\u00f3noma de Madrid, adscrita a la Consejer\u00eda de sanidad.<br><br>Podr\u00e1 dirigirse mediante correo electr\u00f3nico a la fundaci\u00f3n para cualquier duda o consulta que le pueda surgir respecto a nuestras actividades.<br><br>Gracias por confiar en nosotros y apoyar nuestra misi\u00f3n.<\/p>"}},{"type":"descargas","data":{"subtitle":null,"title":"Documentos de inter\u00e9s","text":null,"items":[{"title":"Legales","content":"L\u00f6rem ipsum jaling pret\u00e5belt tiskade baraktig geologi. Drevkultur kropibelt i ber. Paranas makrolig tism. Anan prerat tills diagus. Spedonas telekalig. ","file":"descargas\/01JRJ9VRXZ1P2J3R7RP829QJCP.pdf"}]}}]'
                )]);
        Page::factory()
            ->published()->create([
                'title' => 'Osteosarcoma',
                'slug' => 'osteosarcoma',
                'layout' => 'donacion',
                'parent_id' => $quienes->id,
                'blocks' => json_decode('[{"type":"basico","data":{"subtitle":"Osteosarcoma","title":"\u00bfQu\u00e9 es el osteosarcoma?","text":"<p>Es un tipo de c\u00e1ncer poco frecuente que se origina en los huesos y que suele afectar a ni\u00f1os y adolescentes. Comienza cuando las c\u00e9lulas del cuerpo empiezan a crecer de forma descontrolada. En ni\u00f1os, adolescentes y adultos j\u00f3venes, suele originarse en zonas en las que el hueso crece r\u00e1pidamente, como cerca de los extremos de los huesos de las piernas (rodillas) o los brazos (hombros), aunque el osteosarcoma puede desarrollarse en cualquier hueso.<\/p><h2>Diagn\u00f3stico<\/h2><p><br>Es fundamental hacer una biopsia. Una vez establecido el diagn\u00f3stico es probable que necesites estudios de imagen especiales o esc\u00e1neres para buscar el c\u00e1ncer en cualquier otra parte del cuerpo. Es importante buscar un especialista en sarcomas que te gu\u00ede durante todo el proceso.<\/p><p><br><\/p><p><figure data-trix-attachment=\"{&quot;contentType&quot;:&quot;image\/jpeg&quot;,&quot;filename&quot;:&quot;investigacion.jpg&quot;,&quot;filesize&quot;:316114,&quot;height&quot;:471,&quot;href&quot;:&quot;https:\/\/fundacionelenatertre.test\/storage\/lRO7zXI26QjvK4Y5xExpcfHSvxGo2OTuHDS4Ntp5.jpg&quot;,&quot;url&quot;:&quot;https:\/\/fundacionelenatertre.test\/storage\/lRO7zXI26QjvK4Y5xExpcfHSvxGo2OTuHDS4Ntp5.jpg&quot;,&quot;width&quot;:856}\" data-trix-content-type=\"image\/jpeg\" data-trix-attributes=\"{&quot;presentation&quot;:&quot;gallery&quot;}\" class=\"attachment attachment--preview attachment--jpg\"><a href=\"https:\/\/fundacionelenatertre.test\/storage\/lRO7zXI26QjvK4Y5xExpcfHSvxGo2OTuHDS4Ntp5.jpg\"><img src=\"https:\/\/fundacionelenatertre.test\/storage\/lRO7zXI26QjvK4Y5xExpcfHSvxGo2OTuHDS4Ntp5.jpg\" width=\"856\" height=\"471\"><figcaption class=\"attachment__caption\"><span class=\"attachment__name\">investigacion.jpg<\/span> <span class=\"attachment__size\">308.71 KB<\/span><\/figcaption><\/a><\/figure><\/p><p><br><\/p><h2>Tratamiento<\/h2><h3>Al ser un c\u00e1ncer poco frecuente, solo los m\u00e9dicos de los principales centros oncol\u00f3gicos tienen amplia experiencia en el tratamiento.<\/h3><p><br><\/p><p>Para tratarlo, se recomienda un enfoque de equipo. En ni\u00f1os y adolescentes, este equipo incluye al pediatra del ni\u00f1o y a los especialistas en c\u00e1ncer infantil: un cirujano especializado en m\u00fasculos y huesos con experiencia en el tratamiento de tumores \u00f3seos, un onc\u00f3logo m\u00e9dico o pedi\u00e1trico, un onc\u00f3logo radioter\u00e1pico y un m\u00e9dico especializado en rehabilitaci\u00f3n y fisioterapia. El equipo tambi\u00e9n puede incluir otros m\u00e9dicos, asistentes, enfermeros, psic\u00f3logos, trabajadores sociales, especialistas en rehabilitaci\u00f3n y otros profesionales de la salud.<\/p><p><br><\/p><p><br><\/p><p><figure data-trix-attachment=\"{&quot;contentType&quot;:&quot;image\/jpeg&quot;,&quot;filename&quot;:&quot;investigacion.jpg&quot;,&quot;filesize&quot;:316114,&quot;height&quot;:471,&quot;href&quot;:&quot;https:\/\/fundacionelenatertre.test\/storage\/lRO7zXI26QjvK4Y5xExpcfHSvxGo2OTuHDS4Ntp5.jpg&quot;,&quot;url&quot;:&quot;https:\/\/fundacionelenatertre.test\/storage\/lRO7zXI26QjvK4Y5xExpcfHSvxGo2OTuHDS4Ntp5.jpg&quot;,&quot;width&quot;:856}\" data-trix-content-type=\"image\/jpeg\" data-trix-attributes=\"{&quot;presentation&quot;:&quot;gallery&quot;}\" class=\"attachment attachment--preview attachment--jpg\"><a href=\"https:\/\/fundacionelenatertre.test\/storage\/lRO7zXI26QjvK4Y5xExpcfHSvxGo2OTuHDS4Ntp5.jpg\"><img src=\"https:\/\/fundacionelenatertre.test\/storage\/lRO7zXI26QjvK4Y5xExpcfHSvxGo2OTuHDS4Ntp5.jpg\" width=\"856\" height=\"471\"><figcaption class=\"attachment__caption\"><span class=\"attachment__name\">investigacion.jpg<\/span> <span class=\"attachment__size\">308.71 KB<\/span><\/figcaption><\/a><\/figure><br><br><\/p>"}}]'
                )]);

        Page::factory()
            ->published()->create([
                'title' => 'Objetivos',
                'slug' => 'objetivos',
                'parent_id' => $quienes->id,
            ]);
        $quehacemos = Page::factory()
            ->published()->create([
                'title' => 'Qué hacemos',
                'slug' => 'que-hacemos',
            ]);
        Page::factory()->create([
            'title' => 'Actividades',
            'slug' => 'actividades',
            'parent_id' => $quehacemos->id,
        ]);

    }
}
