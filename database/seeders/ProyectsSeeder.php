<?php

namespace Database\Seeders;

use App\Models\Proyect;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProyectsSeeder extends Seeder
{
    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    public function run(): void
    {
        $titulo = 'Proyecto 1';
        Proyect::factory()
            ->donacion()
            ->imagen(public_path('storage/danza-actividad.jpg'))
            ->galeria([
                public_path('storage/danza-actividad.jpg'),
                public_path('storage/cartel-festival.jpg'),
                public_path('storage/cartel-carrera-solidaria.jpg'),
                public_path('storage/LAS-ROZAS-FET-2025-316SALIDAS-PRUEBAS-DE-4-Y-8-KM.jpg'),
            ])->create([
                'title' => $titulo,
                'slug' => Str::slug($titulo),
                'content' => '<h2>Conoce más del festival</h2><p><br><strong>El III Festival Solidario de Música y Danza a favor de la Fundación Elena Tertre, tendrá lugar en el Teatro Victoria de Talavera de la Reina el domingo 16 de marzo a las 18.30 hrs.</strong><br><br>En el festival participarán cerca de 200 artistas de diferentes grupos musicales y escuelas de danza de Talavera de la Reina y su comarca.',
                'resume' => 'Proyecto 1 es un proyecto',
            ]);

        $titulo = 'Proyecto 2';
        Proyect::factory()
            ->imagen(public_path('storage/LAS-ROZAS-FET-2025-316SALIDAS-PRUEBAS-DE-4-Y-8-KM.jpg'))
            ->create([
                'title' => $titulo,
                'slug' => Str::slug($titulo),
                'content' => '<p>El pasado sábado 5 de abril, tuvo lugar en la Dehesa de Navalcarbón, en <strong>Las Rozas</strong> (Madrid) la <strong>III Carrera Solidaria de la Fundación Elena Tertre.</strong></p>',
                'resume' => 'El pasado sábado 5 de abril, tuvo lugar en la Dehesa de Navalcarbón',
            ]);

        $titulo = 'Proyecto 3';
        Proyect::factory()
            ->imagen(public_path('storage/cartel-carrera-solidaria.jpg'))->create([
                'title' => $titulo,
                'slug' => Str::slug($titulo),
                'content' => '<p>Únete a la Carrera Solidaria y la Marcha Familiar en apoyo a la Fundación Elena Tertre!</p><p>Fecha: 5 de abril de 2025<br>Lugar: Explanada de la Dehesa de Navalcarbón<br>Horario: de 10:00 a 13:00</p><p>Inscripción: ¡Recuerda que todo lo recaudado será destinado íntegramente a los programas de musicoterapia y detección precoz del osteosarcoma de la Fundación Elena Tertre!</p>',
                'resume' => 'Únete a la Carrera Solidaria y la Marcha Familiar en apoyo a la Fundación Elena Tertre!',
            ]);

    }
}
