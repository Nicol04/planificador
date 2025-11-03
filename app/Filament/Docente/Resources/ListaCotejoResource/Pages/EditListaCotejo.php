<?php

namespace App\Filament\Docente\Resources\ListaCotejoResource\Pages;

use App\Filament\Docente\Resources\ListaCotejoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditListaCotejo extends EditRecord
{
    protected static string $resource = ListaCotejoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
