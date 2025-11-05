<?php

namespace App\Filament\Pages\Settings;

use Closure;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
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
                    Tabs\Tab::make('Facturación')
                        ->columns(12)
                        ->schema([
                            Section::make('Datos fiscales de la entidad')
                                ->columns(12)
                                ->schema([
                                    TextInput::make('billing.company')->label('Razón social')->columnSpan(6)->required(),
                                    TextInput::make('billing.nif')->label('NIF/CIF')->columnSpan(3)->required(),
                                    TextInput::make('billing.email')->label('Email')->columnSpan(3)->required(),
                                    TextInput::make('billing.phone')->label('Teléfono')->columnSpan(3),
                                    TextInput::make('billing.address')->label('Dirección')->columnSpan(6)->required(),
                                    TextInput::make('billing.postal_code')->label('CP')->columnSpan(2)->required(),
                                    TextInput::make('billing.city')->label('Ciudad')->columnSpan(4)->required(),
                                    TextInput::make('billing.country')->label('País')->columnSpan(3)->required(),
                                ]),
                            Section::make('IVA por defecto')
                                ->columns(6)
                                ->schema([
                                    Grid::make(6)->schema([
                                        TextInput::make('billing.vat.orders_default')
                                            ->label('Pedidos IVA % (por defecto)')
                                            ->numeric()
                                            ->default(21)
                                            ->columnSpan(3)
                                            ->helperText('Porcentaje, p.ej. 21 para 21%'),
                                        TextInput::make('billing.vat.donations_default')
                                            ->label('Donaciones IVA % (por defecto)')
                                            ->numeric()
                                            ->default(0)
                                            ->columnSpan(3)
                                            ->helperText('Porcentaje, p.ej. 0 para exento'),
                                    ]),
                                ]),
                            Section::make('Branding')
                                ->columns(6)
                                ->schema([
                                    FileUpload::make('billing.logo_path')
                                        ->label('Logo para PDF')
                                        ->disk('public')
                                        ->directory('branding')
                                        ->image()
                                        ->imageEditor()
                                        ->preserveFilenames()
                                        ->helperText('Opcional. Si no se configura, se intentará usar public/images/logo-fundacion-horizontal.svg o .png'),
                                ]),
                        ]),
                ]),
        ];
    }
}
