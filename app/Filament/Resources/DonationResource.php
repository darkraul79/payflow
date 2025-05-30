<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationResource\Pages;
use App\Filament\Resources\DonationResource\RelationManagers\PaymentsRelationManager;
use App\Models\Donation;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static ?string $slug = 'donaciones';

    protected static ?string $pluralLabel = 'donaciones';

    protected static ?string $label = 'donación';

    protected static ?string $navigationIcon = 'heroicon-s-gift';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                /* TextInput::make('amount')
                     ->required()
                     ->numeric(),

                 Placeholder::make('created_at')
                     ->label('Created Date')
                     ->content(fn(?Donation $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                 Placeholder::make('updated_at')
                     ->label('Last Modified Date')
                     ->content(fn(?Donation $record): string => $record?->updated_at?->diffForHumans() ?? '-'),*/
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->with('addresses');
            })
            ->columns([
                TextColumn::make('number')
                    ->label('Nº'),
                TextColumn::make('amount')
                    ->label('Importe')
                    ->alignCenter()
                    ->formatStateUsing(function ($state) {
                        return convertPrice($state);
                    }),
                TextColumn::make('type')
                    ->alignCenter()
                    ->icon(fn($record) => $record->iconType())
                    ->color(fn($record) => $record->colorType())
                    ->label('Tipo')
                    ->badge()
                    ->searchable(),

                TextColumn::make('addresses')
                    ->alignCenter()
                    ->color(function ($state) {
                        return $state ? 'purple' : 'danger';
                    })
                    ->badge()
                    ->label('Certificado')
                    ->formatStateUsing(function ($record) {
                        return $record->certificate() ? 'Sí' : 'No';
                    }),
                TextColumn::make('payments_count')
                    ->label('Pagos')
                    ->counts('payments')
                    ->alignCenter(),
                TextColumn::make('updated_at')
                    ->label('Fecha')
                    ->sortable()
                    ->alignRight()
                    ->since(),

            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([

                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\Listdonations::route('/'),
            'view' => Pages\Viewdonation::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
        ];
    }
}
