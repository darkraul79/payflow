<?php

namespace App\Filament\Resources\DonationResource\Pages;

use App\Filament\Resources\DonationResource;
use Filament\Resources\Pages\ViewRecord;

class ViewDonation extends ViewRecord
{
    protected static string $resource = DonationResource::class;

    protected static string $view = 'filament.resources.donation-resource.pages.view-donation';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
