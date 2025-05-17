<?php

namespace App\Filament\Pages\Settings;

use Closure;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class Settings extends BaseSettings
{

//    protected static ?string $slug = 'ajustes';
    protected static ?string $label = 'Ajuste';
    protected static ?string $pluralLabel = 'Ajustes';

    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int $navigationSort = 30;

    protected static ?string $navigationIcon = 'heroicon-s-adjustments-horizontal';

    public static function getNavigationLabel(): string
    {
        return 'Ajustes';
    }

    public function getTitle(): string
    {
        return 'Ajustes';
    }

    public function schema(): array|Closure
    {
        return [
            Tabs::make('Settings')
                ->schema([
                    Tabs\Tab::make('Contacto')
                        ->label('Datos de contacto')
                        ->columns(2)
                        ->schema([
                            TextInput::make('contact.email')
                                ->label('Email de contacto')
                                ->columnSpanFull()
                                ->required(),
                            TextInput::make('contact.horario')
                                ->label('Horario de atención')
                                ->helperText('Ejemplo: Lunes a Viernes de 9:00 a 18:00')
                                ->columnSpan(1)
                                ->required(),
                            TextInput::make('contact.telefono')
                                ->label('Télefono de contacto')
                                ->columnSpan(1)
                                ->required(),
                        ]),
                    Tabs\Tab::make('Rss')
                        ->columns(4)
                        ->schema([
                            TextInput::make('rss.facebook')
                                ->label('Facebook')
                                ->hintIcon('bi-facebook'),
                            TextInput::make('rss.x')
                                ->label('Twitter/X')
                                ->hintIcon('bi-twitter-x'),
                            TextInput::make('rss.instagram')
                                ->label('Instagram')
                                ->hintIcon('bi-instagram'),
                            TextInput::make('rss.youtube')
                                ->label('Youtube')
                                ->hintIcon('bi-youtube'),
                        ]),
                ]),
        ];
    }
}
