<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Pboivin\FilamentPeek\Pages\Actions\PreviewAction;
use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;
use Z3d0X\FilamentFabricator\Models\Contracts\Page as PageContract;

class EditPage extends EditRecord
{
    use Concerns\HasPreviewModal;

    protected static string $resource = PageResource::class;

    public static function getResource(): string
    {
        return config('filament-fabricator.page-resource') ?? static::$resource;
    }

    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $blockquotes = $data['blockquotes'] ?? null;
        $record->update(collect($data)->except(['blockquotes'])->toArray());

        // Sync
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        Page::find($record->id)->blockquotes()->sync($blockquotes);

        return $record;
    }

    protected function getActions(): array
    {
        return [
            PreviewAction::make(),

            ViewAction::make()
                ->visible(config('filament-fabricator.enable-view-page')),

            DeleteAction::make(),

            Action::make('visit')
                ->label('Visit Page')
                ->label('Visitar')
                ->url(function () {
                    /** @var PageContract $page */
                    $page = $this->getRecord();

                    return FilamentFabricator::getPageUrlFromId($page->id);
                })
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->openUrlInNewTab()
                ->color('success')
                ->visible(config('filament-fabricator.routing.enabled')),

            Action::make('Guardar')
                ->action('save')
                ->label('Guardar'),
        ];
    }
}
