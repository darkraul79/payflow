<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Page;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Items extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('items')
            ->schema([
                Repeater::make('items')
                    ->label('Item')
                    ->collapsible()
                    ->schema([
                        FileUpload::make('icon')
                            ->label('Icono')
                            ->multiple(false),
                        TextInput::make('title')
                            ->label('Título')
                            ->required(),
                        RichEditor::make('description')
                            ->label('Description')
                            ->required(),
                        Fieldset::make('button')
                            ->label('Botón')
                            ->schema([
                                TextInput::make('text')
                                    ->label('Texto del botón')
                                    ->required(),
                                TextInput::make('link')
                                    ->label('Enlace del botón')
                                    ->url()
                                    ->required(),
                            ]),

                    ])->model(Page::class)->itemLabel(fn(array $state): ?string => $state['title'] ?? null),


                //
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
