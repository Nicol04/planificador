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
            Actions\Action::make('preview')
                ->label('Vista Previa / Imprimir')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn(): string => route('fichas.preview', ['fichaId' => $this->record->id]))
                ->openUrlInNewTab(),
            
        ];
    }

    /**
     * Hook: después de actualizar la ficha
     */
    protected function afterSave(): void
    {
        // Limpiar variables de sesión
        //FichaAprendizaje::limpiarSesionEjercicios();

        // Notificación de éxito
        Notification::make()
            ->title('Ficha actualizada exitosamente')
            ->success()
            ->body('Los ejercicios han sido actualizados correctamente.')
            ->send();

        $currentUrl = route('filament.docente.resources.ficha-aprendizajes.edit', ['record' => $this->record->id]);
        $this->redirect($currentUrl, navigate: false);


        // Refrescar la página después de guardar
        //$this->redirect(request()->fullUrl(), navigate: true);
    }
}
