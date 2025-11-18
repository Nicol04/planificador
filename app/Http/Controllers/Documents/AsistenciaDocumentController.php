<?php

namespace App\Http\Controllers\Documents;

use App\Models\Año;
use App\Models\Estudiante;
use App\Models\usuario_aula;
use App\Models\Asistencia;
use App\Models\Aula;
use App\Models\Plantilla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
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

            // Obtener nombre del docente (prioridad: asistencia->docente, sino Auth)
            $docenteNombre = null;
            if (!empty($id) && isset($asistencia) && $asistencia && method_exists($asistencia, 'docente') && $asistencia->docente) {
                $u = $asistencia->docente;
            } else {
                $u = Auth::user();
            }

            if (!empty($u)) {
                // Preferir persona->nombre y persona->apellido(s)
                try {
                    if (method_exists($u, 'persona') && $u->persona) {
                        $pn = trim($u->persona->nombre ?? '');
                        $pa = trim($u->persona->apellido ?? $u->persona->apellidos ?? '');
                        $full = trim($pn . ' ' . $pa);
                        if ($full !== '') {
                            $docenteNombre = $full;
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore and fallback
                }

                // Fallbacks si no se obtuvo desde persona
                if (empty($docenteNombre)) {
                    if (!empty($u->nombres) || !empty($u->apellidos)) {
                        $docenteNombre = trim(($u->nombres ?? '') . ' ' . ($u->apellidos ?? ''));
                    } elseif (!empty($u->name)) {
                        $docenteNombre = $u->name;
                    } else {
                        $docenteNombre = $u->email ?? 'Docente';
                    }
                }
            }

            // --- Nuevo: resolver URL del avatar del docente para la vista ---
            $docenteAvatar = null;
            try {
                if (!empty($u)) {
                    // 1) método común de Jetstream/Filament
                    if (method_exists($u, 'profile_photo_url')) {
                        $docenteAvatar = $u->profile_photo_url;
                    }
                    // 2) si se guarda path en profile_photo_path
                    if (empty($docenteAvatar) && !empty($u->profile_photo_path)) {
                        $docenteAvatar = Storage::url($u->profile_photo_path);
                    }
                    // 3) Spatie media (getFirstMediaUrl)
                    if (empty($docenteAvatar) && method_exists($u, 'getFirstMediaUrl')) {
                        $m = $u->getFirstMediaUrl('avatar');
                        if (!empty($m)) $docenteAvatar = $m;
                    }

                    // 4) campos alternativos (incluye avatar_url y avatar_path)
                    foreach (['avatar_url','avatar_path','avatar','photo','imagen','foto','profile_photo'] as $f) {
                        if (empty($docenteAvatar) && !empty($u->{$f})) {
                            $val = $u->{$f};
                            // si ya es URL absoluta o data URI, usarla tal cual
                            if (is_string($val) && (str_starts_with($val, 'http') || str_starts_with($val, 'data:'))) {
                                $docenteAvatar = $val;
                                break;
                            }
                            // intentar resolver como archivo en Storage
                            try {
                                if (is_string($val) && Storage::exists($val)) {
                                    $docenteAvatar = Storage::url($val);
                                    break;
                                }
                                // fallback: asumir que la ruta es relativa en storage/ y construir url pública
                                if (is_string($val)) {
                                    $docenteAvatar = asset('storage/' . ltrim($val, '/'));
                                    break;
                                }
                            } catch (\Throwable $_) {
                                // usar el valor tal cual si todo falla
                                $docenteAvatar = $val;
                                break;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('No se pudo resolver avatar docente: ' . $e->getMessage());
                $docenteAvatar = null;
            }
            // --- fin avatar ---

            // Intentar obtener grado y sección desde usuario_aulas -> aula
            $gradoSeccion = null;
            try {
                $uaForAula = null;
                if (!empty($id) && isset($asistencia) && $asistencia) {
                    $uaForAula = usuario_aula::where('user_id', $asistencia->docente_id)->first();
                }
                if (empty($uaForAula)) {
                    $uaForAula = usuario_aula::where('user_id', Auth::id())->first();
                }
                if ($uaForAula && !empty($uaForAula->aula_id)) {
                    $aula = Aula::find($uaForAula->aula_id);
                    if ($aula) {
                        $grado = $aula->grado ?? '';
                        $seccion = $aula->seccion ?? '';
                        $gradoSeccion = trim($grado . ' / ' . $seccion, ' / ');
                    }
                } elseif (!empty($asistencia->nombre_aula ?? null)) {
                    // si el registro almacena un nombre de aula, usarlo como fallback
                    $gradoSeccion = $asistencia->nombre_aula;
                }
            } catch (\Throwable $e) {
                // ignore: si no existe modelo Aula no romper
                $gradoSeccion = null;
            }

            $plantillaId = $request->input('plantilla_id') ?? ($asistencia->plantilla_id ?? null);
            $plantilla = $plantillaId ? Plantilla::find($plantillaId) : null;
            $vista = $plantilla?->vista_segura ?? 'filament.docente.documentos.asistencias.vista-previa-horizontal';

            return response()->view($vista, [
                'mes' => $mesNombre ?? ($mesInput ?? now()->translatedFormat('F')),
                'anio' => $anioFinal,
                'estudiantes' => $estudiantes,
                'orientacion' => 'horizontal',
                'matrix' => $matrix,
                'selectedDates' => $selectedDates, // ya normalizados
                'docenteNombre' => $docenteNombre,
                'docenteAvatar' => $docenteAvatar,
                'gradoSeccion' => $gradoSeccion,
                'plantilla' => $plantilla, // opcional, si la vista necesita info
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en previsualizar asistencia: ' . $e->getMessage(), ['exception' => $e]);
            return response('Error al generar la previsualización: ' . $e->getMessage(), 500);
        }
    }
    public function descargarDocx(Request $request)
    {
        // Normalizar entradas (se puede pasar id o mes/anio/selectedDates)
        $id = $request->route('id') ?? $request->input('id', null);
        $mesInput = $request->input('mes', null);
        $anioInput = $request->input('anio', null);
        $selectedDatesRaw = $request->input('selectedDates', []);
        $selectedDates = [];

        // Normalizar selectedDates a YYYY-MM-DD
        if (is_string($selectedDatesRaw)) {
            $decoded = json_decode($selectedDatesRaw, true);
            if (is_array($decoded)) $selectedDatesRaw = $decoded;
            else $selectedDatesRaw = [];
        }
        if (is_array($selectedDatesRaw)) {
            foreach ($selectedDatesRaw as $sd) {
                try { $selectedDates[] = \Carbon\Carbon::parse($sd)->toDateString(); } catch (\Throwable $e) {}
            }
            $selectedDates = array_values(array_unique($selectedDates));
        }

        // --- Obtener estudiantes, mes, año y matrix (igual que antes) ---
        $estudiantes = [];
        $mesNumero = null;
        $anioFinal = $anioInput ?: null;
        $mesNombre = $mesInput ?? now()->translatedFormat('F');

        $asistencia = null;
        if ($id) {
            $asistencia = Asistencia::find($id);
            if ($asistencia) {
                if (!empty($asistencia->mes) && is_numeric($asistencia->mes) && !empty($asistencia->anio)) {
                    $mesNumero = (int)$asistencia->mes;
                    $anioFinal = $asistencia->anio;
                    $mesNombre = \Carbon\Carbon::createFromDate((int)$asistencia->anio, (int)$asistencia->mes, 1)->translatedFormat('F');
                } else {
                    $mesNumero = is_numeric($mesInput) ? (int)$mesInput : null;
                    $mesNombre = $mesInput ?? $mesNombre;
                }

                // si la asistencia tiene dias_no_clase guardados, usarlos
                if (!empty($asistencia->dias_no_clase)) {
                    $selectedDates = [];
                    $raw = $asistencia->dias_no_clase;
                    if (is_string($raw)) {
                        $decoded = json_decode($raw, true);
                        if (is_array($decoded)) $raw = $decoded;
                        else $raw = [];
                    }
                    if (is_array($raw)) {
                        foreach ($raw as $sd) {
                            try { $selectedDates[] = \Carbon\Carbon::parse($sd)->toDateString(); } catch (\Throwable $e) {}
                        }
                        $selectedDates = array_values(array_unique($selectedDates));
                    }
                }

                // obtener estudiantes por aula del docente propietario
                $ua = \App\Models\usuario_aula::where('user_id', $asistencia->docente_id)->first();
                if ($ua?->aula_id) {
                    $estudiantes = \App\Models\Estudiante::where('aula_id', $ua->aula_id)
                        ->orderBy('apellidos')->orderBy('nombres')->get()
                        ->map(fn($e) => ['id' => $e->id, 'nombre' => trim(($e->apellidos ?? '') . ' ' . ($e->nombres ?? ''))])
                        ->values()->toArray();
                }
            }
        } else {
            // sin id: intentar recibir lista de students o usar aula del docente autenticado (como en previsualizar)
            $studentsNames = $request->input('students', []);
            if (!empty($studentsNames) && is_array($studentsNames)) {
                $estudiantes = array_map(fn($name) => ['nombre' => (string)$name], $studentsNames);
            } else {
                $año = \App\Models\Año::whereDate('fecha_inicio','<=',now())->whereDate('fecha_fin','>=',now())->first();
                $ua = \App\Models\usuario_aula::where('user_id', \Illuminate\Support\Facades\Auth::id())->when($año, fn($q)=> $q->where('año_id',$año->id))->first();
                if ($ua?->aula_id) {
                    $estudiantes = \App\Models\Estudiante::where('aula_id', $ua->aula_id)
                        ->orderBy('apellidos')->orderBy('nombres')->get()
                        ->map(fn($e)=>['id'=>$e->id,'nombre'=>trim(($e->apellidos??'').' '.($e->nombres??''))])->values()->toArray();
                }
            }

            if (!empty($mesInput) && is_numeric($mesInput) && !empty($anioInput)) {
                $mesNumero = (int)$mesInput;
                $anioFinal = $anioInput;
                $mesNombre = \Carbon\Carbon::createFromDate((int)$anioInput, (int)$mesInput, 1)->translatedFormat('F');
            } else {
                $mesNombre = $mesInput ?? $mesNombre;
                $mesNumero = is_numeric($mesInput) ? (int)$mesInput : null;
            }
        }

        // ordenar estudiantes por apellido-nombre
        usort($estudiantes, function($a,$b){ return strcasecmp($a['nombre'] ?? '', $b['nombre'] ?? ''); });

        // generar matriz si hay mes y año
        $matrix = [];
        if (!empty($mesNumero) && !empty($anioFinal)) {
            $matrix = Asistencia::generateWeeksMatrix((int)$mesNumero, (int)$anioFinal);
        }

        // Preparar estructura de semanas y días visibles (como en la vista)
        $validDaysPerWeek = [];
        if (!empty($matrix)) {
            foreach ($matrix as $wIndex => $week) {
                $valid = [];
                foreach (['L','Ma','Mi','J','V'] as $k) {
                    if (!empty($week[$k]['date'])) {
                        $valid[$k] = $week[$k];
                    }
                }
                $validDaysPerWeek[$wIndex] = $valid;
            }
        } else {
            // fallback: 4 semanas con 5 días
            for ($w = 0; $w < 4; $w++) {
                $validDaysPerWeek[$w] = [
                    'L' => ['date' => null],
                    'Ma' => ['date' => null],
                    'Mi' => ['date' => null],
                    'J' => ['date' => null],
                    'V' => ['date' => null],
                ];
            }
        }

        // ----------------------------
        // Calcular docenteNombre y gradoSeccion (igual que en previsualizar)
        // ----------------------------
        $docenteNombre = null;
        if (!empty($id) && isset($asistencia) && $asistencia && method_exists($asistencia, 'docente') && $asistencia->docente) {
            $u = $asistencia->docente;
        } else {
            $u = Auth::user();
        }
        if (!empty($u)) {
            try {
                if (method_exists($u, 'persona') && $u->persona) {
                    $pn = trim($u->persona->nombre ?? '');
                    $pa = trim($u->persona->apellido ?? $u->persona->apellidos ?? '');
                    $full = trim($pn . ' ' . $pa);
                    if ($full !== '') {
                        $docenteNombre = $full;
                    }
                }
            } catch (\Throwable $e) {
                // fallback posteriormente
            }
            if (empty($docenteNombre)) {
                if (!empty($u->nombres) || !empty($u->apellidos)) {
                    $docenteNombre = trim(($u->nombres ?? '') . ' ' . ($u->apellidos ?? ''));
                } elseif (!empty($u->name)) {
                    $docenteNombre = $u->name;
                } else {
                    $docenteNombre = $u->email ?? 'Docente';
                }
            }
        }

        $gradoSeccion = null;
        try {
            $uaForAula = null;
            if (!empty($id) && isset($asistencia) && $asistencia) {
                $uaForAula = usuario_aula::where('user_id', $asistencia->docente_id)->first();
            }
            if (empty($uaForAula)) {
                $uaForAula = usuario_aula::where('user_id', Auth::id())->first();
            }
            if ($uaForAula && !empty($uaForAula->aula_id)) {
                $aula = Aula::find($uaForAula->aula_id);
                if ($aula) {
                    $grado = $aula->grado ?? '';
                    $seccion = $aula->seccion ?? '';
                    $gradoSeccion = trim($grado . ' / ' . $seccion, ' / ');
                }
            } elseif (!empty($asistencia->nombre_aula ?? null)) {
                // si el registro almacena un nombre de aula, usarlo como fallback
                $gradoSeccion = $asistencia->nombre_aula;
            }
        } catch (\Throwable $e) {
            $gradoSeccion = null;
        }

        // Determinar plantilla si se pidió o si la asistencia la tiene
        $plantillaId = $request->input('plantilla_id') ?? ($asistencia->plantilla_id ?? null);
        $plantilla = $plantillaId ? \App\Models\Plantilla::find($plantillaId) : null;

        // Delegar la generación a función separada (devuelve ruta temporal)
        try {
            $rutaArchivo = $this->generarDocumento(
                $mesNombre,
                $anioFinal,
                $estudiantes,
                $validDaysPerWeek,
                $selectedDates,
                $docenteNombre,
                $gradoSeccion,
                $plantilla
            );

            $downloadName = 'lista_asistencia_' . str_replace(' ', '_', strtolower($mesNombre)) . '_' . ($anioFinal ?? date('Y')) . '.docx';
            return $this->downloadResponse($rutaArchivo, $downloadName);
        } catch (\Throwable $e) {
            Log::error('Error generando DOCX: ' . $e->getMessage(), ['exception' => $e]);
            return response('Error al generar el documento: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Genera el documento .docx con PhpWord (sin usar plantilla).
     * Devuelve la ruta al archivo temporal generado.
     */
    private function generarDocumento($mesNombre, $anioFinal, array $estudiantes, array $validDaysPerWeek, array $selectedDates, $docenteNombre, $gradoSeccion, $plantilla = null)
    {
        // Normalizar selectedDates para comparación rápida
        $selectedLookup = [];
        foreach ($selectedDates as $sd) {
            if (is_string($sd) && trim($sd) !== '') $selectedLookup[trim($sd)] = true;
        }

        // Si no hay semanas, usar fallback (4 semanas x 5 días)
        if (empty($validDaysPerWeek) || !is_array($validDaysPerWeek)) {
            $validDaysPerWeek = [];
            for ($w = 0; $w < 4; $w++) {
                $validDaysPerWeek[$w] = [
                    'L'  => ['date' => null],
                    'Ma' => ['date' => null],
                    'Mi' => ['date' => null],
                    'J'  => ['date' => null],
                    'V'  => ['date' => null],
                ];
            }
        }

        $phpWord = new PhpWord();
        // landscape y márgenes razonables
        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'marginLeft' => 600,
            'marginRight' => 600,
            'marginTop' => 600,
            'marginBottom' => 600,
        ]);

        // Título
        $section->addText('LISTA DE ASISTENCIA', ['bold' => true, 'size' => 14]);
        $section->addText(ucfirst($mesNombre) . ' ' . ($anioFinal ?? ''), ['size' => 10], ['spaceAfter' => 200]);

        // Estilo tabla
        $tableStyleName = 'AsistTable';
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => 'DDDDDD',
            'cellMargin' => 80
        ];
        $phpWord->addTableStyle($tableStyleName, $tableStyle);
        $table = $section->addTable($tableStyleName);

        // --- Cabecera fila 1: N° | Apellidos y nombres | Semana 1 (colspan) ... | Observaciones
        $table->addRow();
        $table->addCell(1200, ['vMerge' => 'restart'])->addText('N°', ['bold' => true], ['alignment' => 'center']);
        $table->addCell(8000, ['vMerge' => 'restart'])->addText('APELLIDOS Y NOMBRES', ['bold' => true]);
        foreach ($validDaysPerWeek as $wIndex => $valid) {
            $colspan = is_array($valid) ? count($valid) : 0;
            if ($colspan > 0) {
                // gridSpan para unir varias columnas bajo "Semana X"
                $table->addCell(1000 * $colspan, ['gridSpan' => $colspan])->addText('Semana ' . ($wIndex + 1), ['bold' => true], ['alignment' => 'center']);
            }
        }
        $table->addCell(3000, ['vMerge' => 'restart'])->addText('Observaciones', ['bold' => true]);

        // --- Cabecera fila 2: etiquetas de día (L, Ma, Mi...)
        $table->addRow();
        $table->addCell(1200, ['vMerge' => 'continue']);
        $table->addCell(8000, ['vMerge' => 'continue']);
        foreach ($validDaysPerWeek as $valid) {
            if (is_array($valid)) {
                foreach (array_keys($valid) as $dKey) {
                    $table->addCell(1000)->addText($dKey, ['bold' => true], ['alignment' => 'center']);
                }
            }
        }
        $table->addCell(3000, ['vMerge' => 'continue']);

        // --- Cabecera fila 3: números del día (01,02,...)
        $table->addRow();
        $table->addCell(1200, ['vMerge' => 'continue']);
        $table->addCell(8000, ['vMerge' => 'continue']);
        foreach ($validDaysPerWeek as $valid) {
            if (is_array($valid)) {
                foreach ($valid as $info) {
                    $dayNum = (!empty($info['date'])) ? \Carbon\Carbon::parse($info['date'])->format('d') : '';
                    $table->addCell(1000)->addText($dayNum, [], ['alignment' => 'center']);
                }
            }
        }
        $table->addCell(3000, ['vMerge' => 'continue']);

        // --- Filas de estudiantes
        if (empty($estudiantes)) {
            // dejar una fila vacía para impresión manual
            $table->addRow();
            $table->addCell(1200)->addText('');
            $table->addCell(8000)->addText('');
            foreach ($validDaysPerWeek as $valid) {
                if (is_array($valid)) {
                    foreach ($valid as $info) {
                        $table->addCell(1000)->addText('');
                    }
                }
            }
            $table->addCell(3000)->addText('');
        } else {
            foreach ($estudiantes as $idx => $est) {
                $table->addRow();
                // N°
                $table->addCell(1200)->addText((string)($idx + 1), [], ['alignment' => 'center']);
                // Nombre
                $table->addCell(8000)->addText($est['nombre'] ?? '');
                // Celdas por día
                foreach ($validDaysPerWeek as $valid) {
                    if (is_array($valid)) {
                        foreach ($valid as $info) {
                            $date = $info['date'] ?? null;
                            $isNoClass = $date && isset($selectedLookup[$date]);
                            $cellStyle = $isNoClass ? ['bgColor' => 'E6F0FF'] : [];
                            $cell = $table->addCell(1000, $cellStyle);
                            // Puedes cambiar 'X' por otro marcador o dejar vacío
                            $cell->addText($isNoClass ? 'X' : '', [], ['alignment' => 'center']);
                        }
                    }
                }
                // Observaciones (vacía)
                $table->addCell(3000)->addText('');
            }
        }

        // Guardar en archivo temporal y devolver ruta
        $rutaTemp = $this->generateTempFile('asistencia');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($rutaTemp);
        return $rutaTemp;
    }

    /**
     * Procesa variables generales en TemplateProcessor y clona filas de estudiantes si la plantilla contiene marcadores.
     * Se buscan marcadores comunes: est_nombre, estudiante_nombre, nombre, NOMBRE, etc.
     */
    private function procesarVariablesGenerales(TemplateProcessor $tp, array $datos)
    {
        // Reemplazos simples
        $tp->setValue('MES', $datos['MES'] ?? '');
        $tp->setValue('ANIO', $datos['ANIO'] ?? '');
        $tp->setValue('DOCENTE', $datos['DOCENTE'] ?? '');
        $tp->setValue('AULA', $datos['AULA'] ?? '');

        $estudiantes = $datos['ESTUDIANTES'] ?? [];
        // preparar listas
        $studentsText = [];
        $numbers = [];
        foreach ($estudiantes as $i => $est) {
            $name = '';
            if (is_array($est)) {
                // Priorizar claves comunes
                $name = $est['nombre'] ?? ($est['nombres'] ?? trim(($est['apellidos'] ?? '') . ' ' . ($est['nombres'] ?? '')));
            } elseif (is_object($est)) {
                $name = $est->nombre ?? trim(($est->apellidos ?? '') . ' ' . ($est->nombres ?? ''));
            } else {
                $name = (string) $est;
            }
            $studentsText[] = $name;
            $numbers[] = (string)($i + 1);
        }

        // intentar detectar marcador clonable en la plantilla (más robusto)
        $vars = [];
        try {
            $vars = method_exists($tp, 'getVariables') ? $tp->getVariables() : [];
        } catch (\Throwable $e) {
            $vars = [];
        }

        // Normalizar variables a un array asociativo: lowercase => original
        $varMap = [];
        foreach ($vars as $v) {
            $varMap[strtolower($v)] = $v;
        }

        // Buscar marcador para el nombre de estudiante (busca cualquier variable que contenga 'nombre' o 'est_')
        $cloneKey = null;
        foreach ($varMap as $low => $orig) {
            if (strpos($low, 'nombre') !== false || strpos($low, 'est_nombre') !== false || strpos($low, 'estudiante') !== false) {
                $cloneKey = $orig;
                break;
            }
        }
        // Si no se detectó, intentar detectar NOMBRE explícito
        if (!$cloneKey && isset($varMap['nombre'])) {
            $cloneKey = $varMap['nombre'];
        } elseif (!$cloneKey && isset($varMap['nombre#'])) {
            $cloneKey = $varMap['nombre#']; // raro, pero por seguridad
        }

        // Buscar marcador para numeración (n, num, numero, nro)
        $numKey = null;
        foreach ($varMap as $low => $orig) {
            if ($low === 'n' || strpos($low, 'num') !== false || strpos($low, 'numero') !== false || strpos($low, 'nro') !== false) {
                $numKey = $orig;
                break;
            }
        }

        // Si se detectó cloneKey, intentar cloneRow con ese marcador
        if ($cloneKey) {
            $count = max(1, count($studentsText));
            try {
                $tp->cloneRow($cloneKey, $count);
                for ($i = 0; $i < $count; $i++) {
                    $idx = $i + 1;
                    $tp->setValue("{$cloneKey}#{$idx}", $studentsText[$i] ?? '');
                    if ($numKey) {
                        $tp->setValue("{$numKey}#{$idx}", (string)$idx);
                    } else {
                        // intentar también con claves comunes 'N' o 'NOMBRE' si existen
                        if (isset($varMap['n'])) {
                            $tp->setValue("{$varMap['n']}#{$idx}", (string)$idx);
                        } elseif (isset($varMap['nro'])) {
                            $tp->setValue("{$varMap['nro']}#{$idx}", (string)$idx);
                        }
                    }
                }
                // también rellenar ESTUDIANTES/N por compatibilidad
                $tp->setValue('ESTUDIANTES', implode("\n", $studentsText));
                $tp->setValue('N', implode("\n", $numbers));
                return;
            } catch (\Throwable $e) {
                // Si cloneRow falla por cualquier razón, caer al fallback multilinea abajo
                Log::warning('cloneRow falló: ' . $e->getMessage());
            }
        }

        // Intento alternativo: si existe marcador 'N' para numeración y 'NOMBRE' para nombre (plantillas clásicas)
        if (isset($varMap['n']) && (isset($varMap['nombre']) || isset($varMap['nombre']))) {
            $rowKey = isset($varMap['nombre']) ? $varMap['nombre'] : (isset($varMap['nombre']) ? $varMap['nombre'] : null);
            $count = max(1, count($studentsText));
            try {
                // clonar usando 'N' si la fila tiene ${N}
                $tp->cloneRow($varMap['n'], $count);
                for ($i = 0; $i < $count; $i++) {
                    $idx = $i + 1;
                    $tp->setValue("{$varMap['n']}#{$idx}", (string)$idx);
                    if ($rowKey) {
                        $tp->setValue("{$rowKey}#{$idx}", $studentsText[$i] ?? '');
                    }
                }
                $tp->setValue('ESTUDIANTES', implode("\n", $studentsText));
                $tp->setValue('N', implode("\n", $numbers));
                return;
            } catch (\Throwable $e) {
                Log::warning('cloneRow alternativo falló: ' . $e->getMessage());
            }
        }

        // Fallback: reemplazo multilinea en una sola celda (si no se detectó row clonable)
        $tp->setValue('ESTUDIANTES', implode("\n", $studentsText));
        $tp->setValue('N', implode("\n", $numbers));
    }
}