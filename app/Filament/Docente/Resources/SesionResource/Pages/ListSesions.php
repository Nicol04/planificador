<?php

namespace App\Filament\Docente\Resources\SesionResource\Pages;

use App\Filament\Docente\Resources\SesionResource;
use App\Models\Sesion;
use App\Models\AulaCurso;
use App\Models\Aula;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                ->label('Crear una nueva sesión')
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
    public function deleteSesion($id)
    {
        try {
            // aceptar payload { sesion_id: ... } o id directo
            if (is_array($id) && isset($id['sesion_id'])) {
                $id = $id['sesion_id'];
            } elseif (is_object($id) && isset($id->sesion_id)) {
                $id = $id->sesion_id;
            }

            $tituloSesion = null;

            DB::transaction(function () use ($id, &$tituloSesion) {
                // cargar la relación singular 'momento' en lugar de 'momentos'
                $sesion = Sesion::with(['detalle', 'detalles', 'momento', 'listasCotejos'])->findOrFail($id);
                $tituloSesion = $sesion->titulo;

                // eliminar listas de cotejo
                foreach ($sesion->listasCotejos ?? [] as $lista) {
                    $lista->delete();
                }

                // eliminar momento (hasOne)
                if ($sesion->momento) {
                    $sesion->momento->delete();
                }

                // eliminar detalles hasMany
                foreach ($sesion->detalles ?? [] as $detalle) {
                    $detalle->delete();
                }

                // eliminar detalle hasOne (si existe)
                if ($sesion->detalle) {
                    $sesion->detalle->delete();
                }

                // finalmente eliminar la sesión
                $sesion->delete();
            });

            \Filament\Notifications\Notification::make()
                ->title('🗑️ Sesión eliminada exitosamente')
                ->body("\"{$tituloSesion}\" ha sido eliminada con sus elementos relacionados.")
                ->success()
                ->duration(4000)
                ->send();
        } catch (\Throwable $e) {
            Log::error('Error eliminando sesión', ['id' => $id, 'exception' => $e]);

            \Filament\Notifications\Notification::make()
                ->title('❌ Error al eliminar la sesión')
                ->body('No se pudo eliminar la sesión. ' . ($e->getMessage() ?: 'Verifica dependencias.'))
                ->danger()
                ->duration(6000)
                ->send();
        }
    }

    public function duplicateSesion($id)
    {
        try {
            // aceptar payload { sesion_id: ... } o id directo
            if (is_array($id) && isset($id['sesion_id'])) {
                $id = $id['sesion_id'];
            } elseif (is_object($id) && isset($id->sesion_id)) {
                $id = $id->sesion_id;
            }

            $nuevaId = null;

            DB::transaction(function () use ($id, &$nuevaId) {
                // cargar la relación singular 'momento' en lugar de 'momentos'
                $sesion = Sesion::with(['detalle', 'detalles', 'momento', 'listasCotejos'])->findOrFail($id);

                $nueva = $sesion->replicate();
                $nueva->titulo = $sesion->titulo . ' (Copia)';
                // opcional: marcar no pública
                $nueva->public = false;
                $nueva->save();

                // duplicar detalle hasOne
                if ($sesion->detalle) {
                    $nuevoDetalle = $sesion->detalle->replicate();
                    $nuevoDetalle->sesion_id = $nueva->id;
                    $nuevoDetalle->save();
                }

                // duplicar detalles hasMany (evitar duplicar el hasOne si coincide)
                $skipId = $sesion->detalle?->id ?? null;
                foreach ($sesion->detalles ?? [] as $detalle) {
                    if ($skipId && $detalle->id === $skipId) {
                        continue;
                    }
                    $nuevo = $detalle->replicate();
                    $nuevo->sesion_id = $nueva->id;
                    $nuevo->save();
                }

                // duplicar momento (hasOne)
                if ($sesion->momento) {
                    $nuevoMomento = $sesion->momento->replicate();
                    $nuevoMomento->sesion_id = $nueva->id;
                    $nuevoMomento->save();
                }

                // duplicar listas de cotejo
                foreach ($sesion->listasCotejos ?? [] as $lista) {
                    $nuevaLista = $lista->replicate();
                    $nuevaLista->sesion_id = $nueva->id;
                    $nuevaLista->save();
                }

                $nuevaId = $nueva->id;
            });

            \Filament\Notifications\Notification::make()
                ->title('📋 ¡Sesión duplicada exitosamente!')
                ->body('Se ha creado una copia de la sesión.')
                ->success()
                ->duration(5000)
                ->actions([
                    \Filament\Notifications\Actions\Action::make('editar')
                        ->label('✏️ Editar ahora')
                        ->url(fn() => route('filament.docente.resources.sesions.edit', ['record' => $nuevaId]))
                        ->button(),
                ])
                ->send();
        } catch (\Throwable $e) {
            Log::error('Error duplicando sesión', ['id' => $id, 'exception' => $e]);

            \Filament\Notifications\Notification::make()
                ->title('❌ Error al duplicar la sesión')
                ->body('No se pudo duplicar la sesión. ' . ($e->getMessage() ?: 'Verifica logs.'))
                ->danger()
                ->duration(6000)
                ->send();
        }
    }
    public function publishSesion($id)
    {
        try {
            // aceptar payload { sesion_id: ... } o id directo
            if (is_array($id) && isset($id['sesion_id'])) {
                $id = $id['sesion_id'];
            } elseif (is_object($id) && isset($id->sesion_id)) {
                $id = $id->sesion_id;
            }

            $tituloSesion = null;

            DB::transaction(function () use ($id, &$tituloSesion) {
                $sesion = Sesion::findOrFail($id);
                $tituloSesion = $sesion->titulo;

                // cambiar estado a publicado (1)
                $sesion->public = 1;
                $sesion->save();
            });

            \Filament\Notifications\Notification::make()
                ->title('✅ Sesión publicada')
                ->body("\"{$tituloSesion}\" estará visible para el grupo docente de la institución.")
                ->success()
                ->duration(4000)
                ->send();
        } catch (\Throwable $e) {
            Log::error('Error publicando sesión', ['id' => $id, 'exception' => $e]);

            \Filament\Notifications\Notification::make()
                ->title('❌ Error al publicar la sesión')
                ->body('No se pudo publicar la sesión. ' . ($e->getMessage() ?: 'Verifica logs.'))
                ->danger()
                ->duration(6000)
                ->send();
        }
    }
    public function togglePublicacion($id)
    {
        try {
            // aceptar payload { sesion_id: ... } o id directo
            if (is_array($id) && isset($id['sesion_id'])) {
                $id = $id['sesion_id'];
            } elseif (is_object($id) && isset($id->sesion_id)) {
                $id = $id->sesion_id;
            }

            $sesion = Sesion::findOrFail($id);
            $sesion->public = $sesion->public ? 0 : 1;
            $sesion->save();

            if ($sesion->public) {
                \Filament\Notifications\Notification::make()
                    ->title('✅ Sesión publicada')
                    ->body("\"{$sesion->titulo}\" estará visible para el grupo docente de la institución.")
                    ->success()
                    ->duration(4000)
                    ->send();
            } else {
                \Filament\Notifications\Notification::make()
                    ->title('🔒 Publicación retirada')
                    ->body("\"{$sesion->titulo}\" ya no estará visible para el grupo docente.")
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
}
