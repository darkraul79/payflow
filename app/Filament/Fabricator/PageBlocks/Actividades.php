<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Actividades extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('actividades')
            ->schema([
                Reusable::Basic(['text']),
                TextInput::make('number')
                    ->numeric()
                    ->maxValue(3)
                    ->minValue(1)
                    ->default(3)
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['classGrid'] = 'md:grid-cols-2 lg:grid-cols-' . $data['number'] . ' gap-4';
        return $data;
    }


}
