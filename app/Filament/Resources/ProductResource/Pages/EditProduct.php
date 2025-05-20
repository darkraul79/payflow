<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
            Action::make('visit')
                ->label('Visitar')
                ->url(fn($record) => $record->getLink() ?? null)
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->openUrlInNewTab()
                ->color('success'),
            Action::make('Guardar')->action('save'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!$data['oferta']) {
            $data['offer_price'] = null;
        }
        unset($data['oferta']);

        return $data;
    }
}
