<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Facturas')
            ->recordTitleAttribute('number')
            ->columns([
                TextColumn::make('number')->label('Número')->searchable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => convertPrice($state)),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->since()
                    ->sortable()
                    ->alignRight(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->actions([
                Action::make('open')
                    ->label('Ver')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(function (Invoice $record) {
                        return route('invoices.show', $record);
                    })
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }
}
