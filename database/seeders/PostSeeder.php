<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            'cartel-festival.jpg' => 'S1yJHm1NPxSYhksVis1gsYRbtGnjU5yROOVVjBLF.jpeg',
            'danza.jpg' => '7lHXTEPuLo87f3R5UWDUqHIqcG6KnAM9pU504Ue8.jpg',
            'danza-actividad.jpg' => '01JSH0J7NQQY1RPDYKNTBAB8R9.jpg',
        ];

        // Copio estas imágenes a la carpeta de storage
        foreach ($images as $name => $image) {
            copy(base_path('public/images/' . $name), public_path('storage/' . $image));

        }
        $titulo = 'III Festival solidario de música y danza a favor de la fundación Elena Tertre';
        $actividad = Post::factory()->create([
            'title' => $titulo,
            'slug' => Str::slug($titulo),
            'content' => '<h2>Conoce más del festival</h2><p><br><strong>El III Festival Solidario de Música y Danza a favor de la Fundación Elena Tertre, tendrá lugar en el Teatro Victoria de Talavera de la Reina el domingo 16 de marzo a las 18.30 hrs.</strong><br><br>En el festival participarán cerca de 200 artistas de diferentes grupos musicales y escuelas de danza de Talavera de la Reina y su comarca.<br><br>En cuanto a la danza, destaca la presencia en el cartel de la Escuela de Danza Elsa G. Biedma, así como de la Escuela de Baile Adae Alma.&nbsp;<br> En cuanto a los conjuntos musicales, habrá notable presencia de música popular y tradicional, por parte de los grupos Pizarro, Parranda Castellana, La Alpargata y Rondaoras de Talavera.<br> Esta igualmente previsto que asista a este evento, un grupo coral de gran nivel, como es el Coro Quadrivium, agrupación que ofrecerá diferentes versiones vocalizadas de canciones cinematográficas y de música pop muy conocidas.<br> Las entradas para este III Festival Solidario de Música y Danza, se pueden adquirir en el Centro Extremeño de Talavera de la Reina, de 6 a 9 de la tarde todos los días de la semana e igualmente, en la misma taquilla del Teatro Victoria de Talavera de la Reina, una hora antes del comienzo del festival el día 16 de marzo, en el caso de que las entradas no se hubieran agotado con anterioridad.<br><br></p><h3>Agradecimiento particular</h3><p><br>La organización de esta gala solidaria, quiere agradecer la colaboración inestimable que está recibiendo en los preparativos de este festival por parte del Excmo. Ayto. de Talavera de la Reina, el Organismo Autónomo Local de Cultura, así como el apoyo de numerosas firmas comerciales de Talavera de la Reina y su comarca como son: Óptica Trinidad, automatismos MARSAMATIC, Copi-Arte, El Desván floristas, Neumáticos Manolo, Restaurante El Bodegón, Agrícola Marino, Restaurante El Puchero, Asociación KSEMAN y Restaurante POTO.</p><p><figure data-trix-attachment="{&quot;contentType&quot;:&quot;image/jpeg&quot;,&quot;filename&quot;:&quot;cartel-festival.jpg&quot;,&quot;filesize&quot;:288573,&quot;height&quot;:674,&quot;href&quot;:&quot;https://fundacionelenatertre.test/storage/S1yJHm1NPxSYhksVis1gsYRbtGnjU5yROOVVjBLF.jpg&quot;,&quot;url&quot;:&quot;https://fundacionelenatertre.test/storage/S1yJHm1NPxSYhksVis1gsYRbtGnjU5yROOVVjBLF.jpg&quot;,&quot;width&quot;:612}" data-trix-content-type="image/jpeg" data-trix-attributes="{&quot;caption&quot;:&quot;Festival&quot;,&quot;presentation&quot;:&quot;gallery&quot;}" class="attachment attachment--preview attachment--jpg"><a href="https://fundacionelenatertre.test/storage/S1yJHm1NPxSYhksVis1gsYRbtGnjU5yROOVVjBLF.jpg"><img src="https://fundacionelenatertre.test/storage/S1yJHm1NPxSYhksVis1gsYRbtGnjU5yROOVVjBLF.jpg" width="612" height="674"><figcaption class="attachment__caption attachment__caption--edited">Festival</figcaption></a></figure></p><h3>Agradecimiento particular</h3><p><br>La organización de esta gala solidaria, quiere agradecer la colaboración inestimable que está recibiendo en los preparativos de este festival por parte del Excmo. Ayto. de Talavera de la Reina, el Organismo Autónomo Local de Cultura, así como el apoyo de numerosas firmas comerciales de Talavera de la Reina y su comarca como son: Óptica Trinidad, automatismos MARSAMATIC, Copi-Arte, El Desván floristas, Neumáticos Manolo, Restaurante El Bodegón, Agrícola Marino, Restaurante El Puchero, Asociación KSEMAN y Restaurante POTO.</p><p><figure data-trix-attachment="{&quot;contentType&quot;:&quot;image/jpeg&quot;,&quot;filename&quot;:&quot;danza.jpg&quot;,&quot;filesize&quot;:534214,&quot;height&quot;:487,&quot;href&quot;:&quot;https://fundacionelenatertre.test/storage/7lHXTEPuLo87f3R5UWDUqHIqcG6KnAM9pU504Ue8.jpg&quot;,&quot;url&quot;:&quot;https://fundacionelenatertre.test/storage/7lHXTEPuLo87f3R5UWDUqHIqcG6KnAM9pU504Ue8.jpg&quot;,&quot;width&quot;:856}" data-trix-content-type="image/jpeg" data-trix-attributes="{&quot;presentation&quot;:&quot;gallery&quot;}" class="attachment attachment--preview attachment--jpg"><a href="https://fundacionelenatertre.test/storage/7lHXTEPuLo87f3R5UWDUqHIqcG6KnAM9pU504Ue8.jpg"><img src="https://fundacionelenatertre.test/storage/7lHXTEPuLo87f3R5UWDUqHIqcG6KnAM9pU504Ue8.jpg" width="856" height="487"><figcaption class="attachment__caption"><span class="attachment__name">danza.jpg</span> <span class="attachment__size">521.69 KB</span></figcaption></a></figure></p>',
            'date' => '26-04-26 11:40:00',
            'address' => 'Teatro Victoria, Talavera de la Reina',
            'resume' => 'El III Festival Solidario de Música y Danza a favor de la Fundación Elena Tertre',
            'published' => true,
            'donacion' => true,
        ]);
        $actividad->addMedia(public_path('storage/01JSH0J7NQQY1RPDYKNTBAB8R9.jpg'))
            ->preservingOriginal()
            ->toMediaCollection();
    }
}
