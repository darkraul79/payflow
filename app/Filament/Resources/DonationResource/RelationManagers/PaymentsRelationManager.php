<?php

namespace App\Filament\Resources\DonationResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $pluralLabel = 'Pagos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pagos')
            ->description('Pagos realizados para esta donación')
            ->recordTitleAttribute('name')
            ->defaultSort('updated_at', 'desc')
            ->columns([

                TextColumn::make('amount')
                    ->label('Importe')
                    ->alignCenter()
                    ->verticallyAlignStart()
                    ->formatStateUsing(function ($state) {
                        return convertPrice($state);
                    }),
                TextColumn::make('updated_at')
                    ->label('Fecha')
                    ->sortable()
                    ->verticallyAlignStart()
                    ->alignLeft()
                    ->since(),
                ViewColumn::make('info')->view('filament.tables.columns.info')->label(false)->grow()
                ,
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
