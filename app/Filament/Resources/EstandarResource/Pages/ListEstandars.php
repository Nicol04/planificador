<?php

namespace App\Filament\Resources\EstandarResource\Pages;

use App\Filament\Resources\EstandarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEstandars extends ListRecords
{
    protected static string $resource = EstandarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
