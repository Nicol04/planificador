<?php

namespace App\Filament\Docente\Resources\AsistenciaResource\Pages;

use App\Filament\Docente\Resources\AsistenciaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreateAsistencia extends CreateRecord
{
    protected static string $resource = AsistenciaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Log para verificar que se ejecuta
        Log::info('CreateAsistencia: mutateFormDataBeforeCreate ejecutado', [
            'user_id' => Auth::id(),
            'data_keys' => array_keys($data)
        ]);

        // Asigna el ID del usuario autenticado a los datos antes de crear el registro
        $data['docente_id'] = Auth::id();
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Podrías agregar acciones adicionales aquí si es necesario
        ];
    }
}
