<?php

namespace App\Filament\Docente\Resources\AsistenciaResource\Pages;

use App\Filament\Docente\Resources\AsistenciaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Plantilla;
use Illuminate\Support\Facades\Auth;
use App\Models\Asistencia; // <-- agregado
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListAsistencias extends ListRecords
{
    protected static string $resource = AsistenciaResource::class;

    public function getView(): string
    {
        return 'filament.docente.asistencia.list-plantillas-cards';
    }
    public function getBreadcrumbs(): array
    {
        return [];
    }
    public function getHeading(): string 
{
    return '';
}

    protected function getViewData(): array
    {
        $userId = Auth::id();

        $plantillas = Plantilla::whereRaw('LOWER(tipo) = ?', ['asistencia'])
            ->where(function ($q) use ($userId) {
                $q->where('public', true);
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
            })
            ->get();

        // Obtener las asistencias creadas por el docente autenticado
        $misAsistencias = [];
        if ($userId) {
            $misAsistencias = Asistencia::where('docente_id', $userId)
                ->with('plantilla')
                ->orderByDesc('id')
                ->get();
        }

        return array_merge(parent::getViewData(), [
            'plantillas' => $plantillas,
            'misAsistencias' => $misAsistencias,
        ]);
    }
    public function deleteAsistencias($id)
    {
        try {
            if (is_array($id) && isset($id['asistencia_id'])) {
                $id = $id['asistencia_id'];
            } elseif (is_object($id) && isset($id->asistencia_id)) {
                $id = $id->asistencia_id;
            }

            $titulo = null;

            DB::transaction(function () use ($id, &$titulo) {
                $asistencia = \App\Models\Asistencia::with('plantilla')->findOrFail($id);
                $titulo = "Asistencia de " . ($asistencia->nombre_aula ?? 'Aula');
                $asistencia->delete();
            });

            \Filament\Notifications\Notification::make()
                ->title('🗑️ Asistencia eliminada')
                ->body("{$titulo} fue eliminada correctamente.")
                ->success()
                ->duration(3500)
                ->send();
        } catch (\Throwable $e) {
            Log::error("Error eliminando asistencia {$id}", ['exception' => $e]);

            \Filament\Notifications\Notification::make()
                ->title('❌ Error al eliminar asistencia')
                ->body("No se pudo eliminar. " . $e->getMessage())
                ->danger()
                ->duration(6000)
                ->send();
        }
    }
}
