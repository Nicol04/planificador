<?php

namespace App\Filament\Docente\Resources\SesionResource\Pages;

use App\Filament\Docente\Resources\SesionResource;
use App\Models\Sesion;
use App\Models\AulaCurso;
use App\Models\Aula;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSesions extends ListRecords
{
    protected static string $resource = SesionResource::class;

    public $search = '';
    public $filterFechaDesde = '';
    public $filterFechaHasta = '';
    public $filterCurso = '';
    public $orderBy = 'fecha_desc';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva Sesión')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getView(): string
    {
        return 'filament.docente.sesion.list-sesions-cards';
    }

    // Si $todos es true, muestra todos los cursos del aula del docente
    public function getCursos($todos = false)
    {
        $user = Auth::user();
        if (!$user) return [];

        // Obtener todas las aulas del docente
        $aulaIds = $user->usuario_aulas()->pluck('aula_id')->toArray();

        // Obtener todos los AulaCurso de esas aulas
        $aulaCursoQuery = AulaCurso::whereIn('aula_id', $aulaIds)->with('curso');
        if (!$todos) {
            // Solo los usados en sesiones
            $aulaCursoIds = Sesion::where('docente_id', Auth::id())
                ->pluck('aula_curso_id')
                ->unique()
                ->filter()
                ->toArray();
            $aulaCursoQuery->whereIn('id', $aulaCursoIds);
        }
        return $aulaCursoQuery->get()
            ->mapWithKeys(function ($aulaCurso) {
                return [$aulaCurso->id => $aulaCurso->curso?->curso ?? 'Curso'];
            })
            ->toArray();
    }

    public function getFilteredSesiones()
    {
        $query = Sesion::query()
            ->where('docente_id', Auth::id());

        // Filtro de búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('titulo', 'like', '%' . $this->search . '%')
                    ->orWhere('tema', 'like', '%' . $this->search . '%');
            });
        }

        // Filtro por fecha desde/hasta
        if ($this->filterFechaDesde) {
            $query->whereDate('fecha', '>=', $this->filterFechaDesde);
        }
        if ($this->filterFechaHasta) {
            $query->whereDate('fecha', '<=', $this->filterFechaHasta);
        }

        // Filtro por curso (aula_curso_id)
        if ($this->filterCurso) {
            $query->where('aula_curso_id', $this->filterCurso);
        }

        // Orden
        switch ($this->orderBy) {
            case 'fecha_asc':
                $query->orderBy('fecha', 'asc');
                break;
            case 'fecha_desc':
                $query->orderBy('fecha', 'desc');
                break;
            case 'titulo_asc':
                $query->orderBy('titulo', 'asc');
                break;
            case 'titulo_desc':
                $query->orderBy('titulo', 'desc');
                break;
            default:
                $query->orderBy('fecha', 'desc');
        }

        // Cargar relación curso a través de aulaCurso
        return $query->with(['aulaCurso.curso'])->simplePaginate(12);
    }
}
