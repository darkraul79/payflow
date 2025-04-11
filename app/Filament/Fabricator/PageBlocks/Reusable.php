<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class Reusable
{
    public static function BotonFields(): Section
    {
        return Section::make('Botón')
//            ->compact()
            ->persistCollapsed()
            ->collapsible()
            ->label('Botón')
            ->extraAttributes([
                'class' => 'bg-azul-wave',
            ])
            ->schema([
                TextInput::make('button_text')
                    ->label('Texto del botón')
                    ->required(),
                TextInput::make('button_link')
                    ->label('Enlace del botón')
                    ->url()
                    ->required(),
            ]);
    }

    /**
     * @param array $exceptions
     * @return Group
     */
    public static function Basic(array $exceptions = []): Group
    {
        // Exclude the fields in $exceptions from the schema
        $fields = [
            TextInput::make('subtitle')
                ->label('Subtitular')
            ,
            TextInput::make('title')
                ->label('Titular')
            ,
            RichEditor::make('text')
                ->label('Texto')
            ,
        ];
        $fields = array_filter($fields, function ($field) use ($exceptions) {
            return !in_array($field->getName(), $exceptions);
        });
        return Group::make($fields);

    }


    public static function alignment(): Select
    {
        return Select::make('alignment')
            ->label('Alineación')
            ->options([
                'left' => 'Izquierda',
                'center' => 'Centrado',
                'right' => 'Derecha',
            ])
            ->default('right');
    }
}
