<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Patronato extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('patronato')
            ->schema([
                Reusable::Basic(),
                Section::make()
                    ->label('Patronato')
                    ->schema([
                        Repeater::make('items')
                            ->reorderable()
                            ->collapsible()
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Foto'),
                                TextInput::make('name')
                                    ->required()
                                    ->label('Nombre'),
                                TextInput::make('position')
                                    ->required()
                                    ->label('Cargo'),
                                RichEditor::make('bio'),
                                //                                Reusable::BotonFields()
                            ]),
                    ]),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
