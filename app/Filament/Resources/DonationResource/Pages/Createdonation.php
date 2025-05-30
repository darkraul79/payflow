<?php

namespace App\Filament\Resources\donationResource\Pages;

use App\Filament\Resources\DonationResource;
use Filament\Resources\Pages\CreateRecord;

class Createdonation extends CreateRecord
{
    protected static string $resource = DonationResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
