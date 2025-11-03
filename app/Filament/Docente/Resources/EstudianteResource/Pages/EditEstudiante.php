<?php

namespace App\Filament\Docente\Resources\EstudianteResource\Pages;

use App\Filament\Docente\Resources\EstudianteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEstudiante extends EditRecord
{
    protected static string $resource = EstudianteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }
}
