<?php

namespace App\Filament\Resources;

use App\Filament\Fabricator\PageBlocks\Reusable;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Closure;
use Exception;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;
use Z3d0X\FilamentFabricator\Forms\Components\PageBuilder;
use Z3d0X\FilamentFabricator\Models\Contracts\Page as PageContract;
use Z3d0X\FilamentFabricator\View\ResourceSchemaSlot;

class PageResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $slug = 'paginas';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getModel(): string
    {
        return Page::class;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Group::make()
                    ->schema([
                        Group::make()->schema(FilamentFabricator::getSchemaSlot(ResourceSchemaSlot::BLOCKS_BEFORE)),

                        PageBuilder::make('blocks')
                            ->label(__('filament-fabricator::page-resource.labels.blocks')),

                        Group::make()->schema(FilamentFabricator::getSchemaSlot(ResourceSchemaSlot::BLOCKS_AFTER)),
                    ])
                    ->columnSpan(2),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Group::make()->schema(FilamentFabricator::getSchemaSlot(ResourceSchemaSlot::SIDEBAR_BEFORE)),

                        Section::make()
                            ->schema([

                                TextInput::make('title')
                                    ->label('Título')
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?PageContract $record) {
                                        if (!$get('is_slug_changed_manually') && filled($state) && blank($record)) {
                                            $set('slug', Str::slug($state, language: config('app.locale', 'en')));
                                        }
                                    })
                                    ->debounce('500ms')
                                    ->required(),

                                Hidden::make('is_slug_changed_manually')
                                    ->default(false)
                                    ->dehydrated(false),

                                TextInput::make('slug')
                                    ->label('Url')
                                    ->unique(ignoreRecord: true, modifyRuleUsing: fn(Unique $rule, Get $get) => $rule->where('parent_id', $get('parent_id')))
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('is_slug_changed_manually', true);
                                    })
                                    ->rule(function ($state) {
                                        return function (string $attribute, $value, Closure $fail) use ($state) {
                                            if ($state !== '/' && (Str::startsWith($value, '/') || Str::endsWith($value, '/'))) {
                                                $fail(__('filament-fabricator::page-resource.errors.slug_starts_or_ends_with_slash'));
                                            }
                                        };
                                    })
                                    ->required(),
                                Placeholder::make('page_url')
                                    ->label('')
                                    ->visible(fn(?PageContract $record) => config('filament-fabricator.routing.enabled') && filled($record))
                                    ->content(fn(?PageContract $record) => FilamentFabricator::getPageUrlFromId($record?->id)),

                                Select::make('layout')
                                    ->label('Plantilla')
                                    ->options(FilamentFabricator::getLayouts())
                                    ->default(fn() => FilamentFabricator::getDefaultLayoutName())
                                    ->live()
                                    ->required(),

                                Select::make('parent_id')
                                    ->label('Página padre')
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->suffixAction(
                                        fn($get, $context) => FormAction::make($context . '-parent')
                                            ->icon('heroicon-o-arrow-top-right-on-square')
                                            ->url(fn() => PageResource::getUrl($context, ['record' => $get('parent_id')]))
                                            ->openUrlInNewTab()
                                            ->visible(fn() => filled($get('parent_id')))
                                    )
                                    ->relationship(
                                        'parent',
                                        'title',
                                        function (Builder $query, ?PageContract $record) {
                                            if (filled($record)) {
                                                $query->where('id', '!=', $record->id);
                                            }
                                        }
                                    ),
                                Select::make('blockquotes')
                                    ->hidden(fn(Get $get) => $get('is_home'))
                                    ->searchable()
                                    ->label('Frase inspiración')
                                    ->preload()
                                    ->relationship(name: 'blockquotes', titleAttribute: 'text'),
                            ]),

                        Group::make()->schema(FilamentFabricator::getSchemaSlot(ResourceSchemaSlot::SIDEBAR_AFTER)),
                    ]),

            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parent.title')
                    ->label('Pertenece a')
                    ->extraAttributes(['class' => 'text-gray-500'])
                    ->toggleable()
                    ->formatStateUsing(fn($state): HtmlString => new HtmlString('<span class="text-xs text-gray-600"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-3 inline-block">
  <path fill-rule="evenodd" d="M3.74 3.749a.75.75 0 0 1 .75.75V15h13.938l-2.47-2.47a.75.75 0 0 1 1.061-1.06l3.75 3.75a.75.75 0 0 1 0 1.06l-3.75 3.75a.75.75 0 0 1-1.06-1.06l2.47-2.47H3.738a.75.75 0 0 1-.75-.75V4.5a.75.75 0 0 1 .75-.751Z" clip-rule="evenodd" />
</svg>
' . $state . '</span>' ?? '-'))
                    ->url(fn(?PageContract $record) => filled($record->parent_id) ? PageResource::getUrl('edit', ['record' => $record->parent_id]) : null),

                TextColumn::make('blockquotes.text')
                    ->label('Frase inspiración')
                    ->wrap()
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->limit(20)
                    ->tooltip(function ($record): ?string {
                        return $record->blockquotes->first()?->text;
                    })
                    ->toggleable()
                    ->color('secondary'),

                TextColumn::make('layout')
                    ->label('Plantilla')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'default' => 'primary',
                        'donacion' => 'success',
                    })
                    ->toggleable()
                    ->sortable(),

                Reusable::publicado('App\Models\Page'),

            ])
            ->filters([
                SelectFilter::make('layout')
                    ->label('Plantilla')
                    ->options(FilamentFabricator::getLayouts()),
            ])
            ->actions([
                ViewAction::make()
                    ->visible(config('filament-fabricator.enable-view-page')),
                EditAction::make(),
                Action::make('visit')
                    ->label('Visitar')
                    ->url(fn(?PageContract $record) => FilamentFabricator::getPageUrlFromId($record->id, true) ?: null)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->openUrlInNewTab()
                    ->color('secondary')
                    ->visible(config('filament-fabricator.routing.enabled')),
            ])
            ->bulkActions([]);
    }

    public static function getModelLabel(): string
    {
        return 'Página';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Páginas';
    }

    public static function getPages(): array
    {
        return array_filter([
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'view' => config('filament-fabricator.enable-view-page') ? Pages\ViewPage::route('/{record}') : null,
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ]);
    }
}
