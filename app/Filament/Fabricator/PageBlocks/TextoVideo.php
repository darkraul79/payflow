<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class TextoVideo extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('texto-video')
            ->schema([
                Reusable::Basic()->columnSpan(1),
                TextInput::make('video')
                    ->label('Vídeo')
                    ->columnSpan(1)
                    ->activeUrl(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
