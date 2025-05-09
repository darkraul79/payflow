<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageResource\Pages\Concerns\HasPreviewModal;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Pboivin\FilamentPeek\Pages\Actions\PreviewAction;

class CreatePage extends CreateRecord
{
    use HasPreviewModal;

    protected static string $resource = PageResource::class;

    public static function getResource(): string
    {
        return config('filament-fabricator.page-resource') ?? static::$resource;

    }

    protected function handleRecordCreation(array $data): Model
    {
        $blockquotes = $data['blockquotes'] ?? null;
        $model = static::getModel()::create(collect($data)->except(['blockquotes'])->toArray());

        // Sync
        $model->blockquotes()->sync($blockquotes);

        return $model;
    }

    protected function getActions(): array
    {
        return [
            PreviewAction::make(),
        ];
    }
}
