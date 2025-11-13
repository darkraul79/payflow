<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Page;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
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
                        Grid::make()
                            ->columns(4)
                            ->schema([

                                FileUpload::make('icon')
                                    ->label('Icono')
                                    ->columnSpan(1)
                                    ->required()
                                    ->multiple(false),

                                Group::make()
                                    ->columns(1)
                                    ->columnSpan(3)
                                    ->schema([
                                        Reusable::Basic(['subtitle']),
                                    ]),
                            ]),
                        Reusable::BotonFields(),

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
