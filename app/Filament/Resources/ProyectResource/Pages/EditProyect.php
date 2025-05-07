<?php

namespace App\Filament\Resources\ProyectResource\Pages;

use App\Filament\Resources\ProyectResource;
use App\Models\Proyect;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProyect extends EditRecord
{
    protected static string $resource = ProyectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
            Action::make('visit')
                ->label('Visitar')
                ->url(fn (?Proyect $record) => $record->getUrl() ?? null)
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->openUrlInNewTab()
                ->color('success')
                ->visible(config('filament-fabricator.routing.enabled')),
            Action::make('Guardar')->action('save'),
        ];
    }
}
