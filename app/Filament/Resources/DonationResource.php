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
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
                    ->icon(fn (?Model $record) => $record?->state->icono())
                    ->color(fn (?Model $record) => $record?->state->colorEstado())
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
                    ->icon(fn (?Donation $record) => $record?->certificate() ? 'heroicon-m-check-badge' : '')
                    ->iconColor(fn (?Donation $record) => $record?->certificate() ? 'lime' : 'gray')
                    ->formatStateUsing(fn (?Donation $record): string => $record?->certificate() ? 'Si' : ''),
                TextColumn::make('payments_count')
                    ->label('Pagos')
                    ->sortable()
                    ->html()
                    ->alignCenter()
                    ->color('gray')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->formatStateUsing(function ($state, ?Donation $record) {
                        $sum = $record?->payments_sum_amount ?? 0;

                        return sprintf('%s (%d)', convertPrice($sum), $state);
                    }),

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
                    ->icon(fn (?Model $record) => $record?->iconType())
                    ->color(fn (?Model $record) => $record?->colorType())
                    ->label('Tipo')
                    ->badge()
                    ->searchable(),

            ])
            ->recordClasses(fn (Model $record
            ) => $record->payments_sum_amount == 0 ? ' table-td-error' : '')
            ->defaultSort('updated_at', 'desc')
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('state')
                    ->label('Estado')
                    ->multiple()
                    ->options(function () {
                        $op = Donation::make()->available_states();
                        unset($op['ACEPTADO']);

                        return array_combine($op, $op);
                    })
                    ->query(function (Builder $query, array $data): Builder {

                        if (empty($data['values'])) {
                            return $query;
                        }

                        return $query->whereHas('state', function (Builder $query) use ($data) {
                            $query->whereIn('name', $data['values']);
                        });
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query->with('addresses')->withCount('payments')->withSum('payments', 'amount');
            })
            ->actions([

                //                ViewAction::make(),
                Action::make('cancelar')
                    ->label('Cancelar')
                    ->requiresConfirmation()
                    ->action(fn (?Donation $record) => $record?->cancel())
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (?Donation $record) => $record?->type === Donation::RECURRENTE &&
                        $record?->state?->name === State::ACTIVA),
                DeleteAction::make()
                    ->visible(fn (?Donation $record
                    ) => $record->state?->name === State::ERROR || $record->state == null),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => $record->state?->name === State::ERROR || $record->state == null,
            );
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
