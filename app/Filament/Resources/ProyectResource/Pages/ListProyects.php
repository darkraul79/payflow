<?php

namespace App\Filament\Resources\ProyectResource\Pages;

use App\Filament\Resources\ProyectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProyects extends ListRecords
{
    protected static string $resource = ProyectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
