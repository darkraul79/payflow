<?php

namespace App\Filament\Resources;

use App\Filament\Fabricator\PageBlocks\Reusable;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Exception;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $slug = 'products';

    protected static ?string $navigationGroup = 'Tienda';

    protected static ?string $pluralModelLabel = 'Productos';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    Group::make([

                        TextInput::make('name')
                            ->label('Nombre')
                            ->columnSpan(4)
                            ->required(),

                        TextInput::make('stock')
                            ->required()
                            ->integer()
                            ->numeric()
                            ->columnSpan(1),
                        Reusable::SlugField('App\Models\Product'),
                        Reusable::richTextEditor(field: 'description', label: 'Descripción')
                            ->columnSpanFull(),
                        Section::make('Imágenes de producto')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('image')
                                    ->label(false)
                                    ->reorderable()
                                    ->collection('product_images')
                                    ->multiple()
                                    ->disk('public')->nullable(),
                            ]),

                    ])->columns(5),
                    Group::make([
                        Toggle::make('published')->label('Publicado')
                            ->helperText('Será visible en la web')
                            ->inline()
                            ->default(false)
                            ->columnSpan(1),

                        Section::make('Precio de venta')
                            ->columns(1)
                            ->schema([
                                TextInput::make('price')
                                    ->label('Precio')
                                    ->placeholder('0.00')
                                    ->required(),
                                Toggle::make('oferta')
                                    ->label('Oferta?')
                                    ->reactive()
                                    ->helperText('¿Es una oferta?')
                                    ->inline()
                                    ->default(false),

                                TextInput::make('offer_price')
                                    ->label('Precio en oferta')
                                    ->hidden(fn (callable $get): bool => ! $get('oferta'))
                                    ->placeholder('0.00')
                                    ->required(fn (callable $get): bool => $get('oferta')),
                            ])->columnSpanFull(),

                        Reusable::donacion(),
                        Select::make('tags')
                            ->searchable()
                            ->label('Etiquetas')
                            ->preload()
                            ->multiple()
                            ->relationship(titleAttribute: 'name'),
                        Reusable::datesInfo(),

                    ])->grow(false),

                ])->columnSpanFull(),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        $type = 'App\Models\Product';

        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->filterMediaUsing(
                        fn (Collection $media): Collection => $media->where(
                            'order_column',
                            1,
                        ),
                    )
                    ->collection('product_images')
                    ->conversion('thumb')
                    ->grow(false)
                    ->extraAttributes(['class' => 'rounded-lg'])
                    ->label(''),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->grow()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Precio')
                    ->html()
                    ->formatStateUsing(fn (Product $record): string => $record->getFormatedPriceWithDiscount()),

                TextColumn::make('stock')
                    ->alignCenter()
                    ->color(fn ($record) => $record->stock > 0 ? 'success' : 'danger'),

                TextColumn::make('description')
                    ->limit(20)
                    ->html(),

                Reusable::donacionTable(),
                Reusable::dateTable($type),
                Reusable::publicado($type),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('visit')
                        ->label('Visitar')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(function (Product $record) {
                            return $record->getLink();
                        }),
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ]),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
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
        return ['name', 'description'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Descripción' => new HtmlString(strip_tags(Str::limit($record->description, 20))),
            'Precio' => new HtmlString($record->getFormatedPriceWithDiscount()),
            'Stock' => $record->stock,
        ];
    }
}
