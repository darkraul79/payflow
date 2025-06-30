<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationResource\Pages;
use App\Filament\Resources\DonationResource\RelationManagers\PaymentsRelationManager;
use App\Models\Donation;
use App\Models\State;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
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

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationGroup = 'Donaciones';

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

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->with('addresses');
            })
            ->columns([
                TextColumn::make('number')
                    ->searchable()
                    ->label('Nº'),
                TextColumn::make('state.name')
                    ->alignCenter()
                    ->icon(fn($record) => $record->state->icono())
                    ->color(fn($record) => $record->state->colorEstado())
                    ->label('Estado')
                    ->badge()
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Importe')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(function ($state) {
                        return convertPrice($state);
                    }),
                TextColumn::make('created_at')
                    ->label('Certificado')
                    ->alignCenter()
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->icon(fn(Donation $record) => $record->certificate() ? 'heroicon-m-check-badge' : '')
                    ->iconColor(fn(Donation $record) => $record->certificate() ? 'lime' : 'gray')
                    ->formatStateUsing(fn(Donation $record): string => $record->certificate() ? 'Si' : ''),
                TextColumn::make('payments_count')
                    ->label('Pagos')
                    ->counts('payments')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label('Fecha')
                    ->sortable()
                    ->color('gray')
                    ->dateTimeTooltip()
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->alignRight()
                    ->since(),
                TextColumn::make('type')
                    ->alignCenter()
                    ->sortable()
                    ->icon(fn($record) => $record->iconType())
                    ->color(fn($record) => $record->colorType())
                    ->label('Tipo')
                    ->badge()
                    ->searchable(),

            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([

                //                ViewAction::make(),
                Action::make('cancelar')
                    ->label('Cancelar')
                    ->requiresConfirmation()
                    ->action(fn(Donation $record) => $record->cancel())
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn(Donation $record) => $record->type === Donation::RECURRENTE &&
                        $record->state?->name === State::ACTIVA),
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
