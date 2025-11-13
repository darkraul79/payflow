<?php

namespace App\Filament\Resources;

use App\Filament\Fabricator\PageBlocks\Reusable;
use App\Filament\Resources\SponsorResource\Pages\ListSponsors;
use App\Models\Sponsor;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SponsorResource extends Resource
{
    protected static ?string $model = Sponsor::class;

    protected static ?string $slug = 'patrocinadores';

    protected static ?string $pluralModelLabel = 'patrocinadores';

    protected static ?string $label = 'patrocinador';

    protected static ?int $navigationSort = 31;

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $navigationIcon = 'bi-apple';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('image')
                    ->conversion('card-thumb')
                    ->label('Logo')
                    ->collection('sponsors')
                    ->required()
                    ->acceptedFileTypes(['image/*'])
                    ->columnSpanFull(),

                Group::make([

                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->columnSpan(2),
                    TextInput::make('url')
                        ->columnSpan(2)
                        ->url(),

                    TextInput::make('order')
                        ->label('Orden')
                        ->required()
                        ->columnSpan(1)
                        ->integer(),
                ])->columns(5)->columnSpanFull(),

                Reusable::datesInfo(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('sponsors')
                    ->conversion('icon')
                    ->circular()
                    ->grow(false)
                    ->extraAttributes(['class' => 'rounded-lg'])
                    ->label(''),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('url')
                    ->label('URL')
                    ->grow()
                    ->searchable()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('order')
                    ->alignCenter(),
            ])
            ->defaultSort('order')
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSponsors::route('/'),
            //            'create' => CreateSponsor::route('/create'),
            //            'edit' => EditSponsor::route('/{record}/edit'),
        ];
    }
}
