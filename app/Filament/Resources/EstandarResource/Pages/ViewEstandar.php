<?php

namespace App\Filament\Resources\EstandarResource\Pages;

use App\Filament\Resources\EstandarResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEstandar extends ViewRecord
{
    protected static string $resource = EstandarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
