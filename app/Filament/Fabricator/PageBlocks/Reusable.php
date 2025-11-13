<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\State;
use App\Services\InvoiceService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class Reusable
{
    public static function BotonFields(): Section
    {
        return Section::make('Botón')
//            ->compact()
            ->persistCollapsed()
            ->collapsible()
            ->label('Botón')
            ->extraAttributes([
                'class' => 'bg-azul-wave',
            ])
            ->schema([
                TextInput::make('button_text')
                    ->label('Texto del botón')
                    ->required(),
                TextInput::make('button_link')
                    ->label('Enlace del botón')
                    ->url()
                    ->required(),
            ]);
    }

    public static function Basic(array $exceptions = []): Group
    {
        // Exclude the fields in $exceptions from the schema
        $fields = [
            TextInput::make('subtitle')
                ->label('Subtitular'),
            TextInput::make('title')
                ->label('Titular'),
            RichEditor::make('text')
                ->label('Texto'),
        ];
        $fields = array_filter($fields, function ($field) use ($exceptions) {
            return ! in_array($field->getName(), $exceptions);
        });

        return Group::make($fields);

    }

    public static function datesInfo(): Group
    {
        return Group::make([
            Placeholder::make('created_at')
                ->visible(fn ($record) => $record)
                ->label('Fecha creación')
                ->extraAttributes(['class' => 'text-gray-400 text-end'])
                ->inlineLabel()
                ->columnSpanFull()
                ->content(fn ($record): string => $record?->created_at?->diffForHumans() ?? '-'),

            Placeholder::make('updated_at')
                ->label('Fecha modificación')
                ->visible(fn ($record) => $record)
                ->extraAttributes(['class' => 'text-gray-400 text-end'])
                ->inlineLabel()
                ->columnSpanFull()
                ->content(fn ($record): string => $record?->updated_at?->diffForHumans() ?? '-'),
        ]);
    }

    public static function Content(string $type)
    {

        return Split::make([
            Group::make([

                TextInput::make('title')
                    ->label('Titulo')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->columnSpanFull()
                    ->required(),
                self::SlugField($type),
                self::richTextEditor(field: 'content', label: 'Contenido')
                    ->columnSpanFull(),

                TextInput::make('resume')
                    ->label('Resumen'),
                Section::make('Galería de imágenes')
                    ->description('Imágenes de la actividad')
                    ->schema([
                        Reusable::imageGallery(),
                    ])->columnSpanFull(),

            ]),
            Group::make([
                Toggle::make('published')->label('Publicado')
                    ->helperText('Será visible en la web')
                    ->inline()
                    ->default(false)
                    ->columnSpan(1),

                Reusable::donacion(),

                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('principal')
                    ->conversion('card-thumb')
                    ->disk('public')->nullable(),

                self::evento($type),
                Select::make('tags')
                    ->searchable()
                    ->label('Etiquetas')
                    ->preload()
                    ->multiple()
                    ->relationship(titleAttribute: 'name'),
                self::datesInfo(),

            ])->grow(false),
        ])->from('md')
            ->columnSpanFull();
    }

    public static function SlugField($type)
    {

        return TextInput::make('slug')
            ->prefix(fn ($record): string => (new $type)->getUrlPrefix(true))
            ->label('Slug')
            ->unique(ignoreRecord: true)
            ->helperText('URL amigable')
            ->required()
            ->unique($type, 'slug', ignoreRecord: true)
            ->maxLength(255)
            ->columnSpanFull();
    }

    public static function richTextEditor(string $field, string $label)
    {
        return RichEditor::make($field)
            ->label($label);
    }

    public static function imageGallery(): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('gallery')
            ->label(false)
            ->collection('gallery')
            ->multiple()
            ->disk('public')
            ->directory('actividades')
            ->nullable();
    }

    public static function donacion(): ToggleButtons
    {

        return ToggleButtons::make('donacion')
            ->grouped()
            ->label(fn (): string => '¿Panel de donación?')
            ->boolean()->inline()
            ->default(false)
            ->columnSpan(1);
    }

    public static function evento($type)
    {
        return match ($type) {
            'App\Models\Activity' => Section::make('')
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
                ]),
            default => Placeholder::make(''),
        };

    }

    public static function genericContentTable($type): array
    {

        return [
            SpatieMediaLibraryImageColumn::make('image')
                ->collection('principal')
                ->conversion('card-thumb')
                ->circular()
                ->grow(false)
                ->extraAttributes(['class' => 'rounded-lg'])
                ->label(''),
            TextColumn::make('title')
                ->label('Título')
                ->grow()
                ->searchable()
                ->sortable(),
            self::donacionTable(),
            self::dateTable($type),
            Reusable::publicado($type),

        ];
    }

    public static function donacionTable(): TextColumn
    {
        return TextColumn::make('donacion')
            ->sortable()
            ->formatStateUsing(function (string $state): string {
                return $state ? 'Si' : 'No';
            })
            ->alignment('center')
            ->color(fn ($state): string => (! $state) ? 'danger' : 'success')
            ->badge();
    }

    public static function alignment(): Select
    {
        return Select::make('alignment')
            ->label('Alineación')
            ->options([
                'left' => 'Izquierda',
                'center' => 'Centrado',
                'right' => 'Derecha',
            ])
            ->default('right');
    }

    public static function dateTable($type)
    {
        return match ($type) {
            'App\Models\Activity' => TextColumn::make('date')
                ->label('Fecha del evento')
                ->formatStateUsing(fn (
                    $state,
                    $record
                ): string => Carbon::parse($state)->diffForHumans()." <small class='text-gray-400'>(".Carbon::parse($state)->format('d/m/Y').')</small>'
                )
                ->alignment('right')
                ->html()
                ->sortable(),
            default => TextColumn::make('updated_at')
                ->label('Fecha de actualización')
                ->alignment('right')
                ->since()
                ->sortable(),
        };
    }

    public static function publicado($type)
    {

        return match ($type) {
            'App\Models\Page' => ToggleColumn::make('published_at')
                ->label('Publicado')
                ->alignment('right')
                ->toggleable()
                ->sortable()
                ->onColor('secondary')
                ->offColor('gray')
                ->updateStateUsing(function ($record, $state) {
                    if ($state) {
                        $record->published_at = now();
                    } else {
                        $record->published_at = null;
                    }
                    $record->save();

                    return $state;
                }),
            default => ToggleColumn::make('published')
                ->label('Publicado')
                ->alignment('right')
                ->toggleable()
                ->sortable()->onColor('secondary')
                ->offColor('gray'),
        };
    }

    public static function facturaColumn(): IconColumn
    {
        return IconColumn::make('latest_invoice')
            ->label('Factura')
            ->tooltip(fn (Model $record) => 'Factura '.$record->invoices()->first()?->number)
            ->getStateUsing(function (Model $record) {
                return $record->invoices()->first()?->number;
            })
            ->url(function (Model $record) {
                $inv = $record->invoices()->first();

                return $inv ? route('invoices.show', $inv) : null;
            })
            ->openUrlInNewTab()
            ->icon('heroicon-o-document-text')
            ->alignCenter();
    }

    public static function facturaActions()
    {

        return ActionGroup::make([
            Action::make('invoice')
                ->label(fn (Model $record
                ) => $record->invoices()->exists() ? 'Regenerar factura' : 'Generar factura')
                ->color(fn (Model $record
                ) => $record->invoices()->exists() ? 'warning' : 'primary')
                ->icon(fn (Model $record
                ) => $record->invoices()->exists() ? 'heroicon-s-arrow-path' : 'heroicon-o-document-currency-euro')
                /* ->visible(function (Model $record
                 ) {
                     return match ($record->getMorphClass()) {
                         'App\Models\Order' => $record->billing_address() && in_array($record->state?->name, [
                             State::PAGADO,
                             State::ENVIADO,
                             State::FINALIZADO,
                         ]),
                         'App\Models\Donation' => (bool) $record->certificate(),
                         default => false,
                     };

                 })*/
                ->form([
                    self::toggleSendEmail(),
                ])
                ->modalSubmitActionLabel(fn (Model $record
                ) => $record->invoices()->exists() ? 'Regenerar' : 'Generar')
                ->action(function (Model $record, array $data) {

                    $service = app(InvoiceService::class);
                    $send = (bool) ($data['send_email'] ?? false);

                    try {
                        switch ($record->getMorphClass()) {
                            case 'App\\Models\\Order':
                                $result = $service->generateForOrder($record, sendEmail: $send, force: true);
                                break;
                            case 'App\\Models\\Donation':
                                // If no email on certificate, force send=false
                                if ($send && ! ($record->certificate()?->email)) {
                                    $send = false;
                                    Notification::make()
                                        ->warning()
                                        ->title('Sin email en el certificado')
                                        ->body('Se generó la factura, pero no se envió por falta de email del donante.')
                                        ->send();
                                }
                                $result = $service->generateForDonation($record, sendEmail: $send, force: true);
                                break;
                            default:
                                throw new RuntimeException('Tipo no soportado para facturación.');
                        }

                        Notification::make()
                            ->success()
                            ->title($record->invoices()->exists() ? 'Factura regenerada' : 'Factura generada')
                            ->body('Número '.$result['invoice']->number)
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->danger()
                            ->title('No se pudo generar la factura')
                            ->body('Revisa permisos de escritura en storage/app/public e intenta de nuevo. Detalle: '.$e->getMessage())
                            ->send();
                    }

                }),

        ])
            ->label('More actions')
            ->icon('heroicon-m-ellipsis-vertical')
            ->size(ActionSize::ExtraSmall);

    }

    public static function toggleSendEmail(): Toggle
    {
        return Toggle::make('send_email')
            ->visible(function (Model $record
            ) {
                return match ($record->getMorphClass()) {
                    'App\Models\Order' => $record->billing_address() && in_array($record->state?->name, [
                        State::PAGADO,
                        State::ENVIADO,
                        State::FINALIZADO,
                    ]),
                    'App\Models\Donation' => (bool) $record->certificate(),
                    default => false,
                };

            })
            ->label('Enviar por email')
            ->helperText('¿Deseas enviar la factura por email?')
            ->default(false);

    }

    public static function paymnentMethodColumn(): IconColumn
    {
        return IconColumn::make('payment_method')->label('Método de pago')
            ->alignCenter()
            ->tooltip(fn ($state) => ucfirst(
                str_replace('_', ' ', $state)
            ))
            ->icon(fn ($state) => match (strtolower($state)) {
                'tarjeta' => 'heroicon-s-credit-card',
                'bizum' => 'bizum-logo',
                default => false,
            });
    }
}
