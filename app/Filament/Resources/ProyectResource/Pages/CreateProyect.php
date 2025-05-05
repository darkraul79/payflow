<?php

namespace App\Filament\Resources\ProyectResource\Pages;

use App\Filament\Resources\ProyectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProyect extends CreateRecord
{
    protected static string $resource = ProyectResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
