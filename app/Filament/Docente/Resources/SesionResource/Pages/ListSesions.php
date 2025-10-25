<?php

namespace App\Filament\Docente\Resources\SesionResource\Pages;

use App\Filament\Docente\Resources\SesionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSesions extends ListRecords
{
    protected static string $resource = SesionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
