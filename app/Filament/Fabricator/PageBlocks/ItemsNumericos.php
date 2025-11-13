<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Page;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class ItemsNumericos extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('items-numericos')
            ->schema([
                Repeater::make('items')
                    ->label('Item')
                    ->collapsible()
                    ->schema([
                        Grid::make()
                            ->columns(4)
                            ->schema([

                                FileUpload::make('icon')
                                    ->label('Icono')
                                    ->columnSpan(1)
                                    ->required()
                                    ->multiple(false),

                                Group::make()
                                    ->columnSpan(3)
                                    ->schema([
                                        Group::make()
                                            ->columns()
                                            ->schema([
                                                TextInput::make('number')
                                                    ->columnSpan(1)
                                                    ->label('Número')
                                                    ->numeric()
                                                    ->required(),
                                                ColorPicker::make('color')
                                                    ->columnSpan(1)
                                                    ->label('Color de fondo'),

                                            ]),
                                        TextInput::make('title')
                                            ->columnSpanFull()
                                            ->label('Título')
                                            ->required(),

                                    ]),
                            ]),
                    ])->model(Page::class)
                    ->itemLabel(fn (array $state): ?string => 'Item - '.$state['title'] ?? null),

                //
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
