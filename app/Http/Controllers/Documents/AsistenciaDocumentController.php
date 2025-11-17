<?php

namespace App\Http\Controllers\Documents;

use App\Models\Año;
use App\Models\Estudiante;
use App\Models\usuario_aula;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;

class AsistenciaDocumentController extends DocumentController
{
    // Descargar / generar docx
    public function previsualizar(Request $request)
    {
        try {
            // aceptar id opcional desde ruta o body
            $id = $request->route('id') ?? $request->input('id', null);

            $mesInput = $request->input('mes', null);
            $anioInput = $request->input('anio', null);
            $selectedDatesRaw = $request->input('selectedDates', []);
            $selectedDates = [];

            // selectedDates puede venir como array o como string JSON => normalizar a YYYY-MM-DD strings
            $selectedDates = [];
            if (is_string($selectedDatesRaw)) {
                $decoded = json_decode($selectedDatesRaw, true);
                if (is_array($decoded)) {
                    $selectedDatesRaw = $decoded;
                } else {
                    $selectedDatesRaw = [];
                }
            }
            if (is_array($selectedDatesRaw)) {
                foreach ($selectedDatesRaw as $sd) {
                    try {
                        $d = \Carbon\Carbon::parse($sd)->toDateString();
                        $selectedDates[] = $d;
                    } catch (\Throwable $e) {
                        // ignorar entrada inválida
                    }
                }
                // eliminar duplicados y reindexar
                $selectedDates = array_values(array_unique($selectedDates));
            }

            $estudiantes = [];

            // Determinar mes número y año final
            $mesNumero = null;
            $anioFinal = $anioInput ?: null;

            if ($id) {
                $asistencia = Asistencia::find($id);
                if ($asistencia) {
                    // si mes es numérico y hay año, convertir a nombre / número
                    if (!empty($asistencia->mes) && !empty($asistencia->anio) && is_numeric($asistencia->mes)) {
                        $mesNumero = (int)$asistencia->mes;
                        $anioFinal = $asistencia->anio;
                        $mesNombre = \Carbon\Carbon::createFromDate((int)$asistencia->anio, (int)$asistencia->mes, 1)->translatedFormat('F');
                    } else {
                        $mesNumero = is_numeric($mesInput) ? (int)$mesInput : null;
                        $mesNombre = $mesInput ?? now()->translatedFormat('F');
                    }

                    // Si la asistencia ya tiene dias_no_clase guardados, úsalos (normalizados)
                    if (!empty($asistencia->dias_no_clase)) {
                        $selectedDates = [];
                        $rawFromModel = $asistencia->dias_no_clase;
                        if (is_string($rawFromModel)) {
                            $decoded = json_decode($rawFromModel, true);
                            $rawFromModel = is_array($decoded) ? $decoded : [];
                        }
                        if (is_array($rawFromModel)) {
                            foreach ($rawFromModel as $sd) {
                                try {
                                    $d = \Carbon\Carbon::parse($sd)->toDateString();
                                    $selectedDates[] = $d;
                                } catch (\Throwable $e) {
                                    // ignorar formato inválido
                                }
                            }
                            $selectedDates = array_values(array_unique($selectedDates));
                        }
                    }

                    // intentar obtener estudiantes por aula del docente propietario de la asistencia
                    $ua = usuario_aula::where('user_id', $asistencia->docente_id)->first();
                    if ($ua?->aula_id) {
                        $estudiantes = Estudiante::where('aula_id', $ua->aula_id)
                            ->orderBy('apellidos')
                            ->orderBy('nombres')
                            ->get()
                            ->map(fn($e) => [
                                'id' => $e->id,
                                'nombre' => trim(($e->apellidos ?? '') . ' ' . ($e->nombres ?? '')),
                            ])->values()->toArray();
                    }
                } else {
                    $mesNumero = is_numeric($mesInput) ? (int)$mesInput : null;
                    $mesNombre = $mesInput ?? now()->translatedFormat('F');
                }
            } else {
                // sin id: aceptar lista de estudiantes enviada por el cliente (array de nombres)
                $studentsNames = $request->input('students', []);
                if (!empty($studentsNames) && is_array($studentsNames)) {
                    $estudiantes = array_map(fn($name) => ['nombre' => (string)$name], $studentsNames);
                } else {
                    // fallback: aula del docente autenticado
                    $año = Año::whereDate('fecha_inicio', '<=', now())->whereDate('fecha_fin', '>=', now())->first();
                    $ua = usuario_aula::where('user_id', Auth::id())->when($año, fn($q) => $q->where('año_id', $año->id))->first();
                    if ($ua?->aula_id) {
                        $estudiantes = Estudiante::where('aula_id', $ua->aula_id)
                            ->orderBy('apellidos')
                            ->orderBy('nombres')
                            ->get()
                            ->map(fn($e) => [
                                'id' => $e->id,
                                'nombre' => trim(($e->apellidos ?? '') . ' ' . ($e->nombres ?? '')),
                            ])->values()->toArray();
                    }
                }

                // determinar mes a mostrar (si viene numérico y anio)
                if (!empty($mesInput) && is_numeric($mesInput) && !empty($anioInput)) {
                    $mesNumero = (int)$mesInput;
                    $anioFinal = $anioInput;
                    $mesNombre = \Carbon\Carbon::createFromDate((int)$anioInput, (int)$mesInput, 1)->translatedFormat('F');
                } else {
                    $mesNombre = $mesInput ?? now()->translatedFormat('F');
                    $mesNumero = is_numeric($mesInput) ? (int)$mesInput : null;
                }
            }

            // Ordenar estudiantes por apellidos (el nombre comienza con apellidos)
            usort($estudiantes, function ($a, $b) {
                return strcasecmp($a['nombre'] ?? '', $b['nombre'] ?? '');
            });

            // Generar matrix si tenemos mes y año numéricos
            $matrix = [];
            if (!empty($mesNumero) && !empty($anioFinal)) {
                $matrix = Asistencia::generateWeeksMatrix((int)$mesNumero, (int)$anioFinal);
            }

            return response()->view('filament.docente.documentos.asistencias.vista-previa-horizontal', [
                'mes' => $mesNombre ?? ($mesInput ?? now()->translatedFormat('F')),
                'anio' => $anioFinal,
                'estudiantes' => $estudiantes,
                'orientacion' => 'horizontal',
                'matrix' => $matrix,
                'selectedDates' => $selectedDates, // ya normalizados
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en previsualizar asistencia: ' . $e->getMessage(), ['exception' => $e]);
            return response('Error al generar la previsualización: ' . $e->getMessage(), 500);
        }
    }
    public function vistaPreviaHtml(Request $request)
    {
        $mes = $request->query('mes', now()->translatedFormat('F'));

        // Determinar el aula actual del docente autenticado
        $año = Año::whereDate('fecha_inicio', '<=', now())
            ->whereDate('fecha_fin', '>=', now())
            ->first();

        $ua = usuario_aula::where('user_id', Auth::id())
            ->when($año, fn($q) => $q->where('año_id', $año->id)) 
            ->first();

        // Obtener lista de estudiantes del aula
        $estudiantes = [];
        if ($ua?->aula_id) {
            $estudiantes = Estudiante::where('aula_id', $ua->aula_id)
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->get()
                ->map(fn($e) => [
                    'id' => $e->id,
                    'nombre' => trim(($e->apellidos ?? '') . ' ' . ($e->nombres ?? '')),
                ]);
        }

        // Enviar datos a la plantilla Blade
        return view('Docs.templates.asistencias.1H', [
            'orientacion' => 'horizontal',
            'mes' => $mes,
            'estudiantes' => $estudiantes,
        ]);
    }
    private function generarDocumento($lista_cotejo, $datosGenerales, $orientacion)
    {
        
    }

    private function procesarVariablesGenerales($templateProcessor, $datosGenerales)
    {
        
    }
}
