<?php

/** @noinspection PhpUndefinedMethodInspection */

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use App\Models\OrderState;
use Exception;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'pedidos';

    protected static ?string $label = 'Pedido';

    protected static ?string $pluralLabel = 'Pedidos';

    protected static ?string $navigationGroup = 'Tienda';

    protected static ?string $navigationIcon = 'heroicon-s-shopping-bag';

    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Nº'),
                TextColumn::make('state.name')
                    ->icon(fn ($record) => $record->state->icono())
                    ->color(fn ($record) => $record->state->colorEstado())
                    ->label('Estado')
                    ->badge()
                    ->searchable(),
                TextColumn::make('items_count')->counts('items')
                    ->alignCenter()->label('Productos'),

                TextColumn::make('total')
                    ->alignCenter()
                    ->formatStateUsing(function ($state) {
                        return convertPrice($state);
                    }),

                TextColumn::make('payment_method')->label('Método de pago')
                    ->alignCenter()
                    ->icon('heroicon-o-credit-card'),
                TextColumn::make('updated_at')
                    ->label('Última modificación')
                    ->sortable()
                    ->alignRight()
                    ->since(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Filter::make('estado')
                    ->form([
                        Select::make('estado')
                            ->options([

                                1 => OrderState::FINALIZADO,
                                2 => OrderState::PENDIENTE,
                                3 => OrderState::PAGADO,
                                4 => OrderState::ENVIADO,
                                5 => OrderState::CANCELADO,
                                6 => OrderState::ERROR,
                            ])
                            ->label('Estado')
                            ->default(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['estado'] == 1,
                                fn (Builder $query, $date): Builder => $query->finalizados())
                            ->when($data['estado'] == 2,
                                fn (Builder $query, $date): Builder => $query->pendientePago())
                            ->when($data['estado'] == 3,
                                fn (Builder $query, $date): Builder => $query->pagados())
                            ->when($data['estado'] == 4,
                                fn (Builder $query, $date): Builder => $query->enviados())
                            ->when($data['estado'] == 5,
                                fn (Builder $query, $date): Builder => $query->cancelados())
                            ->when($data['estado'] == 6,
                                fn (Builder $query, $date): Builder => $query->conErrores());
                    }),
            ])
            ->actions([
                Action::make('update')
                    ->tooltip('Actualizar estado')
                    ->label('Estado')
                    ->icon('heroicon-o-arrow-path')
                    ->url(fn ($record): string => self::getUrl('update', ['record' => $record->getKey()])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                [Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Pedido')
                            ->schema([
                                TextInput::make('number')
                                    ->required(),

                                TextInput::make('shipping')
                                    ->required(),

                                TextInput::make('shipping_cost')
                                    ->required()
                                    ->numeric(),

                                TextInput::make('subtotal')
                                    ->required()
                                    ->numeric(),

                                TextInput::make('taxes')
                                    ->required()
                                    ->numeric(),

                                TextInput::make('payment_method')
                                    ->required(),

                                Placeholder::make('created_at')
                                    ->label('Created Date')
                                    ->content(fn (?Order $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                                Placeholder::make('updated_at')
                                    ->label('Last Modified Date')
                                    ->content(fn (?Order $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                            ]),
                        Tabs\Tab::make('Tab 2')
                            ->schema([
                            ]),

                    ]),

                ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'update' => Pages\UpdateOrder::class::route('/{record}/edit'),
            'view' => Pages\ViewOrders::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
