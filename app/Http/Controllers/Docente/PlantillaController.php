<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\AulaCurso;
use App\Models\Sesion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlantillaController extends Controller
{
    public function PlantillaSesion($id)
    {
        try {
            $user = Auth::user();
            $nuevaId = null;

            DB::transaction(function () use ($id, $user, &$nuevaId) {
                $sesion = Sesion::with(['detalle', 'detalles', 'momento', 'listasCotejos'])->findOrFail($id);

                // Replicar sesión
                $nueva = $sesion->replicate();
                $nueva->titulo = $sesion->titulo . ' (Plantilla usada)';
                $nueva->public = false;
                $nueva->docente_id = $user->id;

                // Obtener aula_curso_id del docente autenticado
                $aula = $user->aulas()->first();
                $cursoId = $sesion->aulaCurso?->curso_id;
                $aulaCurso = $aula
                    ? AulaCurso::where('aula_id', $aula->id)
                        ->where('curso_id', $cursoId)
                        ->first()
                    : null;

                $nueva->aula_curso_id = $aulaCurso?->id;
                $nueva->save();

                // Duplicar relaciones
                if ($sesion->detalle) {
                    $nuevoDetalle = $sesion->detalle->replicate();
                    $nuevoDetalle->sesion_id = $nueva->id;
                    $nuevoDetalle->save();
                }

                $skipId = $sesion->detalle?->id ?? null;
                foreach ($sesion->detalles ?? [] as $detalle) {
                    if ($skipId && $detalle->id === $skipId) continue;
                    $nuevo = $detalle->replicate();
                    $nuevo->sesion_id = $nueva->id;
                    $nuevo->save();
                }

                if ($sesion->momento) {
                    $nuevoMomento = $sesion->momento->replicate();
                    $nuevoMomento->sesion_id = $nueva->id;
                    $nuevoMomento->save();
                }

                foreach ($sesion->listasCotejos ?? [] as $lista) {
                    $nuevaLista = $lista->replicate();
                    $nuevaLista->sesion_id = $nueva->id;
                    $nuevaLista->save();
                }

                $nuevaId = $nueva->id;
            });

            return response()->json([
                'success' => true,
                'redirect' => route('filament.docente.resources.sesions.edit', ['record' => $nuevaId]),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al usar plantilla', ['id' => $id, 'exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
