<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Slider extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('slider')
            ->label('Slider')
            ->icon('heroicon-s-chevron-right')
            ->schema([
                //
                Repeater::make('sliders')
                    ->label('Slides')
                    ->collapsible()
                    ->schema([

                        FileUpload::make('image')
                            ->label('Imagen')
                            ->multiple(false),
                        TextInput::make('title')
                            ->label('Título'),
                        RichEditor::make('content')
                            ->label('Descripción'),
                        Select::make('align')
                            ->label('Alineación')
                            ->options([
                                'left' => 'Izquierda',
                                'center' => 'Centrado',
                                'right' => 'Derecha',
                            ])
                            ->default('center'),
                    ])->itemLabel(fn (array $state): ?string => $state['title'] ?? null),

            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
