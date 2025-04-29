<?php

namespace App\Filament\Resources\tagResource\Pages;

use App\Filament\Resources\tagResource;
use Filament\Resources\Pages\CreateRecord;

class Createtag extends CreateRecord
{
    protected static string $resource = tagResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
