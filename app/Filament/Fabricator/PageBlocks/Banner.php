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
                                Reusable::alignment(),
                                Reusable::Basic(['text']),

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
        switch ($data['alignment']) {
            case 'left':
                $data['alignment_text'] = 'text-left';
                $data['box-alignment'] = 'lg:me-auto';
                $data['alignment_button'] = 'me-auto';
                break;

            case 'center':
                $data['alignment_text'] = 'text-center';
                $data['box-alignment'] = 'lg:mx-auto';
                $data['alignment_button'] = 'mx-auto';
                break;

            case 'right':
            default:
                $data['alignment_text'] = 'text-left';
                $data['box-alignment'] = 'lg:ms-auto';
                $data['alignment_button'] = 'me-auto';
                break;
        }

        return $data;
    }
}
