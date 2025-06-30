<?php

namespace App\Filament\Resources\DonationResource\Pages;

use App\Filament\Resources\DonationResource;
use App\Models\Donation;
use App\Models\State;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewDonation extends ViewRecord
{
    protected static string $resource = DonationResource::class;

    protected static string $view = 'filament.resources.donation-resource.pages.view-donation';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function getActions(): array
    {
        return [
            Action::make('cancelar')
                ->label('Cancelar')
                ->requiresConfirmation()
                ->action(fn(Donation $record) => $record->cancel())
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->visible(fn(Donation $record) => $record->type === Donation::RECURRENTE &&
                    $record->state?->name === State::ACTIVA),
        ];
    }
}
