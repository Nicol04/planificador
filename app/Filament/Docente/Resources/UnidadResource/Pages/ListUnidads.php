<?php

namespace App\Filament\Docente\Resources\UnidadResource\Pages;

use App\Filament\Docente\Resources\UnidadResource;
use App\Models\Unidad;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListUnidads extends ListRecords
{
    protected static string $resource = UnidadResource::class;
    public $search = '';
    public $filterGrado = '';
    public $filterEstado = '';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva Unidad')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getView(): string
    {
        return 'filament.docente.unidad.list-unidads-cards';
    }

    public function getFilteredUnidades()
    {
        $query = Unidad::query()
            ->whereJsonContains('profesores_responsables', (string) Auth::id());

        // Filtro de búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('situacion_significativa', 'like', '%' . $this->search . '%')
                    ->orWhere('productos', 'like', '%' . $this->search . '%');
            });
        }

        // Filtro de grado
        if ($this->filterGrado) {
            $query->where('grado', $this->filterGrado);
        }

        // Filtro de estado
        if ($this->filterEstado) {
            $now = Carbon::now();
            switch ($this->filterEstado) {
                case 'activa':
                    $query->where('fecha_inicio', '<=', $now)
                        ->where('fecha_fin', '>=', $now);
                    break;
                case 'finalizada':
                    $query->where('fecha_fin', '<', $now);
                    break;
                case 'proxima':
                    $query->where('fecha_inicio', '>', $now);
                    break;
            }
        }

        return $query->orderBy('fecha_inicio', 'desc')->simplePaginate(12);
    }

    public function getGrados()
    {
        return Unidad::whereJsonContains('profesores_responsables', (string) Auth::id())
            ->distinct()
            ->pluck('grado')
            ->sort()
            ->values();
    }

    public function getEstadoTexto($unidad)
    {
        $now = Carbon::now();

        if ($unidad->fecha_inicio <= $now && $unidad->fecha_fin >= $now) {
            return '🟢 Activa';
        } elseif ($unidad->fecha_fin < $now) {
            return '🔴 Finalizada';
        } else {
            return '🟡 Próxima';
        }
    }

    public function getEstadoColor($unidad)
    {
        $now = Carbon::now();

        if ($unidad->fecha_inicio <= $now && $unidad->fecha_fin >= $now) {
            return 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100';
        } elseif ($unidad->fecha_fin < $now) {
            return 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100';
        } else {
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100';
        }
    }

    public function duplicateUnidad($id)
    {
        try {
            // Buscar la unidad original
            $unidad = Unidad::findOrFail($id);

            // Duplicar la unidad
            try {
                $nuevaUnidad = $unidad->replicate();
                $nuevaUnidad->nombre = $unidad->nombre . ' (Copia)';
                $nuevaUnidad->save();
            } catch (\Exception $e) {
                throw new \Exception('Error al duplicar la unidad: ' . $e->getMessage());
            }

            // Duplicar los detalles relacionados
            try {
                foreach ($unidad->detalles as $detalle) {
                    $nuevoDetalle = $detalle->replicate();
                    $nuevoDetalle->unidad_id = $nuevaUnidad->id;
                    $nuevoDetalle->save();
                }
            } catch (\Exception $e) {
                throw new \Exception('Error al duplicar los detalles: ' . $e->getMessage());
            }

            // Enviar notificación de éxito
            try {
                \Filament\Notifications\Notification::make()
                    ->title('📋 ¡Unidad duplicada exitosamente!')
                    ->body("Se ha creado una copia de \"" . $unidad->nombre . "\" con todos sus detalles curriculares.")
                    ->success()
                    ->duration(5000)
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('editar')
                            ->label('✏️ Editar ahora')
                            ->url(route('filament.docente.resources.unidads.edit', $nuevaUnidad))
                            ->button(),
                        \Filament\Notifications\Actions\Action::make('ver')
                            ->label('👁️ Ver unidad')
                            //->url(route('filament.docente.resources.unidads.view', $nuevaUnidad))
                            ->button()
                            ->color('gray'),
                    ])
                    ->send();
            } catch (\Exception $e) {
                throw new \Exception('Error al enviar la notificación: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            // Manejo de errores generales
            \Filament\Notifications\Notification::make()
                ->title('❌ Error al duplicar la unidad')
                ->body($e->getMessage())
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    public function deleteUnidad($id)
    {
        try {
            $unidad = Unidad::findOrFail($id);
            $nombreUnidad = $unidad->nombre;

            // Contar elementos relacionados
            $detallesCount = $unidad->detalles->count();

            $unidad->delete();

            \Filament\Notifications\Notification::make()
                ->title('🗑️ Unidad eliminada exitosamente')
                ->body("\"" . $nombreUnidad . "\" ha sido eliminada" .
                    ($detallesCount > 0 ? " junto con {$detallesCount} detalles curriculares." : "."))
                ->success()
                ->duration(4000)
                ->send();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('❌ Error al eliminar la unidad')
                ->body('No se pudo eliminar la unidad. Verifica que no tenga dependencias o inténtalo nuevamente.')
                ->danger()
                ->duration(5000)
                ->send();
        }
    }
    public function getBreadcrumbs(): array
    {
        return [];
    }
}
