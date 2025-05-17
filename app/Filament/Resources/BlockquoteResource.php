<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlockquoteResource\Pages;
use App\Models\Blockquote;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlockquoteResource extends Resource
{
    protected static ?string $model = Blockquote::class;

    protected static ?string $label = 'Inspiración';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $pluralLabel = 'Inspiraciones';

    protected static ?int $navigationSort = 33;
    protected static ?string $slug = 'blockquotes';

    //    protected static ?string $navigationIcon = 'heroicon-o-code-bracket-square';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('text')
                ->helperText('Introduce una frase inspiradora sin usar comillas')
                ->prefixIcon('heroicon-o-code-bracket-square')
                ->label('Frase')
                ->columnSpanFull()
                ->required(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('text')->label(''),
                TextColumn::make('created_at')->label('Creada')->since(),

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
            'index' => Pages\ListBlockquotes::route('/'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}
