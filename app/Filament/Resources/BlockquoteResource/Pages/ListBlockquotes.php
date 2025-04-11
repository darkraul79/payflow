<?php

namespace App\Filament\Resources\BlockquoteResource\Pages;

use App\Filament\Resources\BlockquoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBlockquotes extends ListRecords
{
    protected static string $resource = BlockquoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
