<?php

namespace App\Filament\Docente\Resources\FichaAprendizajeResource\Pages;

use App\Filament\Docente\Resources\FichaAprendizajeResource;
use App\Models\FichaAprendizaje;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditFichaAprendizaje extends EditRecord
{
    protected static string $resource = FichaAprendizajeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }

    /**
     * Hook: después de actualizar la ficha
     */
    protected function afterSave(): void
    {
        // Limpiar variables de sesión
        FichaAprendizaje::limpiarSesionEjercicios();

        // Notificación de éxito
        Notification::make()
            ->title('Ficha actualizada exitosamente')
            ->success()
            ->body('Los ejercicios han sido actualizados correctamente.')
            ->send();
    }
}
