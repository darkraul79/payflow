<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Page;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Banner extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('banner')
            ->icon('heroicon-s-camera')
            ->columns(1)
            ->model(Page::class)
            ->schema([
                FileUpload::make('image')
                    ->label('Imagen')
                    ->required(),
                Group::make()
                    ->schema([
                        Group::make()
                            ->schema([
                                Reusable::Basic(),
                                Reusable::alignment()

                            ]),
                        RichEditor::make('description')
                            ->label('Descripción')
                            ->required(),
                    ]),

                Reusable::BotonFields(),

            ]);

    }

    public static function mutateData(array $data): array
    {

        return $data;
    }
}
