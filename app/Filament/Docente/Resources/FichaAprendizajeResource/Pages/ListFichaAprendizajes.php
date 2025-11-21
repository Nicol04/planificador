<?php

namespace App\Filament\Docente\Resources\FichaAprendizajeResource\Pages;

use App\Filament\Docente\Resources\FichaAprendizajeResource;
use App\Models\FichaAprendizaje;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListFichaAprendizajes extends ListRecords
{
    protected static string $resource = FichaAprendizajeResource::class;

    // Propiedades públicas para búsqueda/orden
    public $search = '';
    public $orderBy = 'created_desc';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getView(): string
    {
        return 'filament.docente.ficha_aprendizaje.list-ficha-aprendizaje-cards';
    }

    public function getFilteredFichas()
    {
        $query = FichaAprendizaje::query()
            ->where('user_id', Auth::id());

        // Filtro de búsqueda
        if ($this->search) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        // Orden
        switch ($this->orderBy) {
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'created_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'titulo_asc':
                $query->orderBy('nombre', 'asc');
                break;
            case 'titulo_desc':
                $query->orderBy('nombre', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        // Solo relaciones existentes en el modelo
        return $query->with(['user'])->simplePaginate(12);
    }

    public function deleteFicha($id)
    {
        try {
            // aceptar payload { ficha_id: ... } o id directo
            if (is_array($id) && isset($id['ficha_id'])) {
                $id = $id['ficha_id'];
            } elseif (is_object($id) && isset($id->ficha_id)) {
                $id = $id->ficha_id;
            }

            $nombreFicha = null;

            DB::transaction(function () use ($id, &$nombreFicha) {
                $ficha = FichaAprendizaje::with(['ejercicios', 'fichaSesiones'])->findOrFail($id);
                $nombreFicha = $ficha->nombre;

                // eliminar ejercicios asociados
                foreach ($ficha->ejercicios ?? [] as $ejercicio) {
                    $ejercicio->delete();
                }

                // eliminar relaciones ficha-sesión
                foreach ($ficha->fichaSesiones ?? [] as $fichaSesion) {
                    $fichaSesion->delete();
                }

                // finalmente eliminar la ficha
                $ficha->delete();
            });

            \Filament\Notifications\Notification::make()
                ->title('🗑️ Ficha eliminada exitosamente')
                ->body("\"{$nombreFicha}\" ha sido eliminada con sus elementos relacionados.")
                ->success()
                ->duration(4000)
                ->send();
        } catch (\Throwable $e) {
            Log::error('Error eliminando ficha', ['id' => $id, 'exception' => $e]);

            \Filament\Notifications\Notification::make()
                ->title('❌ Error al eliminar la ficha')
                ->body('No se pudo eliminar la ficha. ' . ($e->getMessage() ?: 'Verifica dependencias.'))
                ->danger()
                ->duration(6000)
                ->send();
        }
    }

    public function togglePublicacionFicha($id)
    {
        try {
            if (is_array($id) && isset($id['ficha_id'])) {
                $id = $id['ficha_id'];
            } elseif (is_object($id) && isset($id->ficha_id)) {
                $id = $id->ficha_id;
            }

            $ficha = FichaAprendizaje::findOrFail($id);
            $ficha->public = $ficha->public ? 0 : 1;
            $ficha->save();

            if ($ficha->public) {
                \Filament\Notifications\Notification::make()
                    ->title('✅ Ficha publicada')
                    ->body("\"{$ficha->nombre}\" estará visible para el grupo docente de la institución.")
                    ->success()
                    ->duration(4000)
                    ->send();
            } else {
                \Filament\Notifications\Notification::make()
                    ->title('🔒 Publicación retirada')
                    ->body("\"{$ficha->nombre}\" ya no estará visible para el grupo docente.")
                    ->warning()
                    ->duration(4000)
                    ->send();
            }
        } catch (\Throwable $e) {
            Log::error('Error cambiando estado de publicación', ['id' => $id, 'exception' => $e]);

            \Filament\Notifications\Notification::make()
                ->title('❌ Error al cambiar publicación')
                ->body('No se pudo actualizar el estado de publicación. ' . ($e->getMessage() ?: 'Verifica logs.'))
                ->danger()
                ->duration(6000)
                ->send();
        }
    }

    public function cambiarNombreFicha($id, $nuevoNombre)
    {
        try {
            if (is_array($id) && isset($id['ficha_id'])) {
                $id = $id['ficha_id'];
            } elseif (is_object($id) && isset($id->ficha_id)) {
                $id = $id->ficha_id;
            }

            $ficha = FichaAprendizaje::where('user_id', Auth::id())->findOrFail($id);
            $ficha->nombre = $nuevoNombre;
            $ficha->save();

            \Filament\Notifications\Notification::make()
                ->title('✏️ Nombre actualizado')
                ->body('El nombre de la ficha fue cambiado exitosamente.')
                ->success()
                ->duration(3000)
                ->send();
        } catch (\Throwable $e) {
            Log::error('Error cambiando nombre de ficha', ['id' => $id, 'exception' => $e]);
            \Filament\Notifications\Notification::make()
                ->title('❌ Error al cambiar el nombre')
                ->body('No se pudo cambiar el nombre de la ficha. ' . ($e->getMessage() ?: 'Verifica logs.'))
                ->danger()
                ->duration(6000)
                ->send();
        }
    }
}
