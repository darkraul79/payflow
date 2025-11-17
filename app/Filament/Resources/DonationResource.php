<?php

namespace App\Filament\Resources;

use App\Enums\DonationType;
use App\Enums\OrderStatus;
use App\Filament\Fabricator\PageBlocks\Reusable;
use App\Filament\Resources\DonationResource\Pages;
use App\Filament\Resources\DonationResource\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\DonationResource\RelationManagers\PaymentsRelationManager;
use App\Models\Donation;
use App\Services\InvoiceService;
use Exception;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
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
use Illuminate\Support\Collection;

class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static ?string $slug = 'donaciones';

    protected static ?string $pluralLabel = 'donaciones';

    protected static ?string $label = 'donación';

    protected static ?string $navigationIcon = 'heroicon-s-gift';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationGroup = 'Donaciones';

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

                Reusable::paymnentMethodColumn(),
                Reusable::facturaColumn(),
                TextColumn::make('updated_at')
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

                TextColumn::make('created_at')
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
            ->defaultSort('created_at', 'desc')
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
                /*Action::make('view_invoice')
                    ->label('Ver factura')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->visible(fn (Donation $record) => $record->invoices()->exists())
                    ->url(fn (Donation $record) => route('invoices.show', $record->invoices()->first()))
                    ->openUrlInNewTab(),*/

                //                ViewAction::make(),
                Action::make('cancelar')
                    ->label('Cancelar')
                    ->requiresConfirmation()
                    ->action(fn (?Donation $record) => $record?->cancel())
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (?Donation $record) => $record?->type === DonationType::RECURRENTE->value &&
                        $record?->state?->name === OrderStatus::ACTIVA->value),
                DeleteAction::make()
                    ->visible(fn (?Donation $record
                    ) => $record->state?->name === OrderStatus::ERROR->value || $record->state == null),
                ForceDeleteAction::make(),
                RestoreAction::make(),
                Reusable::facturaActions(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    BulkAction::make('invoice_bulk')
                        ->label('Generar facturas')
                        ->icon('heroicon-o-document-text')
                        ->form([
                            Toggle::make('send_email')->label('Enviar por email')->default(true),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $service = app(InvoiceService::class);
                            $count = 0;
                            $warned = 0;
                            foreach ($records as $donation) {
                                $send = (bool) ($data['send_email'] ?? false);
                                if ($send && ! ($donation->certificate()?->email)) {
                                    $send = false;
                                    $warned++;
                                }
                                $service->generateForDonation($donation, sendEmail: $send, force: true);
                                $count++;
                            }
                            $note = $warned > 0 ? " ($warned sin email, no enviados)" : '';
                            Notification::make()
                                ->success()
                                ->title('Facturas generadas')
                                ->body($count.' factura(s) creadas'.$note)
                                ->send();
                        }),
                ]),
            ])->checkIfRecordIsSelectableUsing(
                fn (Model $record
                ): bool => $record->state?->name === OrderStatus::ERROR->value || $record->state == null,
            );
    }

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
            InvoicesRelationManager::class,
        ];
    }
}
