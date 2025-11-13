<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class LaOla extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('la-ola')
            ->schema([
                Reusable::Basic(['text']),
                FileUpload::make('image')
                    ->label('Logo'),
                Group::make()
                    ->schema([
                        Fieldset::make('')
                            ->label('Columnas')
                            ->schema([
                                Repeater::make('items')
                                    ->collapsible()
                                    ->schema([
                                        FileUpload::make('icon')
                                            ->label('Icono'),
                                        Reusable::Basic(['subtitle']),
                                    ])->columnSpan(1),
                                Repeater::make('items2')
                                    ->collapsible()
                                    ->schema([
                                        FileUpload::make('icon')
                                            ->label('Icono'),
                                        Reusable::Basic(['subtitle']),
                                    ])->columnSpan(1),
                            ]),

                    ]),

            ]);

    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
