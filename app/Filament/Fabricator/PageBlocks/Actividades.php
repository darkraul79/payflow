<?php

namespace App\Filament\Fabricator\PageBlocks;

use app\Models\Activity;
use app\Models\News;
use app\Models\Proyect;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Actividades extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('actividades')
            ->schema([

                ToggleButtons::make('alignment')
                    ->options([
                        'text-left' => '',
                        'text-center' => '',
                        'text-end' => '',
                    ])
                    ->icons([
                        'text-left' => 'heroicon-o-bars-3-bottom-left',
                        'text-center' => 'heroicon-o-bars-3',
                        'text-end' => 'heroicon-o-bars-3-bottom-right',
                    ])
                    ->label('Alineación')
                    ->grouped()
                    ->columnSpan(1),

                Reusable::Basic(),

                Fieldset::make('Tipo de actividades')
                    ->schema([
                        Select::make('type')
                            ->label('Tipo de contenido:')
                            ->live()
                            ->options([
                                'Activity' => 'Actividades',
                                'News' => 'Noticias',
                                'Proyect' => 'Proyectos',
                            ])
                            ->required()
                            ->columnSpanFull()
                            ->default('Activity'),
                        TextInput::make('number')
                            ->label('Número de actividades')
                            ->live()
                            ->numeric()
                            ->maxValue(3)
                            ->minValue(1)
                            ->default(3),
                        Select::make('filter')
                            ->label('Filtro:')
                            ->live()
                            ->options([
                                'latest' => 'Últimas',
                                'next_activities' => 'Próximas',
                                'manual' => 'Manual',
                                'all' => 'Todas',
                            ])
                            ->default('latest'),
                        Select::make('activities_id')
                            ->label('Actividades')
                            ->visible(fn (Get $get): bool => $get('filter') === 'manual')
                            ->options(fn (Get $get): array => match ($get('type')) {
                                'Activity' => Activity::query()->published()->pluck('title', 'id')->toArray(),
                                'News' => News::query()->published()->pluck('title', 'id')->toArray(),
                                'Proyect' => Proyect::query()->published()->pluck('title', 'id')->toArray(),
                            }
                            )
                            ->preload()
                            ->searchable()
                            ->columnSpan(1)
                            ->multiple(),
                    ]),

            ]);
    }

    public static function mutateData(array $data): array
    {
        switch ($data['filter']) {
            default:
            case 'latest':
                $data['activities'] = $data['type']
                    ? resolve('App\\Models\\'.$data['type'])::query()
                        ->latest_activities()
                        ->get() : [];
                break;
            case 'next_activities':
                $data['activities'] = $data['type']
                    ? resolve('App\\Models\\'.$data['type'])::query()
                        ->next_activities()
                        ->get() : [];
                break;
            case 'manual':
                $data['activities'] = $data['type']
                    ? resolve('App\\Models\\'.$data['type'])::query()
                        ->manual(ids: $data['activities_id'])
                        ->get() : [];
                break;
            case 'all':
                $data['activities'] = $data['type']
                    ? resolve('App\\Models\\'.$data['type'])::query()
                        ->all_activities()
                        ->get() : [];
                break;
        }

        return $data;
    }
}
