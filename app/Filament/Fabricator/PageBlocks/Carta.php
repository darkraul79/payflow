<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Carta extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('carta')
            ->schema([
                FileUpload::make('image')
                    ->label('Imagen'),
                Reusable::Basic(),


            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
