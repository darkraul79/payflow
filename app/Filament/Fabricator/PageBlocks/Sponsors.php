<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Sponsor;
use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Sponsors extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('sponsors')
            ->schema([
                Reusable::Basic(),
                Reusable::BotonFields(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['sponsors'] = Sponsor::all()->sortBy('order');

        return $data;
    }
}
