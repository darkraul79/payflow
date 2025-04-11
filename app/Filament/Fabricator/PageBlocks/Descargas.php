<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Descargas extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('descargas')
            ->schema([
                Reusable::Basic(),
                Section::make('')
                    ->label('Descargas')
                    ->collapsible()
                    ->schema([
                        Repeater::make('items')
                            ->collapsible()
                            ->reorderable()
                            ->schema([
                                TextInput::make('title'),
                                Textarea::make('content')
                                    ->label('Descripción')
                                    ->rows(2)
                                    ->columnSpan('full'),
                                FileUpload::make('file')
                                    ->label('Archivo')
                                    ->directory('descargas')
                            ])
                    ])
            ]);

    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
