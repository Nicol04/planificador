<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa Horizontal - {{ $unidad->nombre }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }
        }

        .document-preview {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
        }

        .document-header {
            border-bottom: 3px solid #0066cc;
            padding: 15px;
            text-align: center;
        }

        .logos-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .document-title {
            color: #0066cc;
            font-weight: bold;
            font-size: 20px;
            margin: 8px 0;
        }

        .section-title {
            background: #0066cc;
            color: white;
            padding: 8px 12px;
            margin: 15px 0 8px 0;
            font-weight: bold;
            border-radius: 5px;
            font-size: 14px;
        }

        .info-table {
            border: 1px solid #ddd;
            width: 100%;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .info-table td,
        .info-table th {
            border: 1px solid #ddd;
            padding: 6px;
            vertical-align: top;
        }

        .info-table th {
            background: #f8f9fa;
            font-weight: bold;
            width: 25%;
        }

        .table th {
            background: #0066cc !important;
            color: white !important;
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }

        .table td {
            padding: 6px;
            vertical-align: top;
            border: 1px solid #ddd;
            font-size: 10px;
            line-height: 1.3;
        }

        .floating-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .btn-floating {
            margin-bottom: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: block;
            width: 150px;
        }

        .enfoques-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .enfoques-table th {
            background: #0066cc;
            color: white;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        .enfoques-table td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        .content-text {
            font-size: 12px;
            line-height: 1.4;
            text-align: justify;
            margin-bottom: 15px;
        }

        .table .bg-light {
            background-color: #f8f9fa !important;
        }

        .table .fw-bold {
            font-weight: bold;
        }

        .table .text-primary {
            color: #0066cc !important;
        }

        .badge {
            font-size: 9px;
            padding: 2px 5px;
        }

        /* Asegura que la tabla del cronograma se imprima y descargue completa y sin scroll */
        .cronograma-table-wrapper {
            width: 100%;
            overflow: visible !important;
        }

        .cronograma-table-wrapper table {
            width: 100% !important;
            min-width: 0 !important;
        }

        @media print {
            .cronograma-table-wrapper {
                overflow: visible !important;
                max-width: none !important;
            }

            .cronograma-table-wrapper table {
                width: 100% !important;
                min-width: 0 !important;
            }
        }
    </style>
</head>

<body class="bg-light">
    <!-- Botones flotantes -->
    <div class="floating-buttons no-print">
        <button class="btn btn-success btn-floating" onclick="descargarDocumentoHorizontal()">
            <i class="fas fa-download"></i> Descargar Word
        </button>
        <button class="btn btn-info btn-floating" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <button class="btn btn-secondary btn-floating" onclick="cerrarVentana()">
            <i class="fas fa-arrow-left"></i> Volver
        </button>
    </div>

    <div class="document-preview">
        <!-- Header con logos más compactos -->
        <div class="document-header">
            <div class="logos-container">
                <div class="logo-container" style="width: 60px; height: 60px;">
                    <img src="{{ url('assets/img/logo_colegio.png') }}" alt="Logo Institución"
                        style="width: 60px; height: 60px; object-fit: contain;">
                </div>

                <div class="text-center flex-grow-1">
                    <div class="logo-container" style="width: 140px; height: 60px; margin: 0 auto 8px;">
                        <img src="{{ url('assets/img/logo_ministerio.png') }}" alt="Logo MINEDU"
                            style="width: 140px; height: 60px; object-fit: contain;">
                    </div>
                    <div class="document-title">UNIDAD DE APRENDIZAJE</div>
                    <h4 style="color: #0066cc; margin: 5px 0;">"{{ $unidad->nombre }}"</h4>
                    <h6 style="color: #0066cc; margin: 0;">{{ date('Y') }}</h6>
                </div>

                <div class="logo-container" style="width: 60px; height: 60px;">
                    <img src="{{ url('assets/img/ugel_logo.jpg') }}" alt="Logo UGEL"
                        style="width: 60px; height: 60px; object-fit: contain;">
                </div>
            </div>
        </div>

        <div class="p-3">
            <!-- 1. DATOS INFORMATIVOS -->
            <div class="section-title">1. DATOS INFORMATIVOS:</div>

            <table class="info-table">
                <tr>
                    <th style="width: 40%;">1.1. Nombre de la unidad de aprendizaje:</th>
                    <td>{{ $unidad->nombre }}</td>
                </tr>
                <tr>
                    <th>1.2. Institución educativa:</th>
                    <td>ANN GOULDEN</td>
                </tr>
                <tr>
                    <th>1.3. Directora:</th>
                    <td>JULIANA RUIZ FALERO</td>
                </tr>
                <tr>
                    <th>1.4. Subdirectores:</th>
                    <td>FELIX HARLE SILUPU RAMÍREZ y ELIZABETH ARELLANO SIANCAS</td>
                </tr>
                <tr>
                    <th>1.5. Grado y sección:</th>
                    <td>{{ $unidad->grado }}° grado - Secciones: {{ implode(', ', $unidad->secciones ?? []) }}</td>
                </tr>
                <tr>
                    <th>1.6. Temporalización:</th>
                    <td>Inicio: {{ $unidad->fecha_inicio->format('d/m/Y') }} | Término:
                        {{ $unidad->fecha_fin->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>1.7. Profesores responsables:</th>
                    <td>
                        @if ($profesores && $profesores->count() > 0)
                            @foreach ($profesores as $profesor)
                                • {{ $profesor['nombre_completo'] }}<br>
                            @endforeach
                        @else
                            • No asignado
                        @endif
                    </td>
                </tr>
            </table>

            <!-- 2. SITUACIÓN SIGNIFICATIVA -->
            <div class="section-title">2. SITUACIÓN SIGNIFICATIVA:</div>
            <div class="content-text">
                {{ $unidad->situacion_significativa ?? 'No especificada' }}
            </div>

            <!-- 3. PROPÓSITOS DE APRENDIZAJE -->
            <div class="section-title">3. PROPÓSITOS DE APRENDIZAJE:</div>

            @if (count($cursosInfo) > 0)
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 15%;">ÁREA</th>
                            <th style="width: 25%;">COMPETENCIAS/CAPACIDADES</th>
                            <th style="width: 25%;">DESEMPEÑOS</th>
                            <th style="width: 15%;">CRITERIOS DE EVALUACIÓN</th>
                            <th style="width: 10%;">EVIDENCIAS</th>
                            <th style="width: 10%;">INSTRUMENTO DE EVALUACIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cursosInfo as $cursoIndex => $cursoInfo)
                            @php
                                $competencias = $cursoInfo['competencias'];
                                $totalCompetencias = count($competencias);
                            @endphp

                            @if ($totalCompetencias > 0)
                                <!-- ✅ CREAR UNA FILA POR CADA COMPETENCIA -->
                                @foreach ($competencias as $compIndex => $competenciaInfo)
                                    <tr
                                        style="border-top: {{ $compIndex === 0 ? '2px solid #0066cc' : '1px solid #dee2e6' }};">
                                        <!-- ÁREA - Solo mostrar en la primera competencia del curso -->
                                        @if ($compIndex === 0)
                                            <td rowspan="{{ $totalCompetencias }}"
                                                class="align-middle text-center fw-bold bg-light"
                                                style="vertical-align: middle; border-right: 2px solid #0066cc; font-size: 10px;">
                                                {{ $cursoInfo['curso']->curso }}
                                            </td>
                                        @endif

                                        <!-- COMPETENCIAS Y CAPACIDADES - Una por fila -->
                                        <td style="border-left: 1px solid #ddd; padding: 6px;">
                                            <div class="fw-bold text-primary mb-1" style="font-size: 9px;">
                                                <i class="fas fa-target me-1"></i>
                                                {{ $competenciaInfo['competencia']->nombre }}
                                            </div>
                                            @if ($competenciaInfo['capacidades']->count() > 0)
                                                <div class="ms-2" style="margin-top: 4px;">
                                                    <strong class="text-success"
                                                        style="font-size: 8px;">Capacidades:</strong>
                                                    @foreach ($competenciaInfo['capacidades'] as $capacidad)
                                                        <div class="small text-muted mt-1"
                                                            style="font-size: 8px; line-height: 1.2;">
                                                            <i class="fas fa-arrow-right me-1"
                                                                style="font-size: 6px;"></i>
                                                            {{ $capacidad->nombre }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>

                                        <!-- DESEMPEÑOS - Una competencia por fila -->
                                        <td style="padding: 6px;">
                                            @if ($competenciaInfo['desempenos']->count() > 0)
                                                @foreach ($competenciaInfo['desempenos'] as $desempeno)
                                                    <div class="small mb-1" style="font-size: 8px; line-height: 1.2;">
                                                        <i class="fas fa-check-circle text-success me-1"
                                                            style="font-size: 6px;"></i>
                                                        {{ $desempeno->descripcion }}
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-muted small">No especificado</span>
                                            @endif
                                        </td>

                                        <!-- CRITERIOS - Una competencia por fila -->
                                        <td class="small" style="padding: 6px; font-size: 8px;">
                                            {{ $competenciaInfo['criterios'] ?: 'No especificado' }}
                                        </td>

                                        <!-- EVIDENCIAS - Una competencia por fila -->
                                        <td class="small" style="padding: 6px; font-size: 8px;">
                                            {{ $competenciaInfo['evidencias'] ?: 'No especificado' }}
                                        </td>

                                        <!-- INSTRUMENTOS - Una competencia por fila -->
                                        <td style="padding: 6px;">
                                            @if (is_array($competenciaInfo['instrumentos']) && count($competenciaInfo['instrumentos']) > 0)
                                                @foreach ($competenciaInfo['instrumentos'] as $instrumento)
                                                    <span class="badge bg-secondary small d-block mb-1"
                                                        style="font-size: 7px;">
                                                        {{ $instrumento }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted small">No especificado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                <!-- ✅ LÍNEA SEPARADORA GRUESA ENTRE CURSOS -->
                                @if (!$loop->last)
                                    <tr>
                                        <td colspan="6"
                                            style="height: 3px; background: linear-gradient(45deg, #0066cc, #004499); border: none; padding: 0;">
                                        </td>
                                    </tr>
                                @endif
                            @else
                                <!-- Curso sin competencias -->
                                <tr style="border-top: 2px solid #0066cc;">
                                    <td class="text-center fw-bold bg-light" style="border-right: 2px solid #0066cc;">
                                        {{ $cursoInfo['curso']->curso }}
                                    </td>
                                    <td colspan="5" class="text-center text-muted">No hay competencias definidas</td>
                                </tr>

                                @if (!$loop->last)
                                    <tr>
                                        <td colspan="6"
                                            style="height: 3px; background: linear-gradient(45deg, #0066cc, #004499); border: none; padding: 0;">
                                        </td>
                                    </tr>
                                @endif
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="content-text">No se ha definido contenido curricular para esta unidad.</div>
            @endif

            <!-- 4. ENFOQUES: VALORES Y ACTITUDES -->
            <div class="section-title">4. ENFOQUES: VALORES Y ACTITUDES</div>

            @if (count($enfoquesInfo) > 0)
                <table class="enfoques-table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">ENFOQUES TRANSVERSALES</th>
                            <th style="width: 25%;">VALORES</th>
                            <th style="width: 40%;">ACTITUDES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($enfoquesInfo as $enfoqueInfo)
                            @if (count($enfoqueInfo['valores']) > 0)
                                @foreach ($enfoqueInfo['valores'] as $index => $valor)
                                    <tr>
                                        <td>
                                            @if ($index === 0)
                                                {{ $enfoqueInfo['enfoque']->nombre }}
                                            @endif
                                        </td>
                                        <td>{{ $valor['valor'] }}</td>
                                        <td>● {{ $valor['actitud'] }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td>{{ $enfoqueInfo['enfoque']->nombre }}</td>
                                    <td>No especificado</td>
                                    <td>● No especificado</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="content-text">No se han definido enfoques transversales.</div>
            @endif

            <!-- 6. CRONOGRAMA DE SESIONES -->
            <div class="section-title">5. CRONOGRAMA DE SESIONES</div>
            @if (!empty($cronograma))
                <!-- Elimina el div .table-responsive y asegúrate de que la tabla ocupe el 100% -->
                <div class="cronograma-table-wrapper">
                    <table class="table table-bordered w-100" style="font-size:11px; min-width: 700px;">
                        <thead>
                            <tr>
                                <th>Semana</th>
                                <th>Día</th>
                                <th>Fecha</th>
                                <th>Sesiones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cronograma as $semana)
                                @foreach ($semana['dias'] as $diaIndex => $dia)
                                    <tr>
                                        @if ($diaIndex === 0)
                                            <td rowspan="{{ count($semana['dias']) }}"
                                                class="align-middle text-center fw-bold bg-light">
                                                {{ $semana['titulo_semana'] ?? 'Semana ' . $semana['semana_id'] }}
                                            </td>
                                        @endif
                                        <td>Día {{ $diaIndex + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($dia['fecha'])->format('d/m/Y') }}</td>
                                        <td>
                                            @if (!empty($dia['sesiones']))
                                                <ul style="padding-left: 18px; margin:0;">
                                                    @foreach ($dia['sesiones'] as $sesion)
                                                        <li>{{ $sesion['titulo'] }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">Sin sesiones</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="content-text">No se ha definido cronograma para esta unidad.</div>
            @endif

            <!-- 5. MATERIALES BÁSICOS Y RECURSOS -->
            <div class="section-title">6. MATERIALES BÁSICOS Y RECURSOS A UTILIZAR EN LA UNIDAD</div>
            <table class="info-table">
                <tr>
                    <th style="width: 50%;">MATERIALES BÁSICOS</th>
                    <th style="width: 50%;">RECURSOS ADICIONALES</th>
                </tr>
                <tr>
                    <td>{{ $materialesBasicos }}</td>
                    <td>{{ $recursos }}</td>
                </tr>
            </table>
            {{-- =================== FIRMAS DE LOS DOCENTES =================== --}}
            <div style="margin-top: 60px; margin-bottom: 40px;">
                <hr style="border-top: 2px solid #333; margin-bottom: 30px;">
                @if ($profesores && $profesores->count() > 0)
                    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 40px;">
                        @foreach ($profesores as $profesor)
                            @php
                                // Buscar aulas donde el usuario es docente (relación usuario_aulas)
                                $aulas = [];
                                if (isset($profesor['id'])) {
                                    $user = \App\Models\User::with('aulas')->find($profesor['id']);
                                    if ($user && $user->aulas) {
                                        $aulas = $user->aulas;
                                    }
                                }
                                // Mostrar grado y sección de todas sus aulas (puede tener varias)
                                $gradoSeccion = '';
                                if (count($aulas) > 0) {
                                    $gradoSeccion = $aulas
                                        ->map(function ($aula) {
                                            return ($aula->grado ?? '-') . '° ' . ($aula->seccion ?? '-');
                                        })
                                        ->join(', ');
                                } else {
                                    $gradoSeccion = '-';
                                }
                            @endphp
                            <div style="flex: 0 0 300px; margin-bottom: 30px; text-align: center;">
                                <div
                                    style="border-bottom:1px solid #888; width:220px; height:24px; margin: 0 auto 8px auto;">
                                </div>
                                <div style="font-size:15px; font-weight: bold;">
                                    {{ $profesor['nombre_completo'] ?? 'Docente' }}
                                </div>
                                <div style="font-size:13px; color:#444;">
                                    Docente de {{ $gradoSeccion }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align:center; color:#888; margin-top:20px;">No asignado</div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Función para descargar Word en formato horizontal
        function descargarDocumentoHorizontal() {
            window.location.href = `/unidades/{{ $unidad->id }}/previsualizar?orientacion=horizontal&descargar=1&raw=1`;
        }

        function cerrarVentana() {
            // Primero intentar cerrar si es popup
            if (window.opener !== null && !window.opener.closed) {
                window.close();
                return;
            }

            // Si hay historial, ir atrás
            if (window.history.length > 1) {
                window.history.back();
            } else {
                // Si no hay historial, ir a la lista de unidades
                window.location.href = '/docente/unidads';
            }
        }

        // Detectar teclas para navegación
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarVentana();
            }
            // Alt + Flecha izquierda para volver
            if (event.altKey && event.key === 'ArrowLeft') {
                cerrarVentana();
            }
        });

        // Mostrar un toast de ayuda al cargar
        window.addEventListener('load', function() {
            setTimeout(function() {
                const helpDiv = document.createElement('div');
                helpDiv.innerHTML = `
                <div style="position: fixed; bottom: 20px; left: 20px; background: #333; color: white; 
                     padding: 10px 15px; border-radius: 5px; font-size: 12px; z-index: 1001;" 
                     id="helpToast">
                    <i class="fas fa-info-circle"></i> Presiona <kbd>ESC</kbd> o <kbd>Alt + ←</kbd> para volver
                </div>
            `;
                document.body.appendChild(helpDiv);

                // Ocultar después de 3 segundos
                setTimeout(function() {
                    const toast = document.getElementById('helpToast');
                    if (toast) {
                        toast.style.opacity = '0';
                        toast.style.transition = 'opacity 0.5s';
                        setTimeout(() => toast.remove(), 500);
                    }
                }, 3000);
            }, 1000);
        });
    </script>
</body>

</html>
