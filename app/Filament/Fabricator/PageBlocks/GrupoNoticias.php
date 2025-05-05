<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Post;
use App\Models\Tag;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class GrupoNoticias extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('grupo-noticias')
            ->schema([
                //
                Reusable::Basic(),
                Section::make()
                    ->label('Grupo de Noticias')
                    ->description('Grupo de Noticias')
                    ->schema([
                        Repeater::make('items')
                            ->reorderable()
                            ->collapsible()
                            ->schema([
                                Reusable::Basic(),
                                TextInput::make('number')
                                    ->label('Número de noticias')
                                    ->live()
                                    ->numeric()
                                    ->maxValue(3)
                                    ->minValue(1)->default(3),
                                Select::make('tags_id')
                                    ->label('Categorías a mostrar')
                                    ->options(fn(): array => Tag::all()->pluck('name', 'id')->toArray())
                                    ->preload()
                                    ->searchable()
                                    ->maxItems(fn(Get $get): int => $get('number'))
                                    ->columnSpan(1)
                                    ->multiple(),
                            ]),
                    ]),
            ]);
    }

    public static function mutateData(array $data): array
    {

        foreach ($data['items'] as $index => $item) {
            $data['items'][$index]['posts'] = Post::whereHas('tags', function ($query) use ($item) {
                return $query->whereIn('id', $item['tags_id']);
            })->limit($data['number'] ?? 5)->get();
        }

        return $data;
    }
}
