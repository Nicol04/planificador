<?php

namespace App\Http\Controllers\Documents;

use App\Models\Unidad;
use App\Models\User;
use App\Models\EnfoqueTransversal;
use App\Models\Curso;
use App\Models\Competencia;
use App\Models\Capacidad;
use App\Models\Desempeno;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;

class UnidadDocumentController extends DocumentController
{
    public function previsualizar($id, Request $request)
    {
        try {
            $unidad = Unidad::findOrFail($id);
            $detalle = $unidad->detalles->first();
            $orientacion = $request->get('orientacion', 'vertical');

            // Procesar datos
            $profesores = $this->getProfesoresInfo($unidad->profesores_responsables);
            $cursosInfo = $this->procesarContenidoCurricular($detalle);
            $enfoquesInfo = $this->procesarEnfoquesTransversales($detalle);

            // NUEVO: Procesar cronograma
            $cronograma = [];
            if ($detalle && $detalle->cronograma) {
                $cronograma = is_string($detalle->cronograma)
                    ? json_decode($detalle->cronograma, true)
                    : $detalle->cronograma;
            }

            // NUEVO: Si es descarga (Word), permite generar desde cero si raw=1
            if ($request->has('descargar')) {
                $isRaw = $request->boolean('raw');
                if ($isRaw) {
                    $rutaArchivo = $this->generarDocumentoDesdeCero($unidad, $detalle, $profesores, $cursosInfo, $enfoquesInfo, $cronograma, $orientacion);
                } else {
                    $rutaArchivo = $this->generarDocumento($unidad, $detalle, $profesores, $cursosInfo, $enfoquesInfo, $orientacion);
                }
                $nombreDescarga = 'Unidad_' . str_replace(' ', '_', $unidad->nombre) . '_' . date('Y-m-d') . '.docx';
                return $this->downloadResponse($rutaArchivo, $nombreDescarga);
            }

            // Si es vista previa, pasa el cronograma a la vista
            $datosVista = [
                'unidad' => $unidad,
                'detalle' => $detalle,
                'profesores' => $profesores,
                'cursosInfo' => $cursosInfo,
                'enfoquesInfo' => $enfoquesInfo,
                'orientacion' => $orientacion,
                'materialesBasicos' => $detalle ? $detalle->materiales_basicos : 'No especificado',
                'recursos' => $detalle ? $detalle->recursos : 'No especificado',
                'cronograma' => $cronograma,
            ];

            // CORREGIDO: vista correcta
            $vista = $orientacion === 'horizontal'
                ? 'filament.docente.documentos.unidad.vista-previa-unidad-horizontal'
                : 'filament.docente.documentos.unidad.vista-previa-unidad';

            return view($vista, $datosVista);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error al generar documento: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generarDocumento($unidad, $detalle, $profesores, $cursosInfo, $enfoquesInfo, $orientacion)
    {
        // Seleccionar plantilla
        $plantillaFile = $orientacion === 'horizontal' ? 'plantilla_horizontal.docx' : 'plantilla_vertical.docx';
        $plantilla = $this->templatesPath . 'Unidades/' . $plantillaFile;

        if (!file_exists($plantilla)) {
            throw new \Exception('Plantilla no encontrada: ' . $plantilla);
        }

        $templateProcessor = new TemplateProcessor($plantilla);

        // Procesar variables básicas
        $this->procesarVariablesBasicas($templateProcessor, $unidad, $profesores);

        // Procesar contenido específico
        $this->procesarEnfoques($templateProcessor, $enfoquesInfo);
        $this->procesarContenidoCurricularTemplate($templateProcessor, $cursosInfo);
        $this->procesarMateriales($templateProcessor, $detalle);

        // Procesar logos
        $this->processLogos($templateProcessor);

        // Generar archivo temporal
        $rutaTemp = $this->generateTempFile('unidad_' . $unidad->id);
        $templateProcessor->saveAs($rutaTemp);

        return $rutaTemp;
    }

    private function procesarVariablesBasicas($templateProcessor, $unidad, $profesores)
    {
        $templateProcessor->setValue('NOMBRE_UNIDAD', $unidad->nombre);
        $templateProcessor->setValue(
            'GRADO_SECCIONES',
            $unidad->grado . '° grado - Secciones: ' . implode(', ', $unidad->secciones ?? [])
        );
        $templateProcessor->setValue('FECHA_INICIO', $unidad->fecha_inicio->format('d/m/Y'));
        $templateProcessor->setValue('FECHA_FIN', $unidad->fecha_fin->format('d/m/Y'));

        // Profesores
        $profesoresTexto = '';
        if ($profesores && $profesores->count() > 0) {
            foreach ($profesores as $profesor) {
                $profesoresTexto .= '• ' . $profesor['nombre_completo'] . "\n";
            }
        } else {
            $profesoresTexto = '• No asignado';
        }
        $templateProcessor->setValue('PROFESORES', trim($profesoresTexto));

        $templateProcessor->setValue(
            'SITUACION_SIGNIFICATIVA',
            $unidad->situacion_significativa ?? 'No especificada'
        );
    }

    private function procesarEnfoquesTransversales($detalle)
    {
        if (!$detalle || !$detalle->enfoques) { // ⚠️ Cambiar de 'enfoques_transversales' a 'enfoques'
            return [];
        }

        // Decodificar JSON si es string
        $enfoques = is_string($detalle->enfoques) // ⚠️ Cambiar aquí también
            ? json_decode($detalle->enfoques, true)
            : $detalle->enfoques;

        if (!is_array($enfoques)) {
            return [];
        }

        $enfoquesInfo = [];

        // Procesar cada enfoque del JSON - ⚠️ Cambiar la lógica para coincidir con UnidadController
        foreach ($enfoques as $key => $enfoqueItem) {
            if (!isset($enfoqueItem['enfoque_id'])) {
                continue;
            }

            // Buscar el enfoque en la base de datos
            $enfoque = EnfoqueTransversal::find($enfoqueItem['enfoque_id']);
            if (!$enfoque) continue;

            $valores = [];

            // Procesar los valores del enfoque
            if (isset($enfoqueItem['valores']) && is_array($enfoqueItem['valores'])) {
                foreach ($enfoqueItem['valores'] as $valorKey => $valorData) {
                    if (is_array($valorData) && isset($valorData['valor']) && isset($valorData['actitud'])) {
                        $valores[] = [
                            'valor' => $valorData['valor'],
                            'actitud' => $valorData['actitud']
                        ];
                    }
                }
            }

            $enfoquesInfo[] = [
                'enfoque' => $enfoque,
                'valores' => $valores
            ];
        }

        return $enfoquesInfo;
    }

    private function procesarEnfoques($templateProcessor, $enfoquesInfo)
    {
        if (count($enfoquesInfo) > 0) {
            // Crear arrays para cada columna
            $enfoques = [];
            $valores = [];
            $actitudes = [];

            foreach ($enfoquesInfo as $enfoqueInfo) {
                $nombreEnfoque = $enfoqueInfo['enfoque']->nombre ?? 'Sin nombre';
                $primeraFila = true;

                if (count($enfoqueInfo['valores']) > 0) {
                    foreach ($enfoqueInfo['valores'] as $valor) {
                        $enfoques[] = $primeraFila ? $nombreEnfoque : '';
                        $valores[] = $valor['valor'];
                        $actitudes[] = '● ' . $valor['actitud'];
                        $primeraFila = false;
                    }
                } else {
                    $enfoques[] = $nombreEnfoque;
                    $valores[] = 'No especificado';
                    $actitudes[] = '● No especificado';
                }
            }

            // Reemplazar valores en la plantilla
            $templateProcessor->setValue('ENFOQUES_TRANSVERSALES', implode("\n", $enfoques));
            $templateProcessor->setValue('VALORES_ENFOQUES', implode("\n", $valores));
            $templateProcessor->setValue('ACTITUDES_ENFOQUES', implode("\n", $actitudes));
        } else {
            $templateProcessor->setValue('ENFOQUES_TRANSVERSALES', 'No se han definido enfoques transversales');
            $templateProcessor->setValue('VALORES_ENFOQUES', '');
            $templateProcessor->setValue('ACTITUDES_ENFOQUES', '');
        }
    }

    private function procesarContenidoCurricular($detalle)
{
    if (!$detalle || !$detalle->contenido) {
        return [];
    }

    $contenido = is_string($detalle->contenido)
        ? json_decode($detalle->contenido, true)
        : $detalle->contenido;

    if (!is_array($contenido)) {
        return [];
    }

    $cursosInfo = [];

    // 🆕 PROCESAR NUEVO FORMATO CON UUIDs
    foreach ($contenido as $uuid => $item) {
        // Verificar que sea un curso
        if (!isset($item['type']) || $item['type'] !== 'curso') {
            continue;
        }

        // Verificar que tenga datos del curso
        if (!isset($item['data']['curso_id'])) {
            continue;
        }

        $curso = Curso::find($item['data']['curso_id']);
        if (!$curso) continue;

        $competenciasInfo = [];

        // Procesar competencias (ahora están bajo 'competencias' con UUIDs)
        if (isset($item['data']['competencias']) && is_array($item['data']['competencias'])) {
            foreach ($item['data']['competencias'] as $competenciaUuid => $competenciaData) {
                if (!isset($competenciaData['competencia_id'])) continue;

                $competencia = Competencia::find($competenciaData['competencia_id']);
                if (!$competencia) continue;

                // Obtener capacidades por IDs
                $capacidades = collect();
                if (isset($competenciaData['capacidades']) && is_array($competenciaData['capacidades'])) {
                    $capacidadesIds = array_filter($competenciaData['capacidades'], function ($id) {
                        return !empty($id) && is_numeric($id);
                    });

                    if (!empty($capacidadesIds)) {
                        $capacidades = Capacidad::whereIn('id', $capacidadesIds)->get();
                    }
                }

                // Obtener desempeños por IDs
                $desempenos = collect();
                if (isset($competenciaData['desempenos']) && is_array($competenciaData['desempenos'])) {
                    $desempenosIds = array_filter($competenciaData['desempenos'], function ($id) {
                        return !empty($id) && is_numeric($id);
                    });

                    if (!empty($desempenosIds)) {
                        $desempenos = Desempeno::whereIn('id', $desempenosIds)->get();
                    }
                }

                // 🆕 PROCESAR INSTRUMENTOS - COMBINAR PREDEFINIDOS Y PERSONALIZADOS
                $instrumentos = [];
                
                // Instrumentos predefinidos
                if (isset($competenciaData['instrumentos_predefinidos']) && is_array($competenciaData['instrumentos_predefinidos'])) {
                    $instrumentos = array_merge($instrumentos, $competenciaData['instrumentos_predefinidos']);
                }
                
                // Instrumentos personalizados
                if (isset($competenciaData['instrumentos_personalizados']) && is_array($competenciaData['instrumentos_personalizados'])) {
                    $instrumentos = array_merge($instrumentos, $competenciaData['instrumentos_personalizados']);
                }
                
                // Filtrar instrumentos vacíos
                $instrumentos = array_filter($instrumentos, function ($instrumento) {
                    return !empty($instrumento);
                });

                $competenciasInfo[] = [
                    'competencia' => $competencia,
                    'capacidades' => $capacidades,
                    'desempenos' => $desempenos,
                    'criterios' => $competenciaData['criterios'] ?? 'No especificado',
                    'evidencias' => $competenciaData['evidencias'] ?? 'No especificado',
                    'instrumentos' => $instrumentos
                ];
            }
        }

        $cursosInfo[] = [
            'curso' => $curso,
            'competencias' => $competenciasInfo
        ];
    }

    // 📋 FALLBACK: Mantener compatibilidad con formatos anteriores
    if (empty($cursosInfo)) {
        $cursosInfo = $this->procesarFormatosAnteriores($contenido);
    }

    return $cursosInfo;
}
private function procesarFormatosAnteriores($contenido)
{
    $cursosInfo = [];

    // Formato anterior con estructura 'cursos'
    if (isset($contenido['cursos']) && is_array($contenido['cursos'])) {
        foreach ($contenido['cursos'] as $cursoData) {
            if (!isset($cursoData['curso_id'])) continue;

            $curso = Curso::find($cursoData['curso_id']);
            if (!$curso) continue;

            $competenciasInfo = [];

            if (isset($cursoData['competencias']) && is_array($cursoData['competencias'])) {
                foreach ($cursoData['competencias'] as $compData) {
                    $competencia = Competencia::find($compData['competencia_id']);
                    if (!$competencia) continue;

                    // Obtener capacidades por IDs
                    $capacidades = collect();
                    if (isset($compData['capacidades']) && is_array($compData['capacidades'])) {
                        $capacidadesIds = array_filter($compData['capacidades'], function ($id) {
                            return !empty($id) && is_numeric($id);
                        });

                        if (!empty($capacidadesIds)) {
                            $capacidades = Capacidad::whereIn('id', $capacidadesIds)->get();
                        }
                    }

                    // Obtener desempeños por IDs
                    $desempenos = collect();
                    if (isset($compData['desempenos']) && is_array($compData['desempenos'])) {
                        $desempenosIds = array_filter($compData['desempenos'], function ($id) {
                            return !empty($id) && is_numeric($id);
                        });

                        if (!empty($desempenosIds)) {
                            $desempenos = Desempeno::whereIn('id', $desempenosIds)->get();
                        }
                    }

                    // Procesar instrumentos
                    $instrumentos = [];
                    if (isset($compData['instrumentos']) && is_array($compData['instrumentos'])) {
                        $instrumentos = array_filter($compData['instrumentos'], function ($instrumento) {
                            return !empty($instrumento);
                        });
                    }

                    $competenciasInfo[] = [
                        'competencia' => $competencia,
                        'capacidades' => $capacidades,
                        'desempenos' => $desempenos,
                        'criterios' => $compData['criterios'] ?? 'No especificado',
                        'evidencias' => $compData['evidencias'] ?? 'No especificado',
                        'instrumentos' => $instrumentos
                    ];
                }
            }

            $cursosInfo[] = [
                'curso' => $curso,
                'competencias' => $competenciasInfo
            ];
        }
    }
    // Formato más antiguo con IDs como claves
    else {
        foreach ($contenido as $cursoId => $cursoData) {
            $curso = Curso::find($cursoId);
            if (!$curso) continue;

            $competenciasInfo = [];

            if (isset($cursoData['competencias']) && is_array($cursoData['competencias'])) {
                foreach ($cursoData['competencias'] as $competenciaId => $competenciaData) {
                    $competencia = Competencia::find($competenciaId);
                    if (!$competencia) continue;

                    // Obtener capacidades
                    $capacidadesIds = $competenciaData['capacidades'] ?? [];
                    $capacidades = collect();
                    if (is_array($capacidadesIds) && count($capacidadesIds) > 0) {
                        $capacidades = Capacidad::whereIn('id', $capacidadesIds)->get();
                    }

                    // Obtener desempeños
                    $desempenosIds = $competenciaData['desempenos'] ?? [];
                    $desempenos = collect();
                    if (is_array($desempenosIds) && count($desempenosIds) > 0) {
                        $desempenos = Desempeno::whereIn('id', $desempenosIds)->get();
                    }

                    $competenciasInfo[] = [
                        'competencia' => $competencia,
                        'capacidades' => $capacidades,
                        'desempenos' => $desempenos,
                        'criterios' => $competenciaData['criterios'] ?? null,
                        'evidencias' => $competenciaData['evidencias'] ?? null,
                        'instrumentos' => $competenciaData['instrumentos'] ?? []
                    ];
                }
            }

            $cursosInfo[] = [
                'curso' => $curso,
                'competencias' => $competenciasInfo
            ];
        }
    }

    return $cursosInfo;
}
    private function procesarContenidoCurricularTemplate($templateProcessor, $cursosInfo)
    {
        if (count($cursosInfo) > 0) {
            // ✅ ARRAYS para acumular TODAS las filas (una por competencia)
            $areas = [];
            $competenciasTexto = [];
            $desempenosTexto = [];
            $criteriosTexto = [];
            $evidenciasTexto = [];
            $instrumentosTexto = [];

            foreach ($cursosInfo as $cursoInfo) {
                $nombreCurso = $cursoInfo['curso']->curso;
                $competencias = $cursoInfo['competencias'];

                if (count($competencias) > 0) {
                    // ✅ CREAR UNA FILA POR CADA COMPETENCIA
                    foreach ($competencias as $index => $competenciaInfo) {
                        
                        // ✅ ÁREA: Solo mostrar en la primera competencia del curso
                        if ($index === 0) {
                            $areas[] = $nombreCurso;
                        } else {
                            $areas[] = ''; // Celda vacía para competencias adicionales del mismo curso
                        }

                        // ✅ COMPETENCIA + CAPACIDADES en una sola celda
                        $competenciaCompleta = $competenciaInfo['competencia']->nombre;
                        if ($competenciaInfo['capacidades']->count() > 0) {
                            foreach ($competenciaInfo['capacidades'] as $capacidad) {
                                $competenciaCompleta .= "\n• " . $capacidad->nombre;
                            }
                        }
                        $competenciasTexto[] = $competenciaCompleta;

                        // ✅ DESEMPEÑOS en una sola celda
                        if ($competenciaInfo['desempenos']->count() > 0) {
                            $desempenosArray = [];
                            foreach ($competenciaInfo['desempenos'] as $desempeno) {
                                $desempenosArray[] = "• " . $desempeno->descripcion;
                            }
                            $desempenosTexto[] = implode("\n", $desempenosArray);
                        } else {
                            $desempenosTexto[] = "No especificado";
                        }

                        // ✅ CRITERIOS
                        $criteriosTexto[] = $competenciaInfo['criterios'] ?: 'No especificado';

                        // ✅ EVIDENCIAS
                        $evidenciasTexto[] = $competenciaInfo['evidencias'] ?: 'No especificado';

                        // ✅ INSTRUMENTOS
                        if (is_array($competenciaInfo['instrumentos']) && count($competenciaInfo['instrumentos']) > 0) {
                            $instrumentosTexto[] = implode("\n", $competenciaInfo['instrumentos']);
                        } else {
                            $instrumentosTexto[] = 'No especificado';
                        }
                    }
                } else {
                    // Si el curso no tiene competencias
                    $areas[] = $nombreCurso;
                    $competenciasTexto[] = 'No hay competencias definidas';
                    $desempenosTexto[] = '';
                    $criteriosTexto[] = '';
                    $evidenciasTexto[] = '';
                    $instrumentosTexto[] = '';
                }
            }

            // ✅ INTENTAR USAR CLONEROW PARA GENERAR FILAS DINÁMICAS
            try {
                if (count($areas) > 1) {
                    $templateProcessor->cloneRow('CURSO', count($areas));
                    
                    // Asignar valores a cada fila clonada
                    for ($i = 0; $i < count($areas); $i++) {
                        $index = $i + 1;
                        $templateProcessor->setValue("CURSO#${index}", $areas[$i]);
                        $templateProcessor->setValue("COMPETENCIA#${index}", $competenciasTexto[$i]);
                        $templateProcessor->setValue("DESEMPEÑOS#${index}", $desempenosTexto[$i]);
                        $templateProcessor->setValue("CRITERIOS#${index}", $criteriosTexto[$i]);
                        $templateProcessor->setValue("EVIDENCIAS#${index}", $evidenciasTexto[$i]);
                        $templateProcessor->setValue("INSTRUMENTOS#${index}", $instrumentosTexto[$i]);
                    }
                } else {
                    // Si solo hay una fila, usar método tradicional
                    throw new \Exception("Solo una fila, usar método tradicional");
                }
            } catch (\Exception $e) {
                // ✅ FALLBACK: Si cloneRow no funciona, usar el método tradicional
                // Cada "\n" representa una nueva fila en la tabla de Word
                $templateProcessor->setValue('CURSO', implode("\n", $areas));
                $templateProcessor->setValue('COMPETENCIA', implode("\n", $competenciasTexto));
                $templateProcessor->setValue('DESEMPEÑOS', implode("\n", $desempenosTexto));
                $templateProcessor->setValue('CRITERIOS', implode("\n", $criteriosTexto));
                $templateProcessor->setValue('EVIDENCIAS', implode("\n", $evidenciasTexto));
                $templateProcessor->setValue('INSTRUMENTOS', implode("\n", $instrumentosTexto));
                
                // También establecer las variables con números por si la plantilla las usa
                $templateProcessor->setValue('CAPACIDADES', implode("\n", $competenciasTexto));
            }

        } else {
            // No hay contenido curricular
            $templateProcessor->setValue('CURSO', 'No se ha definido contenido curricular');
            $templateProcessor->setValue('COMPETENCIA', '');
            $templateProcessor->setValue('DESEMPEÑOS', '');
            $templateProcessor->setValue('CRITERIOS', '');
            $templateProcessor->setValue('EVIDENCIAS', '');
            $templateProcessor->setValue('INSTRUMENTOS', '');
            $templateProcessor->setValue('CAPACIDADES', '');
        }
    }
    
    private function procesarMateriales($templateProcessor, $detalle)
    {
        $materialesBasicos = $detalle && $detalle->materiales_basicos
            ? $detalle->materiales_basicos
            : 'No especificado';

        $recursos = $detalle && $detalle->recursos
            ? $detalle->recursos
            : 'No especificado';

        $templateProcessor->setValue('MATERIALES_BASICOS', $materialesBasicos);
        $templateProcessor->setValue('RECURSOS', $recursos);
    }

    // NUEVO: Generar documento Word programáticamente (sin plantilla, mejorado)
    private function generarDocumentoDesdeCero($unidad, $detalle, $profesores, $cursosInfo, $enfoquesInfo, $cronograma, $orientacion)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $sectionStyle = [];
        if ($orientacion === 'horizontal') {
            $sectionStyle = ['orientation' => 'landscape', 'marginTop' => 600, 'marginLeft'=>600, 'marginRight'=>600];
        }
        $section = $phpWord->addSection($sectionStyle);

        // === LOGOS Y ENCABEZADO ===
        // Mejor distribución: 3 columnas, ancho total 100%
        $table = $section->addTable([
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
            'width' => 5000, // 100% (en centésimas de %)
            'unit' => 'pct', // usar string 'pct' para porcentaje
        ]);
        $table->addRow(1000);

        $logoIzq = public_path('assets/img/logo_colegio.png');
        $logoMin = public_path('assets/img/logo_ministerio.png');
        $logoDer = public_path('assets/img/ugel_logo.jpg');

        // Celda izquierda (logo colegio)
        $cellIzq = $table->addCell(2000, ['valign'=>'center']);
        if (file_exists($logoIzq)) {
            $cellIzq->addImage($logoIzq, [
                'width'=>70, 'height'=>70, 'alignment'=>'left', 'wrappingStyle'=>'inline'
            ]);
        }

        // Celda centro (logo minedu + títulos)
        $cellCenter = $table->addCell(6000, ['valign'=>'center']);
        if (file_exists($logoMin)) {
            $cellCenter->addImage($logoMin, [
                'width'=>150, 'height'=>60, 'alignment'=>'center', 'wrappingStyle'=>'inline'
            ]);
        }
        $cellCenter->addTextBreak(1);
        $cellCenter->addText('UNIDAD DE APRENDIZAJE', ['name'=>'Merriweather','size'=>16,'bold'=>true,'color'=>'0066cc'], ['alignment'=>'center']);
        $cellCenter->addText('"' . $unidad->nombre . '"', ['name'=>'Merriweather','size'=>14,'bold'=>true,'color'=>'0066cc'], ['alignment'=>'center']);
        $cellCenter->addText(date('Y'), ['name'=>'Merriweather','size'=>12,'color'=>'0066cc'], ['alignment'=>'center']);

        // Celda derecha (logo ugel)
        $cellDer = $table->addCell(2000, ['valign'=>'center']);
        if (file_exists($logoDer)) {
            $cellDer->addImage($logoDer, [
                'width'=>70, 'height'=>70, 'alignment'=>'right', 'wrappingStyle'=>'inline'
            ]);
        }

        $section->addTextBreak(1);

        // === DATOS INFORMATIVOS (NO EN TABLA) ===
        $section->addText('1. DATOS INFORMATIVOS:', ['bold'=>true,'color'=>'0066cc','size'=>12]);
        $section->addTextBreak(0.5);

        // Directivos y datos principales
        $section->addText('Institución educativa: ANN GOULDEN', ['bold'=>true,'size'=>11]);
        $section->addText('Directora: JULIANA RUIZ FALERO', ['size'=>10]);
        $section->addText('Subdirectores: FELIX HARLE SILUPU RAMÍREZ y ELIZABETH ARELLANO SIANCAS', ['size'=>10]);
        $section->addTextBreak(0.5);

        // Datos de la unidad
        $section->addText('Nombre de la unidad de aprendizaje:', ['bold'=>true,'size'=>10]);
        $section->addText($unidad->nombre, ['size'=>10]);
        $section->addText('Grado y sección:', ['bold'=>true,'size'=>10]);
        $section->addText($unidad->grado . '° grado - Secciones: ' . implode(', ', $unidad->secciones ?? []), ['size'=>10]);
        $section->addText('Temporalización:', ['bold'=>true,'size'=>10]);
        $section->addText('Inicio: ' . $unidad->fecha_inicio->format('d/m/Y') . ' | Término: ' . $unidad->fecha_fin->format('d/m/Y'), ['size'=>10]);
        $section->addText('Profesores responsables:', ['bold'=>true,'size'=>10]);
        if ($profesores && $profesores->count() > 0) {
            foreach ($profesores as $profesor) {
                $section->addText('• ' . $profesor['nombre_completo'], ['size'=>10]);
            }
        } else {
            $section->addText('• No asignado', ['size'=>10]);
        }
        $section->addTextBreak(1);

        // === SITUACIÓN SIGNIFICATIVA ===
        $section->addText('2. SITUACIÓN SIGNIFICATIVA:', ['bold'=>true,'color'=>'0066cc','size'=>12]);
        $section->addText($unidad->situacion_significativa ?? 'No especificada', ['name'=>'Nunito','size'=>10]);
        $section->addTextBreak(1);

        // === PROPÓSITOS DE APRENDIZAJE ===
        $section->addText('3. PROPÓSITOS DE APRENDIZAJE:', ['bold'=>true,'color'=>'0066cc','size'=>12]);
        // Definir anchos de columnas según orientación
        if ($orientacion === 'horizontal') {
            // Suma: 1800+3000+3000+1800+1200+1200 = 12000 (más espacio, hoja ancha)
            $colWidths = [1800, 3000, 3000, 1800, 1200, 1200];
        } else {
            // Suma: 900+1500+1500+900+700+700 = 6200 (ajustado a hoja vertical)
            $colWidths = [900, 1500, 1500, 900, 700, 700];
        }
        if (count($cursosInfo) > 0) {
            $tbl = $section->addTable([
                'borderSize'=>6,
                'borderColor'=>'0066cc',
                'cellMargin'=>80,
                'width' => 100 * 50,
                'unit' => 'pct',
            ]);
            // Cabecera
            $tbl->addRow();
            $tbl->addCell($colWidths[0])->addText('ÁREA', ['bold'=>true]);
            $tbl->addCell($colWidths[1])->addText('COMPETENCIAS/CAPACIDADES', ['bold'=>true]);
            $tbl->addCell($colWidths[2])->addText('DESEMPEÑOS', ['bold'=>true]);
            $tbl->addCell($colWidths[3])->addText('CRITERIOS DE EVALUACIÓN', ['bold'=>true]);
            $tbl->addCell($colWidths[4])->addText('EVIDENCIAS', ['bold'=>true]);
            $tbl->addCell($colWidths[5])->addText('INSTRUMENTO DE EVALUACIÓN', ['bold'=>true]);
            // Cuerpo
            foreach ($cursosInfo as $cursoInfo) {
                $competencias = $cursoInfo['competencias'];
                $totalCompetencias = count($competencias);
                if ($totalCompetencias > 0) {
                    foreach ($competencias as $compIndex => $competenciaInfo) {
                        $tbl->addRow();
                        if ($compIndex === 0) {
                            $tbl->addCell($colWidths[0], ['vMerge'=>'restart','valign'=>'center'])->addText($cursoInfo['curso']->curso, ['bold'=>true]);
                        } else {
                            $tbl->addCell($colWidths[0], ['vMerge'=>'continue']);
                        }
                        $compText = $competenciaInfo['competencia']->nombre;
                        if ($competenciaInfo['capacidades']->count() > 0) {
                            foreach ($competenciaInfo['capacidades'] as $capacidad) {
                                $compText .= "\n• " . $capacidad->nombre;
                            }
                        }
                        $tbl->addCell($colWidths[1])->addText($compText, ['size'=>9]);
                        $desempenosText = '';
                        if ($competenciaInfo['desempenos']->count() > 0) {
                            foreach ($competenciaInfo['desempenos'] as $desempeno) {
                                $desempenosText .= "• " . $desempeno->descripcion . "\n";
                            }
                        } else {
                            $desempenosText = "No especificado";
                        }
                        $tbl->addCell($colWidths[2])->addText(trim($desempenosText), ['size'=>9]);
                        $tbl->addCell($colWidths[3])->addText($competenciaInfo['criterios'] ?: 'No especificado', ['size'=>9]);
                        $tbl->addCell($colWidths[4])->addText($competenciaInfo['evidencias'] ?: 'No especificado', ['size'=>9]);
                        $instText = (is_array($competenciaInfo['instrumentos']) && count($competenciaInfo['instrumentos']) > 0)
                            ? implode("\n", $competenciaInfo['instrumentos'])
                            : 'No especificado';
                        $tbl->addCell($colWidths[5])->addText($instText, ['size'=>9]);
                    }
                } else {
                    $tbl->addRow();
                    $tbl->addCell($colWidths[0])->addText($cursoInfo['curso']->curso, ['bold'=>true]);
                    $tbl->addCell(array_sum(array_slice($colWidths,1)), ['gridSpan'=>5])->addText('No hay competencias definidas');
                }
            }
        } else {
            $section->addText('No se ha definido contenido curricular para esta unidad.', ['italic'=>true]);
        }
        $section->addTextBreak(1);

        // === ENFOQUES: VALORES Y ACTITUDES ===
        $section->addText('4. ENFOQUES: VALORES Y ACTITUDES', ['bold'=>true,'color'=>'0066cc','size'=>12]);
        // Definir anchos de columnas según orientación
        if ($orientacion === 'horizontal') {
            $colWidthsEnfoques = [3500, 2500, 4000];
        } else {
            $colWidthsEnfoques = [2000, 1500, 2500];
        }
        if (count($enfoquesInfo) > 0) {
            $tbl = $section->addTable([
                'borderSize'=>6,
                'borderColor'=>'0066cc',
                'cellMargin'=>80,
                'width' => 100 * 50,
                'unit' => 'pct',
            ]);
            $tbl->addRow();
            $tbl->addCell($colWidthsEnfoques[0])->addText('ENFOQUES TRANSVERSALES', ['bold'=>true]);
            $tbl->addCell($colWidthsEnfoques[1])->addText('VALORES', ['bold'=>true]);
            $tbl->addCell($colWidthsEnfoques[2])->addText('ACTITUDES', ['bold'=>true]);
            foreach ($enfoquesInfo as $enfoqueInfo) {
                if (count($enfoqueInfo['valores']) > 0) {
                    foreach ($enfoqueInfo['valores'] as $index => $valor) {
                        $tbl->addRow();
                        if ($index === 0) {
                            $tbl->addCell($colWidthsEnfoques[0], ['vMerge'=>'restart'])->addText($enfoqueInfo['enfoque']->nombre);
                        } else {
                            $tbl->addCell($colWidthsEnfoques[0], ['vMerge'=>'continue']);
                        }
                        $tbl->addCell($colWidthsEnfoques[1])->addText($valor['valor']);
                        $tbl->addCell($colWidthsEnfoques[2])->addText('● ' . $valor['actitud']);
                    }
                } else {
                    $tbl->addRow();
                    $tbl->addCell($colWidthsEnfoques[0])->addText($enfoqueInfo['enfoque']->nombre);
                    $tbl->addCell($colWidthsEnfoques[1])->addText('No especificado');
                    $tbl->addCell($colWidthsEnfoques[2])->addText('● No especificado');
                }
            }
        } else {
            $section->addText('No se han definido enfoques transversales.', ['italic'=>true]);
        }
        $section->addTextBreak(1);

        // === CRONOGRAMA DE SESIONES ===
        $section->addText('5. CRONOGRAMA DE SESIONES', ['bold'=>true,'color'=>'0066cc','size'=>12]);
        // Definir anchos de columnas según orientación
        if ($orientacion === 'horizontal') {
            $colWidthsCrono = [1500, 1000, 2000, 6000];
        } else {
            $colWidthsCrono = [900, 700, 1200, 3000];
        }
        if (!empty($cronograma)) {
            $tbl = $section->addTable([
                'borderSize'=>6,
                'borderColor'=>'0066cc',
                'cellMargin'=>80,
                'width' => 100 * 50,
                'unit' => 'pct',
            ]);
            $tbl->addRow();
            $tbl->addCell($colWidthsCrono[0])->addText('Semana', ['bold'=>true]);
            $tbl->addCell($colWidthsCrono[1])->addText('Día', ['bold'=>true]);
            $tbl->addCell($colWidthsCrono[2])->addText('Fecha', ['bold'=>true]);
            $tbl->addCell($colWidthsCrono[3])->addText('Sesiones', ['bold'=>true]);
            foreach ($cronograma as $semana) {
                foreach ($semana['dias'] as $diaIndex => $dia) {
                    $tbl->addRow();
                    if ($diaIndex === 0) {
                        $tbl->addCell($colWidthsCrono[0], ['vMerge'=>'restart','valign'=>'center'])->addText($semana['titulo_semana'] ?? 'Semana ' . $semana['semana_id']);
                    } else {
                        $tbl->addCell($colWidthsCrono[0], ['vMerge'=>'continue']);
                    }
                    $tbl->addCell($colWidthsCrono[1])->addText('Día ' . ($diaIndex + 1));
                    $tbl->addCell($colWidthsCrono[2])->addText(\Carbon\Carbon::parse($dia['fecha'])->format('d/m/Y'));
                    $sesionesText = '';
                    if (!empty($dia['sesiones'])) {
                        foreach ($dia['sesiones'] as $sesion) {
                            $sesionesText .= '• ' . $sesion['titulo'] . "\n";
                        }
                    } else {
                        $sesionesText = 'Sin sesiones';
                    }
                    $tbl->addCell($colWidthsCrono[3])->addText(trim($sesionesText));
                }
            }
        } else {
            $section->addText('No se ha definido cronograma para esta unidad.', ['italic'=>true]);
        }
        $section->addTextBreak(1);

        // === MATERIALES Y RECURSOS ===
        // Definir anchos de columnas según orientación
        if ($orientacion === 'horizontal') {
            $colWidthsMateriales = [6000, 6000];
        } else {
            $colWidthsMateriales = [3500, 3500];
        }
        $tbl = $section->addTable([
            'borderSize'=>6,
            'borderColor'=>'cccccc',
            'cellMargin'=>80,
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);
        $tbl->addRow();
        $tbl->addCell($colWidthsMateriales[0])->addText('MATERIALES BÁSICOS', ['bold'=>true]);
        $tbl->addCell($colWidthsMateriales[1])->addText('RECURSOS ADICIONALES', ['bold'=>true]);
        $tbl->addRow();
        $tbl->addCell($colWidthsMateriales[0])->addText($detalle && $detalle->materiales_basicos ? $detalle->materiales_basicos : 'No especificado');
        $tbl->addCell($colWidthsMateriales[1])->addText($detalle && $detalle->recursos ? $detalle->recursos : 'No especificado');
        $section->addTextBreak(1);

        // === FIRMAS DE LOS DOCENTES (2 columnas por fila, más filas si hay más docentes) ===
        $section->addText('Firmas de los docentes:', ['bold'=>true,'size'=>11], ['alignment'=>'center']);
        $section->addTextBreak(1);

        if ($profesores && $profesores->count() > 0) {
            // Obtener grado y sección de cada docente
            $docentes = [];
            foreach ($profesores as $profesor) {
                $nombre = $profesor['nombre_completo'] ?? 'Docente';
                $gradoSeccion = '-';
                if (isset($profesor['id'])) {
                    $user = \App\Models\User::with('aulas')->find($profesor['id']);
                    if ($user && $user->aulas && $user->aulas->count() > 0) {
                        $gradoSeccion = $user->aulas
                            ->map(function ($aula) {
                                return ($aula->grado ?? '-') . '° ' . ($aula->seccion ?? '-');
                            })
                            ->implode(', ');
                    }
                }
                $docentes[] = [
                    'nombre' => $nombre,
                    'grado_seccion' => $gradoSeccion,
                ];
            }

            // Siempre 2 columnas por fila (independiente de orientación)
            $cols = 2;
            $table = $section->addTable(['alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER, 'cellMargin'=>80]);
            $total = count($docentes);
            for ($i = 0; $i < $total; $i += $cols) {
                $table->addRow();
                for ($j = 0; $j < $cols; $j++) {
                    $idx = $i + $j;
                    if ($idx < $total) {
                        $doc = $docentes[$idx];
                        $cell = $table->addCell(5000, ['valign'=>'center']);
                        $cell->addText('_____________________________', ['size'=>10], ['alignment'=>'center']);
                        $cell->addText($doc['nombre'], ['bold'=>true,'size'=>10], ['alignment'=>'center']);
                        $cell->addText('Docente de ' . $doc['grado_seccion'], ['size'=>9,'color'=>'444444'], ['alignment'=>'center']);
                    } else {
                        $table->addCell(5000);
                    }
                }
            }
        } else {
            $section->addText('No asignado', ['italic'=>true], ['alignment'=>'center']);
        }

        // Guardar archivo temporal
        $rutaTemp = $this->generateTempFile('unidad_raw_' . $unidad->id);
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($rutaTemp);

        return $rutaTemp;
    }

    // NUEVO: Descargar Word en horizontal directamente (RAW)
    public function descargarHorizontal($id, Request $request)
    {
        try {
            $unidad = Unidad::findOrFail($id);
            $detalle = $unidad->detalles->first();
            $orientacion = 'horizontal';

            $profesores = $this->getProfesoresInfo($unidad->profesores_responsables);
            $cursosInfo = $this->procesarContenidoCurricular($detalle);
            $enfoquesInfo = $this->procesarEnfoquesTransversales($detalle);

            $cronograma = [];
            if ($detalle && $detalle->cronograma) {
                $cronograma = is_string($detalle->cronograma)
                    ? json_decode($detalle->cronograma, true)
                    : $detalle->cronograma;
            }

            // Siempre RAW y horizontal
            $rutaArchivo = $this->generarDocumentoDesdeCero($unidad, $detalle, $profesores, $cursosInfo, $enfoquesInfo, $cronograma, $orientacion);
            $nombreDescarga = 'Unidad_' . str_replace(' ', '_', $unidad->nombre) . '_horizontal_' . date('Y-m-d') . '.docx';
            return $this->downloadResponse($rutaArchivo, $nombreDescarga);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error al generar documento horizontal: ' . $e->getMessage()
            ], 500);
        }
    }

    // === MÉTODOS HELPER ===

    private function getProfesoresInfo($profesoresResponsables)
    {
        if (!$profesoresResponsables || empty($profesoresResponsables)) {
            return collect();
        }

        // Decodificar JSON si es string
        $profesoresIds = is_string($profesoresResponsables)
            ? json_decode($profesoresResponsables, true)
            : $profesoresResponsables;

        if (!is_array($profesoresIds)) {
            return collect();
        }

        // Obtener solo los IDs del array/objeto
        $ids = [];
        foreach ($profesoresIds as $key => $value) {
            if (is_numeric($key)) {
                // Si la clave es numérica, el valor es el ID
                $ids[] = $value;
            } elseif (is_numeric($value)) {
                // Si el valor es numérico, es el ID
                $ids[] = $value;
            } elseif (isset($value['id'])) {
                // Si es un objeto con ID
                $ids[] = $value['id'];
            }
        }

        if (empty($ids)) {
            return collect();
        }

        return User::whereIn('id', $ids)
            ->with('persona')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nombre_completo' => trim(($user->persona->nombre ?? '') . ' ' . ($user->persona->apellido ?? ''))
                ];
            });
    }

    public function vistaPreviaHtml($id, Request $request)
    {
        try {
            $unidad = Unidad::findOrFail($id);
            $detalle = $unidad->detalles->first(); // Esto puede ser null
            $orientacion = $request->get('orientacion', 'vertical');

            // Procesar datos directamente desde la unidad
            $profesores = $this->getProfesoresInfo($unidad->profesores_responsables);
            $cursosInfo = $this->procesarContenidoCurricular($detalle);
            $enfoquesInfo = $this->procesarEnfoquesTransversales($detalle);

            // NUEVO: Procesar cronograma para vista previa
            $cronograma = [];
            if ($detalle && $detalle->cronograma) {
                $cronograma = is_string($detalle->cronograma)
                    ? json_decode($detalle->cronograma, true)
                    : $detalle->cronograma;
            }

            $datosVista = [
                'unidad' => $unidad,
                'detalle' => $detalle,
                'profesores' => $profesores,
                'cursosInfo' => $cursosInfo,
                'enfoquesInfo' => $enfoquesInfo,
                'orientacion' => $orientacion,
                'materialesBasicos' => $detalle ? $detalle->materiales_basicos : 'No especificado',
                'recursos' => $detalle ? $detalle->recursos : 'No especificado',
                'cronograma' => $cronograma,
            ];

            // CORREGIDO: vista correcta
            $vista = $orientacion === 'horizontal'
                ? 'filament.docente.documentos.unidad.vista-previa-unidad-horizontal'
                : 'filament.docente.documentos.unidad.vista-previa-unidad';

            return view($vista, $datosVista);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error al generar vista previa: ' . $e->getMessage()
            ], 500);
        }
    }

    // 🔄 NUEVO: Método para verificar si la unidad tiene datos en la tabla principal
    public function debug($id)
    {
        $unidad = Unidad::findOrFail($id);
        $detalle = $unidad->detalles->first();

        echo "<h2>🔍 DEBUG - Unidad ID: {$id}</h2>";

        // Debug básico de la unidad
        echo "<h3>📋 Datos de la Unidad:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
        echo "Nombre: " . $unidad->nombre . "\n";
        echo "Situación Significativa: " . ($unidad->situacion_significativa ?? 'NULL') . "\n";
        echo "Productos: " . ($unidad->productos ?? 'NULL') . "\n";
        echo "Profesores Responsables: ";
        var_dump($unidad->profesores_responsables);
        echo "</pre>";

        // Debug detalle
        echo "<h3>📊 Detalle de la Unidad:</h3>";
        if ($detalle) {
            echo "<pre style='background: #f0f8ff; padding: 10px; border: 1px solid #ddd;'>";
            echo "ID Detalle: " . $detalle->id . "\n";
            echo "Contenido existe: " . ($detalle->contenido ? 'SÍ' : 'NO') . "\n";
            echo "Enfoques existe: " . ($detalle->enfoques ? 'SÍ' : 'NO') . "\n";
            echo "Materiales básicos: " . ($detalle->materiales_basicos ?? 'NULL') . "\n";
            echo "Recursos: " . ($detalle->recursos ?? 'NULL') . "\n";
            echo "</pre>";

            // Debug contenido
            if ($detalle->contenido) {
                echo "<h3>📚 Contenido Curricular (RAW):</h3>";
                echo "<pre style='background: #fff5ee; padding: 10px; border: 1px solid #ddd;'>";
                $contenido = is_string($detalle->contenido) 
                    ? json_decode($detalle->contenido, true) 
                    : $detalle->contenido;
                var_dump($contenido);
                echo "</pre>";
            }

            // Debug enfoques
            if ($detalle->enfoques) {
                echo "<h3>🎯 Enfoques (RAW):</h3>";
                echo "<pre style='background: #f0fff0; padding: 10px; border: 1px solid #ddd;'>";
                $enfoques = is_string($detalle->enfoques) 
                    ? json_decode($detalle->enfoques, true) 
                    : $detalle->enfoques;
                var_dump($enfoques);
                echo "</pre>";
            }
        } else {
            echo "<p style='color: red;'>❌ No se encontró detalle para esta unidad</p>";
            echo "<p style='color: orange;'>💡 Esto significa que la unidad no tiene datos curriculares asociados en la tabla 'unidad_detalles'</p>";
        }

        // Procesamiento actual
        $cursosInfo = $this->procesarContenidoCurricular($detalle);
        $enfoquesInfo = $this->procesarEnfoquesTransversales($detalle);

        echo "<h3>📖 Resultado del Procesamiento:</h3>";
        echo "<pre style='background: #f0f8ff; padding: 10px; border: 1px solid #ddd;'>";
        echo "Cursos encontrados: " . count($cursosInfo) . "\n";
        echo "Enfoques encontrados: " . count($enfoquesInfo) . "\n";
        echo "</pre>";
    }
}
