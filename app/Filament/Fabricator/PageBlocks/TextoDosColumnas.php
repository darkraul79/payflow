<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class TextoDosColumnas extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('texto-dos-columnas')
            ->schema([
                //
                TextInput::make('subtitle')
                    ->label('Subtitular'),
                TextInput::make('title')
                    ->label('Titular'),
                RichEditor::make('text')
                    ->label('Texto'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
