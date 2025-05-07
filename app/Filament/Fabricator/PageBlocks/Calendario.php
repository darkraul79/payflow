<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Calendario extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('calendario')
            ->schema([
                Reusable::Basic(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
