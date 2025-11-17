<?php

namespace App\Filament\Docente\Resources\AsistenciaResource\Pages;

use App\Filament\Docente\Resources\AsistenciaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Plantilla;
use Illuminate\Support\Facades\Auth;
use App\Models\Asistencia; // <-- agregado

class ListAsistencias extends ListRecords
{
    protected static string $resource = AsistenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getView(): string
    {
        return 'filament.docente.asistencia.list-plantillas-cards';
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
            'misAsistencias' => $misAsistencias, // <-- agregado
        ]);
    }
}
