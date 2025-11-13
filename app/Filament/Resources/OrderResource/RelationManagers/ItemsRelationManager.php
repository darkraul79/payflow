<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $label = 'Resumen';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product')
            ->heading(false)
            ->columns([
                SpatieMediaLibraryImageColumn::make('product.media')
                    ->collection('product_images')
                    ->alignLeft()
                    ->limit(1)
                    ->conversion('thumb')
                    ->extraAttributes(['class' => 'rounded-lg'])
                    ->label('Producto'),
                Tables\Columns\TextColumn::make('product.name')->label('')->grow(),
                Tables\Columns\TextColumn::make('quantity')->label('Cantidad')->alignCenter(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->alignEnd()
                    ->formatStateUsing(function ($state) {
                        return convertPrice($state);
                    })
                    ->label('Subtotal'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->paginated(false);
    }
}
