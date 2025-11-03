<?php

namespace App\Http\Controllers\Documents;

use App\Models\Sesion;
use App\Models\User;
use App\Models\AulaCurso;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\ListaCotejo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Models\Año;
use App\Models\usuario_aula;
use App\Models\Estudiante;

class ListasCotejoDocumentController extends DocumentController
{
    // Descargar / generar docx
    public function previsualizar($id, Request $request)
    {
        try {
            $lista = ListaCotejo::findOrFail($id);
            $orientacion = $request->get('orientacion', 'vertical');

            $competencia = $lista->competencia_id ? Competencia::find($lista->competencia_id)?->nombre : '';
            $titulo = $lista->titulo ?? '';
            $criterios = $lista->descripcion ?? '';
            $niveles = $lista->niveles ?? '';

            // Obtener los estudiantes relacionados al aula del curso de la sesión
            $sesion = Sesion::find($lista->sesion_id);
            $aulaId = $sesion?->aulaCurso?->aula_id;
            $estudiantes = $aulaId
                ? \App\Models\Estudiante::where('aula_id', $aulaId)
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->get(['nombres', 'apellidos'])
                ->map(fn($e) => [
                    'nombre' => trim(($e->apellidos ?? '') . ' ' . ($e->nombres ?? ''))
                ])->toArray()
                : [];

            $competenciaNombre = $lista->competencia_id
                ? Competencia::find($lista->competencia_id)?->nombre
                : null;

            // Si no se encontró, buscar en propositos_aprendizaje del detalle
            if (!$competenciaNombre && $lista->sesion_id) {
                $sesion = Sesion::find($lista->sesion_id);
                $propositos = $sesion?->detalle?->propositos_aprendizaje ?? [];

                foreach ($propositos as $prop) {
                    $propCompetenciaId = $prop['competencia_id'] ?? null;
                    if (!$propCompetenciaId) continue;

                    if (!empty($lista->competencia_id) && ((int)$propCompetenciaId === (int)$lista->competencia_id)) {
                        $competenciaNombre = Competencia::find($propCompetenciaId)?->nombre;
                        break;
                    }
                    if (empty($lista->competencia_id)) {
                        $competenciaNombre = Competencia::find($propCompetenciaId)?->nombre;
                        break;
                    }
                }
            }
            $datosGenerales = [
                'competencia' => $competenciaNombre ?? '',
                'titulo' => $titulo,
                'criterios' => $criterios,
                'niveles' => $niveles,
                'estudiantes' => $estudiantes,
            ];

            $rutaArchivo = $this->generarDocumento($lista, $datosGenerales, $orientacion);
            $nombreDescarga = 'ListaCotejo_' . ($titulo ? str_replace(' ', '_', $titulo) : 'lista_' . $lista->id) . '_' . date('Y-m-d') . '.docx';

            return $this->downloadResponse($rutaArchivo, $nombreDescarga);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error al generar documento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function vistaPreviaHtml($sesionId, Request $request)
    {
        $sesion = Sesion::with(['aulaCurso.aula', 'aulaCurso.curso', 'docente.persona'])->findOrFail($sesionId);
        $detalle = $sesion->detalle;

        // obtener orientación (vertical por defecto)
        $orientacion = $request->get('orientacion', 'vertical');

        // obtener listas guardadas en BD
        $listas = ListaCotejo::where('sesion_id', $sesion->id)->get();

        // si no hay listas guardadas, intentar generar desde propositos_aprendizaje
        if ($listas->isEmpty() && $detalle?->propositos_aprendizaje) {
            $generated = [];
            foreach ($detalle->propositos_aprendizaje as $prop) {
                // sólo generar si hay criterios
                $criterios = [];
                if (!empty($prop['criterios'])) {
                    $criterios = is_array($prop['criterios'])
                        ? array_values(array_filter(array_map('trim', $prop['criterios'])))
                        : array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', (string)$prop['criterios']))));
                }
                if (empty($criterios)) continue;

                $generated[] = (object)[
                    'id' => null,
                    'sesion_id' => $sesion->id,
                    'competencia_id' => $prop['competencia_id'] ?? null,
                    'titulo' => $prop['lista_cotejo_titulo'] ?? ($prop['competencia_id'] ? Competencia::find($prop['competencia_id'])?->nombre : 'Lista de cotejo'),
                    'niveles' => $prop['lista_cotejo_niveles'] ?? null,
                    'descripcion' => implode("\n", $criterios),
                    'is_generated' => true,
                ];
            }
            $listas = collect($generated);
        } else {
            // marcar listas reales
            $listas->transform(fn($l) => tap($l, fn($x) => $x->is_generated = false));
        }

        // ---------------------------
        // OBTENER ESTUDIANTES (REEMPLAZADO)
        // Priorizar estudiantes cuya aula_id == aula_id del usuario autenticado
        // Si no se encuentran, usar los fallbacks anteriores
        // ---------------------------
        $estudiantes = collect();

        try {
            // buscar año vigente y usuario_aula como en AsistenciaResource
            $año = Año::whereDate('fecha_inicio', '<=', now())
                ->whereDate('fecha_fin', '>=', now())
                ->first();

            $ua = usuario_aula::where('user_id', Auth::id())
                ->when($año, fn($q) => $q->where('año_id', $año->id))
                ->first();

            // 1) Intentar por aula del usuario autenticado
            if ($ua?->aula_id) {
                $estCollection = Estudiante::where('aula_id', $ua->aula_id)
                    ->orderBy('apellidos')
                    ->orderBy('nombres')
                    ->get();

                if ($estCollection->isNotEmpty()) {
                    $estudiantes = $estCollection->map(function ($e) {
                        return [
                            'id' => $e->id,
                            'nombres' => $e->nombres,
                            'apellidos' => $e->apellidos,
                            'nombre' => trim(($e->apellidos ?? '') . ' ' . ($e->nombres ?? '')),
                        ];
                    });
                }
            }

            // 2) Fallback: usar aula vinculada a la sesión (aulaCurso)
            if ($estudiantes->isEmpty() && $sesion->aulaCurso) {
                $aulaId = $sesion->aulaCurso->aula_id ?? $sesion->aulaCurso->aula?->id ?? null;
                if ($aulaId) {
                    $estCollection = Estudiante::where('aula_id', $aulaId)
                        ->orderBy('apellidos')
                        ->orderBy('nombres')
                        ->get();

                    if ($estCollection->isNotEmpty()) {
                        $estudiantes = $estCollection->map(function ($e) {
                            return [
                                'id' => $e->id,
                                'nombres' => $e->nombres,
                                'apellidos' => $e->apellidos,
                                'nombre' => trim(($e->apellidos ?? '') . ' ' . ($e->nombres ?? '')),
                            ];
                        });
                    }
                }
            }
        } catch (\Throwable $e) {
            // en caso de error mantener colección vacía
            $estudiantes = collect();
        }

        // si no hay estudiantes, dejar una fila vacía para la vista
        if ($estudiantes->isEmpty()) {
            $estudiantes = collect([['id' => null, 'nombre' => '']]);
        }

        // preparar arrays de criterios por lista
        $listas->transform(function ($l) {
            $criteriosText = $l->descripcion ?? '';
            $criterios = $criteriosText !== '' ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $criteriosText)), fn($v) => $v !== '')) : [];
            // niveles: intentar obtener 3 niveles separados por comas o saltos
            $nivelesText = $l->niveles ?? '';
            $niveles = $nivelesText !== '' ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $nivelesText)), fn($v) => $v !== '')) : [];
            if (count($niveles) < 3) $niveles = ['Bajo', 'Medio', 'Alto']; // default
            $l->criterios_array = $criterios;
            $l->niveles_array = array_slice($niveles, 0, 3);
            // nombre de competencia
            $l->competencia_nombre = $l->competencia_id ? Competencia::find($l->competencia_id)?->nombre : null;
            return $l;
        });

        // Retornar la vista según la orientación solicitada
        $viewName = $orientacion === 'horizontal'
            ? 'filament.docente.documentos.listas_cotejo.vista-previa-listas-cotejo-horizontal'
            : 'filament.docente.documentos.listas_cotejo.vista-previa-listas-cotejo';

        return view($viewName, [
            'sesion' => $sesion,
            'listas' => $listas,
            'estudiantes' => $estudiantes,
            'orientacion' => $orientacion, // pasar también la orientacion a la vista
        ]);
    }
    private function generarDocumento($lista_cotejo, $datosGenerales, $orientacion)
    {
        // Elegir plantilla según orientación y si transversalidad está ausente (null)
        if ($orientacion === 'horizontal') {
            $plantillaFile = 'plantilla_horizontal.docx';
        } else {
            $plantillaFile = 'plantilla_vertical.docx';
        }
        $plantilla = $this->templatesPath . 'Lista_Cotejo/' . $plantillaFile;

        if (!file_exists($plantilla)) {
            throw new \Exception('Plantilla no encontrada: ' . $plantilla);
        }
        $templateProcessor = new TemplateProcessor($plantilla);
        $this->procesarVariablesGenerales($templateProcessor, $datosGenerales);
        $this->processLogos($templateProcessor);
        $rutaTemp = $this->generateTempFile('lista_cotejo_' . $lista_cotejo->id);
        $templateProcessor->saveAs($rutaTemp);

        return $rutaTemp;
    }

    private function procesarVariablesGenerales($templateProcessor, $datosGenerales)
    {
        $competencia = $datosGenerales['competencia'] ?? '';
        $titulo = $datosGenerales['titulo'] ?? '';
        $criterios = $datosGenerales['criterios'] ?? '';
        $niveles = $datosGenerales['niveles'] ?? '';
        $estudiantes = $datosGenerales['estudiantes'] ?? [];

        // Variables básicas
        $templateProcessor->setValue('COMPETENCIA', htmlspecialchars($competencia));
        $templateProcessor->setValue('TITULO', htmlspecialchars($titulo));
        $templateProcessor->setValue('CRITERIOS', htmlspecialchars($criterios));
        $templateProcessor->setValue('NIVELES', htmlspecialchars($niveles));

        // Si hay estudiantes, clonar filas
        if (!empty($estudiantes)) {
            $templateProcessor->cloneRow('N', count($estudiantes));

            foreach ($estudiantes as $index => $est) {
                $num = $index + 1;
                $nombre = $est['nombre'] ?? trim(($est['apellidos'] ?? '') . ' ' . ($est['nombres'] ?? ''));
                $templateProcessor->setValue("N#{$num}", $num);
                $templateProcessor->setValue("NOMBRE#{$num}", htmlspecialchars($nombre));
            }
        } else {
            // Si no hay estudiantes, clona 1 fila vacía
            $templateProcessor->cloneRow('N', 1);
            $templateProcessor->setValue('N#1', '');
            $templateProcessor->setValue('NOMBRE#1', '');
        }
    }
}
