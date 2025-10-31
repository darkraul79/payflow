<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingMethodResource\Pages;
use App\Models\ShippingMethod;
use Exception;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShippingMethodResource extends Resource
{
    protected static ?string $model = ShippingMethod::class;

    protected static ?string $slug = 'metodos-envio';

    protected static ?string $modelLabel = 'Método de envío';

    protected static ?string $pluralLabel = 'Métodos de envío';

    protected static ?string $navigationGroup = 'Tienda';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    Group::make([

                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('price')
                            ->label('Precio')
                            ->suffixIcon('heroicon-o-currency-euro')
                            ->placeholder('0.00')
                            ->required()
                            ->numeric()
                            ->columnSpan(2),

                    ])->columns(5)->columnSpan(2),
                    Group::make([
                        Toggle::make('active')->label('Visible')
                            ->extraAttributes(['class' => 'text-right'])
                            ->inline()
                            ->default(false)
                            ->columnSpan(1),

                        Section::make('Por fechas')
                            ->icon('heroicon-s-calendar')
                            ->compact()
                            ->description('Mostrar en fechas desde / hasta, si no se rellenan, se mostrará siempre')
                            ->collapsible()
                            ->collapsed(fn (?Model $record) => $record?->from === null && $record?->until === null)
                            ->schema([
                                DatePicker::make('from')
                                    ->label('Desde'),

                                DatePicker::make('until')
                                    ->label('Hasta'),

                            ])->columnSpanFull(),

                        Section::make('Por importe')
                            ->icon('heroicon-s-currency-euro')
                            ->compact()
                            ->description('Mostrar cuando el importe de la compra sea mayor o igual al valor, si no se rellena, se mostrará siempre')
                            ->columns(1)
                            ->collapsible()
                            ->collapsed(fn (?Model $record) => $record?->greater === null)
                            ->schema([
                                TextInput::make('greater')
                                    ->label('Importe mínimo')
                                    ->prefixIcon('heroicon-o-arrow-trending-up')
                                    ->suffixIcon('heroicon-o-currency-euro')
                                    ->numeric(),

                            ])->columnSpanFull(),

                    ])->columnSpan(1),

                ])->from('md')->columnSpanFull()->columns(5),

            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->alignCenter()
                    ->label('Precio')
                    ->badge(fn (string $state): string => $state == 0)
                    ->color(fn (string $state): string => $state == 0 ? 'success' : 'gray')
                    ->html()
                    ->sortable()
                    ->formatStateUsing(fn ($record): string => $record?->getFormatedPrice()),

                ToggleColumn::make('active')
                    ->alignCenter()
                    ->label('Visible')
                    ->sortable(),

                TextColumn::make('from')
                    ->label('Por fechas')
                    ->color(fn ($record) => $record->isVisibleToday() ? 'success' : 'danger')
                    ->alignCenter()
                    ->badge()
                    ->html()
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->tooltip(fn ($record): string => $record->isVisibleToday() ? $record->from?->format('d/m/Y').' - '.$record->until?->format('d/m/Y') : false)
                    ->formatStateUsing(function (Model $record): string {
                        return $record->isVisibleToday() ? 'activo' : 'oculto';
                    }),

                TextColumn::make('greater')->label('Importe mínimo')
                    ->prefix('>')
                    ->color('danger')
                    ->alignCenter()
                    ->formatStateUsing(fn (float $state): string => convertPrice($state)),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingMethods::route('/'),
            'create' => Pages\CreateShippingMethod::route('/create'),
            'edit' => Pages\EditShippingMethod::route('/{record}/edit'),
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
        return ['name'];
    }
}
