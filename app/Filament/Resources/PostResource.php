<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Exception;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $slug = 'posts';

    protected static ?string $label = 'Actividad';

    protected static ?string $pluralLabel = 'Actividades';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->square()
                    ->grow(false)
                    ->extraAttributes(['class' => 'rounded-lg'])
                    ->label(''),
                TextColumn::make('title')
                    ->label('Título')
                    ->grow()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Fecha')
                    ->formatStateUsing(fn ($state, $record): string => Carbon::parse($state)->diffForHumans()." <small class='text-gray-400'>(".Carbon::parse($state)->format('d/m/Y').')</small>')
                    ->html()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('visit')
                        ->label('Visitar')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn ($record): string => route('activities.show', ['slug' => $record->slug]), true),
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    Group::make([

                        TextInput::make('title')
                            ->label('Titulo')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->columnSpanFull()
                            ->required(),
                        TextInput::make('slug')
                            ->prefix(fn ($record): string => $record?->getUrlPrefix() ?? (new Post)->getUrlPrefix())
                            ->label('Slug')
                            ->unique(ignoreRecord: true)
                            ->helperText('URL amigable')
                            ->required()
                            ->unique(Post::class, 'slug', ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Contenido')
                            ->required(),

                        TextInput::make('resume')
                            ->label('Resumen'),

                    ])->columns(1),
                    Group::make([
                        Toggle::make('published')->label('Publicado')
                            ->helperText('Será visible en la web')
                            ->inline()
                            ->default(false)
                            ->columnSpan(1),
                        SpatieMediaLibraryFileUpload::make('image')->nullable()
                            ->disk('public')->directory('actividades'),

                        Section::make('')
                            ->label(false)
                            ->description('Información del evento')
                            ->schema([

                                Textarea::make('address')
                                    ->label('Dirección')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                DateTimePicker::make('date')
                                    ->label('Fecha')
                                    ->seconds(false)
                                    ->nullable()
                                    ->columnSpan(1),
                                Toggle::make('donacion')->label('Donación')
                                    ->helperText('¿Es una actividad de donación?')
                                    ->default(false)
                                    ->columnSpan(1),
                            ]),

                        Placeholder::make('created_at')
                            ->label('Fecha creación')
                            ->extraAttributes(['class' => 'text-gray-400 text-end'])
                            ->inlineLabel()
                            ->columnSpanFull()
                            ->content(fn (?Post $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                        Placeholder::make('updated_at')
                            ->label('Fecha modificación')
                            ->extraAttributes(['class' => 'text-gray-400 text-end'])
                            ->inlineLabel()
                            ->columnSpanFull()
                            ->content(fn (?Post $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                    ])->columnSpanFull()->grow(false),
                ])->from('md')
                    ->columnSpanFull(),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
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
        return ['title'];
    }
}
