<?php

namespace App\Filament\Resources\tagResource\Pages;

use App\Filament\Resources\tagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class Edittag extends EditRecord
{
    protected static string $resource = tagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
