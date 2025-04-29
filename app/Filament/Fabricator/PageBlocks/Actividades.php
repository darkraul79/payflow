<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Post;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Actividades extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('actividades')
            ->schema([

                Reusable::Basic(['text']),
                TextInput::make('number')
                    ->label('Número de actividades')
                    ->live()
                    ->numeric()
                    ->maxValue(3)
                    ->minValue(1)
                    ->default(3),

                Fieldset::make('Tipo de actividades')
                    ->schema([
                        Select::make('type')
                            ->label('Actividades a mostrar')
                            ->live()
                            ->options([
                                'latest' => 'Últimas actividades',
                                'next_activities' => 'Próximas actividades',
                                'manual' => 'Manual',
                            ])
                            ->default('latest'),
                        Select::make('activities_id')
                            ->visible(fn(Get $get): bool => $get('type') === 'manual')
                            ->options(fn(): array => Post::query()->published()->pluck('title', 'id')->toArray())
                            ->preload()
                            ->searchable()
                            ->maxItems(fn(Get $get): int => $get('number'))
                            ->columnSpan(1)
                            ->multiple(),
                    ]),

            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['classGrid'] = 'md:grid-cols-2 lg:grid-cols-' . $data['number'] . ' gap-4';
        switch ($data['type']) {
            case 'latest':
                $data['activities'] = Post::query()
                    ->published()
                    ->orderBy('date', 'desc')
                    ->limit($data['number'])
                    ->get();
                break;
            case 'next_activities':
                $data['activities'] = Post::query()
                    ->published()
                    ->next_activities()
                    ->orderBy('date', 'asc')
                    ->limit($data['number'])
                    ->get();
                break;
            case 'manual':
                $data['activities'] = Post::query()
                    ->published()
                    ->whereIn('id', $data['activities_id'])
                    ->orderBy('date', 'desc')
                    ->limit($data['number'])
                    ->get();
                break;
            default:
        }

        return $data;
    }
}
