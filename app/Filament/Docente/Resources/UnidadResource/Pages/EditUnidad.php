<?php

namespace App\Filament\Docente\Resources\UnidadResource\Pages;

use App\Filament\Docente\Resources\UnidadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUnidad extends EditRecord
{
    protected static string $resource = UnidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
