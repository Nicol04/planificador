<?php

namespace App\Filament\Docente\Resources\FichaAprendizajeResource\Pages;

use App\Filament\Docente\Resources\FichaAprendizajeResource;
use App\Models\FichaAprendizaje;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateFichaAprendizaje extends CreateRecord
{
    protected static string $resource = FichaAprendizajeResource::class;

    /**
     * Hook: después de crear la ficha
     */
    protected function afterCreate(): void
    {
        // Limpiar variables de sesión
        FichaAprendizaje::limpiarSesionEjercicios();

        // Notificación de éxito
        Notification::make()
            ->title('Ficha creada exitosamente')
            ->success()
            ->body('Los ejercicios han sido asociados correctamente.')
            ->send();
    }
}
