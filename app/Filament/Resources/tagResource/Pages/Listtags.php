<?php

namespace App\Filament\Resources\tagResource\Pages;

use App\Filament\Resources\tagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class Listtags extends ListRecords
{
    protected static string $resource = tagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
