<?php

namespace App\Http\Controllers\Documents;

use App\Models\Sesion;
use App\Models\User;
use App\Models\AulaCurso;
use App\Models\Curso;
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

            // Procesar enfoques transversales
            $transversalidad = $detalle?->transversalidad ?? [];

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

    private function generarDocumento($sesion, $datosGenerales, $propositos, $transversalidad, $orientacion)
    {
        $plantillaFile = $orientacion === 'horizontal' ? 'plantilla_horizontal.docx' : 'plantilla_vertical.docx';
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
        $templateProcessor->setValue('TITULO_SESION', $datosGenerales['titulo'] ?? '');
        $templateProcessor->setValue('FECHA_SESION', $datosGenerales['fecha'] ?? '');
        $templateProcessor->setValue('DIA_SESION', $datosGenerales['dia'] ?? '');
        $templateProcessor->setValue('GRADO_SECCION', $datosGenerales['grado_seccion'] ?? 'No asignado');
        $templateProcessor->setValue('TIEMPO_ESTIMADO', $datosGenerales['tiempo_estimado'] ?? '');
        $templateProcessor->setValue('PROPOSITO_SESION', $datosGenerales['proposito_sesion'] ?? '');
        $templateProcessor->setValue('DOCENTE', $datosGenerales['docente'] ?? '');
        $templateProcessor->setValue('CURSO', $datosGenerales['curso'] ?? '');
        $templateProcessor->setValue('EVIDENCIAS', $datosGenerales['evidencias'] ?? '');
    }

    private function procesarPropositos($templateProcessor, $propositos)
    {
        // Procesa cada propósito y sus campos principales
        $competencias = [];
        $capacidades = [];
        $desempenos = [];
        $criterios = [];
        $instrumentos = [];

        foreach ($propositos as $prop) {
        // Competencia
        if (!empty($prop['competencia_id'])) {
            $competencia = \App\Models\Competencia::find($prop['competencia_id']);
            $competencias[] = $competencia?->nombre ?? '';
        }

        // Capacidades
        if (!empty($prop['capacidades'])) {
            $capacidadObjs = \App\Models\Capacidad::whereIn('id', $prop['capacidades'])->pluck('nombre')->toArray();
            $capacidadObjs = array_map(fn($c) => '- ' . $c, $capacidadObjs); // Agrega el guion
            $capacidades[] = implode("\n", $capacidadObjs);
        }

        // Desempeños
        if (!empty($prop['desempenos'])) {
            $desempenoObjs = \App\Models\Desempeno::whereIn('id', $prop['desempenos'])->pluck('descripcion')->toArray();
            $desempenoObjs = array_map(fn($d) => '- ' . $d, $desempenoObjs); // Agrega el guion
            $desempenos[] = implode("\n", $desempenoObjs);
        }

        // Criterios
        $criterios[] = $prop['criterios'] ?? '';

        // Instrumentos
        $inst = [];
        if (!empty($prop['instrumentos_predefinidos'])) {
            $inst = array_merge($inst, $prop['instrumentos_predefinidos']);
        }
        if (!empty($prop['instrumentos_personalizados'])) {
            $inst = array_merge($inst, $prop['instrumentos_personalizados']);
        }
        $instrumentos[] = implode(', ', $inst);
    }

        // Asignar a la plantilla (puedes usar cloneRow si lo necesitas)
        $templateProcessor->setValue('COMPETENCIAS', implode("\n", $competencias));
    $templateProcessor->setValue('CAPACIDADES', implode("\n", $capacidades));
    $templateProcessor->setValue('DESEMPEÑOS', implode("\n", $desempenos));
    $templateProcessor->setValue('CRITERIOS', implode("\n", $criterios));
    $templateProcessor->setValue('INSTRUMENTOS', implode("\n", $instrumentos));
    }

    private function procesarTransversalidad($templateProcessor, $transversalidad)
    {
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
}
