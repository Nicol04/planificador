<?php

namespace App\Filament\Resources\AnoResource\Pages;

use App\Filament\Resources\AnoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnos extends ListRecords
{
    protected static string $resource = AnoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
