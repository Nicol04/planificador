<?php

namespace App\Filament\Docente\Resources\FichaAprendizajeResource\Pages;

use App\Filament\Docente\Resources\FichaAprendizajeResource;
use App\Models\FichaAprendizaje;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Session;

class CreateFichaAprendizaje extends CreateRecord
{
    protected static string $resource = FichaAprendizajeResource::class;

    public function mount(): void
    {
        parent::mount();
        // Guardar sesion_id de la URL en la sesión si existe
        $sesionId = request()->get('sesion_id');
        if ($sesionId) {
            Session::put('sesion_id', $sesionId);
        }
    }

    /**
     * Hook: después de crear la ficha
     */
    protected function afterCreate(): void
    {
        // Limpiar variables de sesión
        FichaAprendizaje::limpiarSesionEjercicios();
        Session::forget('sesion_id'); // Limpiar sesion_id después de crear

        // Notificación de éxito
        Notification::make()
            ->title('Ficha creada exitosamente')
            ->success()
            ->body('Los ejercicios han sido asociados correctamente.')
            ->send();

        
        //$this->redirect(request()->fullUrl(), navigate: true);
    }
}
