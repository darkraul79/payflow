<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $slug = 'ajustes';
    protected static ?string $label = 'Ajuste';
    protected static ?string $pluralLabel = 'Ajustes';

    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int $navigationSort = 34;

    protected static ?string $navigationIcon = 'heroicon-s-adjustments-horizontal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('property')
                    ->helperText('En minúsculas y separado por guiones bajos')
                    ->hintIcon('heroicon-o-cog')
                    ->label('Propiedad')
                    ->columnSpanFull()
                    ->required(),

                TextInput::make('value')
                    ->label('Valor')
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property'),

                TextColumn::make('value'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
//            'create' => Pages\CreateSetting::route('/create'),
//            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}
