<?php

namespace App\Http\Controllers\Documents;

use App\Models\Sesion;
use App\Models\Competencia;
use App\Models\ListaCotejo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
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

            // Obtener orientación: si solicitan raw=1 y no se especifica orientación, usar horizontal por defecto
            $orientacionRequest = $request->get('orientacion', null);
            $isRaw = $request->boolean('raw');
            if ($orientacionRequest === null) {
                $orientacion = $isRaw ? 'horizontal' : 'vertical';
            } else {
                $orientacion = $orientacionRequest;
            }

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
            // preparar datos generales (añadido: docente, grado, area)
            $docenteName = $sesion?->docente?->persona ? trim(($sesion->docente->persona->nombre ?? '') . ' ' . ($sesion->docente->persona->apellido ?? '')) : '';
            $gradoSeccion = $sesion?->aulaCurso?->aula?->grado_seccion ?? '';
            $area = $sesion?->aulaCurso?->curso?->curso ?? '';

            $datosGenerales = [
                'competencia' => $competenciaNombre ?? '',
                'titulo' => $titulo,
                'criterios' => $criterios,
                'niveles' => $niveles,
                'estudiantes' => $estudiantes,
                // meta para el documento
                'docente' => $docenteName,
                'grado_seccion' => $gradoSeccion,
                'area' => $area,
            ];

            // Si se solicita raw=1 generar documento programáticamente (sin plantilla)
            if ($isRaw) {
                $rutaArchivo = $this->generarDocumentoDesdeCero($lista, $datosGenerales, $orientacion);
            } else {
                $rutaArchivo = $this->generarDocumento($lista, $datosGenerales, $orientacion);
            }

            $nombreDescarga = 'ListaCotejo_' . ($titulo ? str_replace(' ', '_', $titulo) : 'lista_' . $lista->id) . '_' . date('Y-m-d') . '.docx';

            // --- NUEVO: asegurar extensión .docx y entregar con response()->download ---
            if (!file_exists($rutaArchivo)) {
                throw new \Exception('Archivo generado no encontrado: ' . $rutaArchivo);
            }

            $finalPath = $rutaArchivo;
            if (strtolower(pathinfo($finalPath, PATHINFO_EXTENSION)) !== 'docx') {
                $withExt = $finalPath . '.docx';
                // renombrar si no existe ya la versión con extensión
                if (!file_exists($withExt)) {
                    @rename($finalPath, $withExt);
                }
                $finalPath = file_exists($withExt) ? $withExt : $finalPath;
            }

            if (!file_exists($finalPath)) {
                throw new \Exception('No se pudo preparar el archivo para descarga.');
            }

            return response()->download($finalPath, $nombreDescarga, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ])->deleteFileAfterSend(true);
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
        $docente = $datosGenerales['docente'] ?? '';
        $gradoSeccion = $datosGenerales['grado_seccion'] ?? '';
        $area = $datosGenerales['area'] ?? '';

        // Normalizar niveles como array y asegurar los 3 esperados
        $nivelesArr = [];
        if (is_array($niveles)) {
            $nivelesArr = $niveles;
        } elseif (is_string($niveles) && trim($niveles) !== '') {
            $nivelesArr = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $niveles)), fn($v) => $v !== ''));
        }
        if (count($nivelesArr) < 3) {
            $nivelesArr = ['No logrado', 'En proceso', 'Destacado'];
        } else {
            $nivelesArr = array_slice($nivelesArr, 0, 3);
        }

        // asociar emojis a niveles (para plantilla)
        $emojiMap = [
            'No logrado' => '😞',
            'En proceso'  => '😐',
            'Destacado'   => '😊',
            'Logrado'     => '😊',
        ];
        $nivelesConEmoji = array_map(function($n) use ($emojiMap) {
            $label = $n ?: '';
            $e = $emojiMap[$label] ?? ($emojiMap[trim($label)] ?? '');
            return trim($label . ($e ? ' ' . $e : ''));
        }, $nivelesArr);

        // Variables básicas (mantener otras asignaciones)
        $templateProcessor->setValue('COMPETENCIA', htmlspecialchars($competencia));
        $templateProcessor->setValue('TITULO', htmlspecialchars($titulo));
        $templateProcessor->setValue('CRITERIOS', htmlspecialchars($criterios));
        // pasar niveles con emojis (por ejemplo: "No logrado 😞, En proceso 😐, Destacado 😊")
        $templateProcessor->setValue('NIVELES', htmlspecialchars(implode(', ', $nivelesConEmoji)));
        // meta adicionales para que la plantilla muestre lo mismo que la vista previa
        $templateProcessor->setValue('DOCENTE', htmlspecialchars($docente));
        $templateProcessor->setValue('GRADO_SECCION', htmlspecialchars($gradoSeccion));
        $templateProcessor->setValue('AREA', htmlspecialchars($area));

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

    /**
     * Genera un documento .docx programáticamente (PhpWord) sin usar plantilla.
     */
    private function generarDocumentoDesdeCero($lista_cotejo, $datosGenerales, $orientacion = 'vertical')
    {
        $competencia = $datosGenerales['competencia'] ?? '';
        $titulo = $datosGenerales['titulo'] ?? '';
        $criteriosText = $datosGenerales['criterios'] ?? '';
        $nivelesText = $datosGenerales['niveles'] ?? '';
        $estudiantes = $datosGenerales['estudiantes'] ?? [];
        $docente = $datosGenerales['docente'] ?? '';
        $gradoSeccion = $datosGenerales['grado_seccion'] ?? '';
        $area = $datosGenerales['area'] ?? '';

        // parsear criterios y niveles
        $criterios = $criteriosText !== '' ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $criteriosText)), fn($v)=>$v !== '')) : [];
        // niveles por defecto solicitados por el usuario (asegurar 'Destacado' en vez de 'Logrado')
        $niveles = $nivelesText !== '' ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $nivelesText)), fn($v)=>$v !== '')) : [];
        if (count($niveles) < 3) $niveles = ['No logrado','En proceso','Destacado'];

        // mapa de emojis para mostrar en encabezados
        $emojiMap = [
            'No logrado' => '😞',
            'En proceso' => '😐',
            'Destacado'   => '😊',
            'Logrado'     => '😊',
        ];

        $numCriteria = max(1, count($criterios));
        $numLevels = count($niveles);
        $totalCriteriaCols = $numCriteria * $numLevels;

        // iniciar PhpWord
        $phpWord = new PhpWord();
        $sectionStyle = [];
        if ($orientacion === 'horizontal') {
            $sectionStyle = ['orientation' => 'landscape', 'marginTop' => 600, 'marginLeft'=>600, 'marginRight'=>600];
        }
        $section = $phpWord->addSection($sectionStyle);

        // estilos básicos
        $phpWord->addTitleStyle(1, ['name'=>'Merriweather','size'=>16,'bold'=>true,'color'=>'063826']);
        $phpWord->addFontStyle('meta', ['name'=>'Nunito','size'=>10,'color'=>'063826','bold'=>true]);

        // Header: título centrado (usar $titulo si existe)
        $mainTitle = $titulo ?: 'LISTA DE COTEJO';
        $section->addText($mainTitle, ['name'=>'Merriweather','size'=>16,'bold'=>true,'color'=>'063826'], ['alignment'=>'center']);
        $section->addTextBreak(1);

        // --- CABECERA SIMPLIFICADA: mostrar COMPETENCIA, y en la misma línea GRADO/SECCIÓN · DOCENTE ---
        if (!empty($competencia)) {
            $section->addText('Competencia: ' . $competencia, ['name'=>'Nunito','size'=>10,'color'=>'374151'], ['alignment'=>'left']);
        }

        $line = [];
        if (!empty($gradoSeccion)) {
            $line[] = 'Grado/Sección: ' . $gradoSeccion;
        }
        if (!empty($docente)) {
            $line[] = 'Docente: ' . $docente;
        }
        if (!empty($line)) {
            $section->addText(implode(' · ', $line), ['name'=>'Nunito','size'=>10,'color'=>'374151'], ['alignment'=>'left']);
        }
        $section->addTextBreak(1);
        // --- FIN CABECERA SIMPLIFICADA ---

        // Tabla: estilo y creación
        $tableStyleName = 'CotejoTable';
        $phpWord->addTableStyle($tableStyleName, [
            'borderSize' => 6, 'borderColor' => '093a30', 'cellMargin' => 80
        ], []);
        $table = $section->addTable($tableStyleName);

        // ---------- Encabezados solicitados ----------
        // Fila 1: N° | Apellidos y nombres | CRITERIOS (colspan = numCriteria * numLevels)
        $table->addRow();
        // N° y Nombre se fusionan verticalmente (3 filas de encabezado)
        $table->addCell(1200, ['vMerge' => 'restart'])->addText('N°', ['bold'=>true], ['alignment'=>'center']);
        $table->addCell(8000, ['vMerge' => 'restart'])->addText('Apellidos y nombres', ['bold'=>true], ['alignment'=>'center']);
        // CRITERIOS span horizontalmente
        $table->addCell(7000, ['gridSpan' => $totalCriteriaCols])->addText('CRITERIOS', ['bold'=>true], ['alignment'=>'center']);

        // Fila 2: celdas continuadas para N y Nombre + cada criterio (colspan = numLevels)
        $table->addRow();
        // continuar la fusión vertical para N° y Nombre
        $table->addCell(1200, ['vMerge' => 'continue']);
        $table->addCell(8000, ['vMerge' => 'continue']);
        // columnas: cada criterio ocupa gridSpan = numLevels
        if (count($criterios) > 0) {
            foreach ($criterios as $crit) {
                $table->addCell( ($numLevels * 1400), ['gridSpan' => $numLevels ])->addText($crit, ['bold'=>true], ['alignment'=>'center']);
            }
        } else {
            // si no hay criterios, crear una sola columna de criterio vacía con sus niveles
            $table->addCell( ($numLevels * 1400), ['gridSpan' => $numLevels ])->addText('Criterio', ['bold'=>true], ['alignment'=>'center']);
        }

        // Fila 3: celdas continuadas para N y Nombre + niveles por criterio (añadir emojis a los labels)
        $table->addRow();
        $table->addCell(1200, ['vMerge' => 'continue']);
        $table->addCell(8000, ['vMerge' => 'continue']);
        // colorear las celdas de nivel en el header para que se asemeje a la vista previa
        $levelBg = [
            'No logrado' => 'FEE2E2', // rojo claro
            'En proceso' => 'FEF3C7', // amarillo claro
            'Destacado'  => 'DCFCE7', // verde claro
        ];
        if (count($criterios) > 0) {
            foreach ($criterios as $_) {
                foreach ($niveles as $nivel) {
                    $emoji = $emojiMap[$nivel] ?? '';
                    $nivelLabel = trim($nivel . ($emoji ? ' ' . $emoji : ''));
                    $bg = $levelBg[$nivel] ?? '';
                    $cellStyle = $bg ? ['bgColor' => $bg] : [];
                    $table->addCell(1400, $cellStyle)->addText($nivelLabel, ['size'=>9,'bold'=>true], ['alignment'=>'center']);
                }
            }
        } else {
            // caso sin criterios: mostrar sólo los niveles una vez
            foreach ($niveles as $nivel) {
                $emoji = $emojiMap[$nivel] ?? '';
                $nivelLabel = trim($nivel . ($emoji ? ' ' . $emoji : ''));
                $bg = $levelBg[$nivel] ?? '';
                $cellStyle = $bg ? ['bgColor' => $bg] : [];
                $table->addCell(1400, $cellStyle)->addText($nivelLabel, ['size'=>9,'bold'=>true], ['alignment'=>'center']);
            }
        }

        // ---------- Filas de estudiantes ----------
        if (!empty($estudiantes)) {
            foreach ($estudiantes as $idx => $est) {
                $nombre = is_array($est) ? ($est['nombre'] ?? '') : ($est->nombre ?? '');
                $table->addRow();
                $table->addCell(1200)->addText((string)($idx + 1), [], ['alignment'=>'center']);
                $table->addCell(8000)->addText($nombre, [], ['alignment'=>'left']);
                // celdas vacías por cada criterio * niveles (dejar en blanco hasta que el usuario marque)
                $colsToAdd = $numCriteria * $numLevels;
                for ($c = 0; $c < $colsToAdd; $c++) {
                    $table->addCell(1400)->addText(''); // vacía por defecto
                }
            }
        } else {
            // fila vacía
            $table->addRow();
            $table->addCell(1200)->addText('1', [], ['alignment'=>'center']);
            $table->addCell(8000)->addText('', [], ['alignment'=>'left']);
            $colsToAdd = $numCriteria * $numLevels;
            for ($c = 0; $c < $colsToAdd; $c++) {
                $table->addCell(1400)->addText(''); // vacía por defecto
            }
        }

        // guardar en archivo temporal
        $rutaTemp = $this->generateTempFile('lista_cotejo_raw_' . ($lista_cotejo->id ?? uniqid()));
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($rutaTemp);

        return $rutaTemp;
    }
}
