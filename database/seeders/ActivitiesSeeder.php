<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ActivitiesSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            'cartel-festival.jpg' => 'S1yJHm1NPxSYhksVis1gsYRbtGnjU5yROOVVjBLF.jpeg',
            'danza.jpg' => '7lHXTEPuLo87f3R5UWDUqHIqcG6KnAM9pU504Ue8.jpg',
            'danza-actividad.jpg' => '01JSH0J7NQQY1RPDYKNTBAB8R9.jpg',
            'cartel-carrera-solidaria.jpg' => 'cartel-carrera-solidaria.jpg',
            'LAS-ROZAS-FET-2025-316SALIDAS-PRUEBAS-DE-4-Y-8-KM.jpg' => 'LAS-ROZAS-FET-2025-316SALIDAS-PRUEBAS-DE-4-Y-8-KM.jpg'
        ];

        // Copio estas imágenes a la carpeta de storage
        foreach ($images as $name => $image) {
            copy(base_path('public/images/' . $name), public_path('storage/' . $image));

        }
        $titulo = 'III Festival solidario de música y danza a favor de la fundación Elena Tertre';
        $actividad = Activity::factory()->create([
            'title' => $titulo,
            'slug' => Str::slug($titulo),
            'content' => '<h2>Conoce más del festival</h2><p><br><strong>El III Festival Solidario de Música y Danza a favor de la Fundación Elena Tertre, tendrá lugar en el Teatro Victoria de Talavera de la Reina el domingo 16 de marzo a las 18.30 hrs.</strong><br><br>En el festival participarán cerca de 200 artistas de diferentes grupos musicales y escuelas de danza de Talavera de la Reina y su comarca.<br><br>En cuanto a la danza, destaca la presencia en el cartel de la Escuela de Danza Elsa G. Biedma, así como de la Escuela de Baile Adae Alma.&nbsp;<br> En cuanto a los conjuntos musicales, habrá notable presencia de música popular y tradicional, por parte de los grupos Pizarro, Parranda Castellana, La Alpargata y Rondaoras de Talavera.<br> Esta igualmente previsto que asista a este evento, un grupo coral de gran nivel, como es el Coro Quadrivium, agrupación que ofrecerá diferentes versiones vocalizadas de canciones cinematográficas y de música pop muy conocidas.<br> Las entradas para este III Festival Solidario de Música y Danza, se pueden adquirir en el Centro Extremeño de Talavera de la Reina, de 6 a 9 de la tarde todos los días de la semana e igualmente, en la misma taquilla del Teatro Victoria de Talavera de la Reina, una hora antes del comienzo del festival el día 16 de marzo, en el caso de que las entradas no se hubieran agotado con anterioridad.<br><br></p><h3>Agradecimiento particular</h3><p><br>La organización de esta gala solidaria, quiere agradecer la colaboración inestimable que está recibiendo en los preparativos de este festival por parte del Excmo. Ayto. de Talavera de la Reina, el Organismo Autónomo Local de Cultura, así como el apoyo de numerosas firmas comerciales de Talavera de la Reina y su comarca como son: Óptica Trinidad, automatismos MARSAMATIC, Copi-Arte, El Desván floristas, Neumáticos Manolo, Restaurante El Bodegón, Agrícola Marino, Restaurante El Puchero, Asociación KSEMAN y Restaurante POTO.</p><p><figure data-trix-attachment="{&quot;contentType&quot;:&quot;image/jpeg&quot;,&quot;filename&quot;:&quot;cartel-festival.jpg&quot;,&quot;filesize&quot;:288573,&quot;height&quot;:674,&quot;href&quot;:&quot;https://fundacionelenatertre.test/storage/S1yJHm1NPxSYhksVis1gsYRbtGnjU5yROOVVjBLF.jpg&quot;,&quot;url&quot;:&quot;https://fundacionelenatertre.test/storage/S1yJHm1NPxSYhksVis1gsYRbtGnjU5yROOVVjBLF.jpg&quot;,&quot;width&quot;:612}" data-trix-content-type="image/jpeg" data-trix-attributes="{&quot;caption&quot;:&quot;Festival&quot;,&quot;presentation&quot;:&quot;gallery&quot;}" class="attachment attachment--preview attachment--jpg"><a href="https://fundacionelenatertre.test/storage/S1yJHm1NPxSYhksVis1gsYRbtGnjU5yROOVVjBLF.jpg"><img src="https://fundacionelenatertre.test/storage/S1yJHm1NPxSYhksVis1gsYRbtGnjU5yROOVVjBLF.jpg" width="612" height="674"><figcaption class="attachment__caption attachment__caption--edited">Festival</figcaption></a></figure></p><h3>Agradecimiento particular</h3><p><br>La organización de esta gala solidaria, quiere agradecer la colaboración inestimable que está recibiendo en los preparativos de este festival por parte del Excmo. Ayto. de Talavera de la Reina, el Organismo Autónomo Local de Cultura, así como el apoyo de numerosas firmas comerciales de Talavera de la Reina y su comarca como son: Óptica Trinidad, automatismos MARSAMATIC, Copi-Arte, El Desván floristas, Neumáticos Manolo, Restaurante El Bodegón, Agrícola Marino, Restaurante El Puchero, Asociación KSEMAN y Restaurante POTO.</p><p><figure data-trix-attachment="{&quot;contentType&quot;:&quot;image/jpeg&quot;,&quot;filename&quot;:&quot;danza.jpg&quot;,&quot;filesize&quot;:534214,&quot;height&quot;:487,&quot;href&quot;:&quot;https://fundacionelenatertre.test/storage/7lHXTEPuLo87f3R5UWDUqHIqcG6KnAM9pU504Ue8.jpg&quot;,&quot;url&quot;:&quot;https://fundacionelenatertre.test/storage/7lHXTEPuLo87f3R5UWDUqHIqcG6KnAM9pU504Ue8.jpg&quot;,&quot;width&quot;:856}" data-trix-content-type="image/jpeg" data-trix-attributes="{&quot;presentation&quot;:&quot;gallery&quot;}" class="attachment attachment--preview attachment--jpg"><a href="https://fundacionelenatertre.test/storage/7lHXTEPuLo87f3R5UWDUqHIqcG6KnAM9pU504Ue8.jpg"><img src="https://fundacionelenatertre.test/storage/7lHXTEPuLo87f3R5UWDUqHIqcG6KnAM9pU504Ue8.jpg" width="856" height="487"><figcaption class="attachment__caption"><span class="attachment__name">danza.jpg</span> <span class="attachment__size">521.69 KB</span></figcaption></a></figure></p>',
            'date' => '2026-04-26 11:40:00',
            'address' => 'Teatro Victoria, Talavera de la Reina',
            'resume' => 'El III Festival Solidario de Música y Danza a favor de la Fundación Elena Tertre',
            'published' => true,
            'donacion' => true,
        ]);
        $actividad->addMedia(public_path('storage/01JSH0J7NQQY1RPDYKNTBAB8R9.jpg'))
            ->preservingOriginal()
            ->toMediaCollection('actividades');

        $titulo = 'III CARRERA SOLIDARIA DE LAS ROZAS';
        $actividad = Activity::factory()->create([
            'title' => $titulo,
            'slug' => Str::slug($titulo),
            'content' => '<p>El pasado sábado 5 de abril, tuvo lugar en la Dehesa de Navalcarbón, en <strong>Las Rozas</strong> (Madrid) la <strong>III Carrera Solidaria de la Fundación Elena Tertre.</strong></p><p>Más de medio millón de participantes, (573 en total), llenaron de solidaridad y de optimismo esta cita deportiva popular organizada por la <strong>Fundación Elena Tertre.</strong></p><p>La salida y meta estuvo situada en la zona verde recreativa de la Dehesa de Navalcarbón junto al recinto ferial de Las Rozas. Las pruebas se celebraron durante toda la mañana, siendo dos las distancias a recorrer que fueron de 4 y 8 kilómetros con salida única.</p><p>Además, hubo un paseo solidario popular por el mismo recorrido de la competición y unas pruebas atléticas de pequeño recorrido adaptadas para familias con niñas y niños menores de 10 años.</p><p>Junto con las pruebas deportivas, hubo varias carpas informativas de la Fundación Elena Tertre, así como un mercadillo solidario y una zona de fisioterapia y atención directa a los corredores instalados por la clínica <strong>Fisioincorpore.</strong></p>',
            'date' => '2025-04-05 10:00',
            'address' => 'Dehesa de Navalcarbón, en Las Rozas (Madrid)',
            'resume' => 'El pasado sábado 5 de abril, tuvo lugar en la Dehesa de Navalcarbón',
            'published' => true,
            'donacion' => false,
        ]);
        $actividad->addMedia(public_path('storage/cartel-carrera-solidaria.jpg'))
            ->preservingOriginal()
            ->toMediaCollection('actividades');


        $titulo = 'III CARRERA SOLIDARIA FUNDACIÓN ELENA TERTRE';
        $actividad = Activity::factory()->create([
            'title' => $titulo,
            'slug' => Str::slug($titulo),
            'content' => '<p>Únete a la Carrera Solidaria y la Marcha Familiar en apoyo a la Fundación Elena Tertre!</p><p>Fecha: 5 de abril de 2025<br>Lugar: Explanada de la Dehesa de Navalcarbón<br>Horario: de 10:00 a 13:00</p><p>Inscripción: ¡Recuerda que todo lo recaudado será destinado íntegramente a los programas de musicoterapia y detección precoz del osteosarcoma de la Fundación Elena Tertre!</p>',
            'date' => '2025-04-05 10:00',
            'address' => 'Explanada de la Dehesa de Navalcarbón',
            'resume' => 'Únete a la Carrera Solidaria y la Marcha Familiar en apoyo a la Fundación Elena Tertre!',
            'published' => true,
            'donacion' => false,
        ]);

        $actividad->addMedia(public_path('storage/LAS-ROZAS-FET-2025-316SALIDAS-PRUEBAS-DE-4-Y-8-KM.jpg'))
            ->preservingOriginal()
            ->toMediaCollection('actividades');
    }
}
