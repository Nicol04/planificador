<?php

namespace App\Http\Controllers\Documents;

use App\Models\Sesion;
use App\Models\User;
use App\Models\AulaCurso;
use App\Models\Curso;
use App\Models\Estandar;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;

class SesionDocumentController extends DocumentController
{
    public function previsualizar($id, Request $request)
    {
        try {
            $sesion = Sesion::findOrFail($id);
            $detalle = $sesion->detalle;
            $orientacion = $request->get('orientacion', 'vertical');

            // Procesar datos generales
            $docente = User::with('persona')->find($sesion->docente_id);
            $curso = $sesion->aulaCurso?->curso;
            $aulaCurso = \App\Models\AulaCurso::with('aula')->find($sesion->aula_curso_id);
            $gradoSeccion = $aulaCurso && $aulaCurso->aula ? $aulaCurso->aula->grado_seccion : 'No asignado';
            $datosGenerales = [
                'titulo' => $sesion->titulo,
                'fecha' => $sesion->fecha ? \Carbon\Carbon::parse($sesion->fecha)->format('d/m/Y') : '',
                'dia' => $sesion->dia ?? '',
                'grado_seccion' => $gradoSeccion,
                'tiempo_estimado' => $sesion->tiempo_estimado ?? '',
                'proposito_sesion' => $sesion->proposito_sesion ?? '',
                'docente' => $docente ? trim(($docente->persona->nombre ?? '') . ' ' . ($docente->persona->apellido ?? '')) : 'No asignado',
                'curso' => $curso?->curso ?? 'No asignado',
                'evidencias' => $detalle?->evidencia ?? 'No especificado',
            ];

            // Procesar propósitos de aprendizaje
            $propositos = $detalle?->propositos_aprendizaje ?? [];

            // Procesar enfoques transversales (dejar NULL si no existe para detectar plantilla sin transversal)
            $transversalidad = $detalle?->transversalidad ?? null;

            // Generar documento
            $rutaArchivo = $this->generarDocumento($sesion, $datosGenerales, $propositos, $transversalidad, $orientacion);

            // Nombre del archivo de descarga
            $nombreDescarga = 'Sesion_' . str_replace(' ', '_', $sesion->titulo) . '_' . date('Y-m-d') . '.docx';

            return $this->downloadResponse($rutaArchivo, $nombreDescarga);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error al generar documento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function vistaPreviaHtml($id, Request $request)
    {
        $sesion = Sesion::findOrFail($id);
        $detalle = $sesion->detalle;
        $unidad = $sesion->unidad; // Si tienes relación con unidad
        $orientacion = $request->get('orientacion', 'vertical');

        // Procesar propósitos
        $propositos = [];
        foreach ($detalle?->propositos_aprendizaje ?? [] as $prop) {
            $competencia = !empty($prop['competencia_id']) ? \App\Models\Competencia::find($prop['competencia_id'])?->nombre : null;
            $capacidades = !empty($prop['capacidades']) ? \App\Models\Capacidad::whereIn('id', $prop['capacidades'])->pluck('nombre')->toArray() : [];

            // Ahora usamos ESTANDARES en vez de desempeños
            $estandares = !empty($prop['estandares'])
                ? Estandar::whereIn('id', $prop['estandares'])->pluck('descripcion')->toArray()
                : [];

            $criterios = $prop['criterios'] ?? '';
            $instrumentos = [];
            if (!empty($prop['instrumentos_predefinidos'])) $instrumentos = array_merge($instrumentos, $prop['instrumentos_predefinidos']);
            if (!empty($prop['instrumentos_personalizados'])) $instrumentos = array_merge($instrumentos, $prop['instrumentos_personalizados']);
            $evidencia = $prop['evidencia'] ?? $detalle?->evidencia ?? null; // <-- Agrega esto

            $propositos[] = [
                'competencia' => $competencia,
                'capacidades' => $capacidades,
                'estandares' => $estandares,
                'criterios' => $criterios,
                'instrumentos' => $instrumentos,
                'evidencia' => $evidencia,
            ];
        }

        // Procesar transversalidad
        $trans = $detalle?->transversalidad ?? [];
        $instrumentosMap = [
            'rubrica' => 'Rúbrica',
            'lista_cotejo' => 'Lista de cotejo',
            'prueba' => 'Prueba',
            'observacion' => 'Observación',
            'portafolio' => 'Portafolio',
            'proyecto' => 'Proyecto',
            'entrevista' => 'Entrevista',
            'otro_personalizado' => 'Personalizado',
        ];
        $instrumentosTransversales = [];
        foreach ($trans['instrumentos_transversales_ids'] ?? [] as $key) {
            if (isset($instrumentosMap[$key]) && $key !== 'otro_personalizado') {
                $instrumentosTransversales[] = $instrumentosMap[$key];
            }
        }
        if (!empty($trans['instrumentos_transversales_ids']) && in_array('otro_personalizado', $trans['instrumentos_transversales_ids'])) {
            $personalizado = $trans['instrumentos_transversales_personalizados'] ?? '';
            if ($personalizado) $instrumentosTransversales[] = $personalizado;
        }
        $enfoquesTransversales = !empty($trans['enfoque_transversal_ids']) ? \App\Models\EnfoqueTransversal::whereIn('id', $trans['enfoque_transversal_ids'])->pluck('nombre')->toArray() : [];
        $competenciasTransversales = !empty($trans['competencias_transversales_ids']) ? \App\Models\Competencia::whereIn('id', $trans['competencias_transversales_ids'])->pluck('nombre')->toArray() : [];
        $capacidadesTransversales = !empty($trans['capacidades_transversales_ids']) ? \App\Models\Capacidad::whereIn('id', $trans['capacidades_transversales_ids'])->pluck('nombre')->toArray() : [];
        $desempenosTransversales = !empty($trans['desempeno_transversal_ids']) ? \App\Models\Desempeno::whereIn('id', $trans['desempeno_transversal_ids'])->pluck('descripcion')->toArray() : [];
        $criteriosTransversales = $trans['criterios_transversales'] ?? '';

        // Renderizar vista
        return view('filament.docente.documentos.sesiones.vista-previa-sesion-vertical', [
            'sesion' => $sesion,
            'unidad' => $unidad,
            'orientacion' => $orientacion,
            'sesionInfo' => [
                'propositos' => $propositos,
            ],
            'enfoquesTransversales' => $enfoquesTransversales,
            'competenciasTransversales' => $competenciasTransversales,
            'capacidadesTransversales' => $capacidadesTransversales,
            'desempenosTransversales' => $desempenosTransversales,
            'criteriosTransversales' => $criteriosTransversales,
            'instrumentosTransversales' => $instrumentosTransversales,
        ]);
    }

    private function generarDocumento($sesion, $datosGenerales, $propositos, $transversalidad, $orientacion)
    {
        // Elegir plantilla según orientación y si transversalidad está ausente (null)
        if ($orientacion === 'horizontal') {
            $plantillaFile = 'plantilla_horizontal.docx';
        } else {
            // vertical
            if ($transversalidad === null) {
                // plantilla específica cuando no hay transversalidad
                $plantillaFile = 'plantilla_v_sin_transversal.docx';
            } else {
                $plantillaFile = 'plantilla_vertical.docx';
            }
        }
        $plantilla = $this->templatesPath . 'Sesiones/' . $plantillaFile;

        if (!file_exists($plantilla)) {
            throw new \Exception('Plantilla no encontrada: ' . $plantilla);
        }

        $templateProcessor = new TemplateProcessor($plantilla);
        $this->procesarVariablesGenerales($templateProcessor, $datosGenerales);
        $this->procesarPropositos($templateProcessor, $propositos);
        $this->procesarTransversalidad($templateProcessor, $transversalidad);
        $this->processLogos($templateProcessor);
        $rutaTemp = $this->generateTempFile('sesion_' . $sesion->id);
        $templateProcessor->saveAs($rutaTemp);

        return $rutaTemp;
    }

    private function procesarVariablesGenerales($templateProcessor, $datosGenerales)
    {
        // normalizar antes de setear para evitar "Array to string conversion"
        $templateProcessor->setValue('TITULO_SESION', $this->normalizeForTemplate($datosGenerales['titulo'] ?? ''));
        $templateProcessor->setValue('FECHA_SESION', $this->normalizeForTemplate($datosGenerales['fecha'] ?? ''));
        $templateProcessor->setValue('DIA_SESION', $this->normalizeForTemplate($datosGenerales['dia'] ?? ''));
        $templateProcessor->setValue('GRADO_SECCION', $this->normalizeForTemplate($datosGenerales['grado_seccion'] ?? 'No asignado'));
        $templateProcessor->setValue('TIEMPO_ESTIMADO', $this->normalizeForTemplate($datosGenerales['tiempo_estimado'] ?? ''));
        $templateProcessor->setValue('PROPOSITO_SESION', $this->normalizeForTemplate($datosGenerales['proposito_sesion'] ?? ''));
        $templateProcessor->setValue('DOCENTE', $this->normalizeForTemplate($datosGenerales['docente'] ?? ''));
        $templateProcessor->setValue('CURSO', $this->normalizeForTemplate($datosGenerales['curso'] ?? ''));
        $templateProcessor->setValue('EVIDENCIAS', $this->normalizeForTemplate($datosGenerales['evidencias'] ?? ''));
    }

    private function procesarPropositos($templateProcessor, $propositos)
    {
        // Procesa cada propósito y sus campos principales
        $competencias = [];
        $capacidades = [];
        $estandaresArr = [];
        $criterios = [];
        $instrumentos = [];
        $evidencias = [];

        foreach ($propositos as $prop) {
            // --- CAMBIO: resolver IDs de estándares a su 'descripcion' ---
            if (!empty($prop['estandares'])) {
                $items = is_array($prop['estandares']) ? $prop['estandares'] : [$prop['estandares']];
                // separar ids numéricos para consulta
                $ids = array_values(array_filter($items, fn($v) => is_numeric($v)));
                $descsById = [];
                if (!empty($ids)) {
                    $descsById = Estandar::whereIn('id', $ids)->pluck('descripcion', 'id')->toArray();
                }
                // construir lista final: si es id usar descripción, si es texto usar texto
                $finalEst = array_map(function($v) use ($descsById) {
                    if (is_numeric($v)) {
                        return $descsById[(int)$v] ?? (string)$v;
                    }
                    return (string)$v;
                }, $items);
                $estandaresArr[] = implode("\n", $finalEst);
            } else {
                $estandaresArr[] = '';
            }
            // --- FIN CAMBIO ---

            // Competencia
            if (!empty($prop['competencia_id'])) {
                $competencia = \App\Models\Competencia::find($prop['competencia_id']);
                $competencias[] = $competencia?->nombre ?? '';
            } else {
                // si la vista ya envía nombre en vez de id
                $competencias[] = $prop['competencia'] ?? '';
            }

            // Capacidades
            if (!empty($prop['capacidades'])) {
                $capacidadObjs = \App\Models\Capacidad::whereIn('id', $prop['capacidades'])->pluck('nombre')->toArray();
                $capacidadObjs = array_map(fn($c) => '- ' . $c, $capacidadObjs); // Agrega el guion
                $capacidades[] = implode("\n", $capacidadObjs);
            } else {
                $capacidades[] = '';
            }

            // Criterios
            $criterios[] = is_array($prop['criterios'] ?? null) ? implode("\n", $prop['criterios']) : ($prop['criterios'] ?? '');

            // Evidencias
            $evidencias[] = is_array($prop['evidencia'] ?? null) ? implode("\n", $prop['evidencia']) : ($prop['evidencia'] ?? '');

            // Instrumentos
            $inst = [];
            if (!empty($prop['instrumentos_predefinidos'])) {
                $inst = array_merge($inst, (array)$prop['instrumentos_predefinidos']);
            }
            if (!empty($prop['instrumentos_personalizados'])) {
                $inst = array_merge($inst, (array)$prop['instrumentos_personalizados']);
            }
            $instrumentos[] = implode(', ', $inst);
        }

        // Usar normalizeForTemplate al asignar (asegura string)
        $templateProcessor->setValue('ESTANDARES', $this->normalizeForTemplate(implode("\n\n", $estandaresArr)));
        $templateProcessor->setValue('COMPETENCIAS', $this->normalizeForTemplate(implode("\n\n", $competencias)));
        $templateProcessor->setValue('CAPACIDADES', $this->normalizeForTemplate(implode("\n\n", $capacidades)));
        $templateProcessor->setValue('CRITERIOS', $this->normalizeForTemplate(implode("\n\n", $criterios)));
        $templateProcessor->setValue('EVIDENCIAS', $this->normalizeForTemplate(implode("\n\n", $evidencias)));
        $templateProcessor->setValue('INSTRUMENTOS', $this->normalizeForTemplate(implode("\n\n", $instrumentos)));
    }

    private function procesarTransversalidad($templateProcessor, $transversalidad)
    {
        // Aceptar null -> normalizar a array vacío para evitar "trying to access array offset on null"
        $transversalidad = $transversalidad ?? [];

        $instrumentosMap = [
            'rubrica' => 'Rúbrica',
            'lista_cotejo' => 'Lista de cotejo',
            'prueba' => 'Prueba',
            'observacion' => 'Observación',
            'portafolio' => 'Portafolio',
            'proyecto' => 'Proyecto',
            'entrevista' => 'Entrevista',
            'otro_personalizado' => 'Personalizado',
        ];
        $instrumentos = [];
        if (!empty($transversalidad['instrumentos_transversales_ids'])) {
            foreach ($transversalidad['instrumentos_transversales_ids'] as $key) {
                if (isset($instrumentosMap[$key]) && $key !== 'otro_personalizado') {
                    $instrumentos[] = $instrumentosMap[$key];
                }
            }
        }

        // Si hay personalizado, agregar el texto
        $instrumentosPersonalizados = '';
        if (!empty($transversalidad['instrumentos_transversales_ids']) && in_array('otro_personalizado', $transversalidad['instrumentos_transversales_ids'])) {
            $instrumentosPersonalizados = $transversalidad['instrumentos_transversales_personalizados'] ?? '';
            if ($instrumentosPersonalizados) {
                $instrumentos[] = $instrumentosPersonalizados;
            }
        }

        // Enfoques transversales
        $enfoques = [];
        if (!empty($transversalidad['enfoque_transversal_ids'])) {
            $enfoques = \App\Models\EnfoqueTransversal::whereIn('id', $transversalidad['enfoque_transversal_ids'])->pluck('nombre')->toArray();
        }

        // Competencias transversales
        $competencias = [];
        if (!empty($transversalidad['competencias_transversales_ids'])) {
            $competencias = \App\Models\Competencia::whereIn('id', $transversalidad['competencias_transversales_ids'])->pluck('nombre')->toArray();
        }

        // Capacidades transversales
        $capacidades = [];
        if (!empty($transversalidad['capacidades_transversales_ids'])) {
            $capacidades = \App\Models\Capacidad::whereIn('id', $transversalidad['capacidades_transversales_ids'])->pluck('nombre')->toArray();
            $capacidades = array_map(fn($c) => '- ' . $c, $capacidades); // Agrega el guion
        }

        // Desempeños transversales
        $desempenos = [];
        if (!empty($transversalidad['desempeno_transversal_ids'])) {
            $desempenos = \App\Models\Desempeno::whereIn('id', $transversalidad['desempeno_transversal_ids'])->pluck('descripcion')->toArray();
            $desempenos = array_map(fn($d) => '- ' . $d, $desempenos); // Agrega el guion
        }

        // Criterios transversales
        $criterios = $transversalidad['criterios_transversales'] ?? '';

        $templateProcessor->setValue('ENFOQUES_TRANSVERSALES', implode(', ', $enfoques));
        $templateProcessor->setValue('COMPETENCIAS_TRANSVERSALES', implode(', ', $competencias));
        $templateProcessor->setValue('CAPACIDADES_TRANSVERSALES', implode("\n", $capacidades));
        $templateProcessor->setValue('DESEMPENOS_TRANSVERSALES', implode("\n", $desempenos));
        $templateProcessor->setValue('CRITERIOS_TRANSVERSALES', $criterios);
        $templateProcessor->setValue('INSTRUMENTOS_TRANSVERSALES', implode(', ', $instrumentos));
    }

    /**
     * Normaliza un valor para TemplateProcessor: arrays -> texto con saltos de línea,
     * objetos -> convertir a string si es posible, null -> ''.
     */
    private function normalizeForTemplate($value): string
    {
        if ($value === null) return '';
        if (is_array($value)) {
            // aplanar arrays anidados y convertir a string
            $flat = [];
            $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($value));
            foreach ($it as $v) {
                $flat[] = (string)$v;
            }
            return implode("\n", $flat);
        }
        if (is_object($value)) {
            // si implementa __toString
            if (method_exists($value, '__toString')) {
                return (string)$value;
            }
            // intentar json
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return (string)$value;
    }
}
