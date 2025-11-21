<?php

namespace App\Filament\Docente\Resources\AsistenciaResource\Pages;

use App\Filament\Docente\Resources\AsistenciaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAsistencia extends CreateRecord
{
    protected static string $resource = AsistenciaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asigna el ID del usuario autenticado a los datos antes de crear el registro
        $data['docente_id'] = Auth::id();
        return $data;
    }
}
