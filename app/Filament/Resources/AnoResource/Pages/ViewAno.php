<?php

namespace App\Filament\Resources\AnoResource\Pages;

use App\Filament\Resources\AnoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAno extends ViewRecord
{
    protected static string $resource = AnoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
