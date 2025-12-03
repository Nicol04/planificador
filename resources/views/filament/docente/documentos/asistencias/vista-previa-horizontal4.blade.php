<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de asistencia - {{ $mes ?? 'Mes no definido' }} {{ $anio ?? '' }}</title>

    <!-- Bootstrap CSS (solo para utilidades básicas, nuestros estilos mandan) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- NUESTRO CSS PERSONALIZADO -->
    <link rel="stylesheet" href="{{ asset('assets/style/asistencia/fiestas_patrias.css') }}">
</head>

<body>

    <div class="document-preview">
        
        <!-- TOOLBAR (Fuera del marco para no imprimirlo) -->
        <div class="preview-toolbar no-print">
            <div class="preview-actions">
                <button class="btn-ghost" onclick="window.close()" title="Cerrar">Cerrar</button>
                <button class="btn-ghost" onclick="window.print()" title="Imprimir">Imprimir</button>
                <button id="toggle-fullscreen" class="btn-outline" title="Expandir">Expandir</button>

                <form id="download-word-form" method="GET"
                    action="{{ action('\\App\\Http\\Controllers\\Documents\\AsistenciaDocumentController@descargarDocx', ['id' => request()->route('id') ?? request()->input('id')]) }}"
                    target="_blank" style="display:inline;margin-left:6px;">
                    <input type="hidden" name="mes" value="{{ $mes ?? '' }}">
                    <input type="hidden" name="anio" value="{{ $anio ?? '' }}">
                    <input type="hidden" name="selectedDates" value='@json($selectedDates ?? [])'>
                    <input type="hidden" name="plantilla_id" value="{{ request()->input('plantilla_id') ?? '' }}">
                    <button type="submit" class="btn-primary" title="Descargar como Word">Descargar .docx</button>
                </form>

                <a class="btn-primary" href="#" onclick="window.print();return false;" title="Descargar PDF">Descargar PDF</a>
            </div>
        </div>

        <!-- MARCO DEL DOCUMENTO (La hoja de papel) -->
        <div class="document-frame">
            
            <!-- DECORACIÓN 1: Banderola colgando (Absolute) -->
            <div class="decoration-banderola"></div>
            
            {{-- Encabezado --}}
            <div class="header-banner">
                <!-- Logo Izquierda (Insignia más grande) -->
                <img src="{{ url('assets/img/logo_colegio.png') }}" alt="Logo colegio" class="header-logo left-logo logo-big">

                <!-- Título Central con Mascotas a los lados -->
                <div class="header-title d-flex align-items-center justify-content-center">
                    <!-- Mascota Niña Perú (izquierda) -->
                    <img src="{{ url('assets/img/asistencia_peru/niña.jpg') }}" alt="Niña Perú" class="header-mascot mascot-big d-none d-md-block">

                    <div>
                        <h1>Registro de Asistencia</h1>
                        <p>Institución Educativa Ann Goulden</p>

                        <!-- Leyenda debajo del título -->
                        <div class="legend-card legend-under-title">
                            <div class="legend-chip">
                                <div class="legend-dot att">✓</div>
                                <div class="legend-label">Asistió</div>
                            </div>
                            <div class="legend-chip">
                                <div class="legend-dot abs">✕</div>
                                <div class="legend-label">Falta</div>
                            </div>
                            <div class="legend-chip">
                                <div class="legend-dot exc">J</div>
                                <div class="legend-label">Justif.</div>
                            </div>
                            <div class="legend-chip">
                                <div class="legend-dot lat">T</div>
                                <div class="legend-label">Tard.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Mascota Niño Perú (derecha) -->
                    <img src="{{ url('assets/img/asistencia_peru/niño.jpg') }}" alt="Niño Perú" class="header-mascot mascot-big d-none d-md-block">
                </div>

                <!-- Logo Derecha -->
                <img src="{{ url('assets/img/logo_ministerio.png') }}" alt="Logo MINEDU" class="header-logo right-logo">
            </div>

            {{-- Información Docente en una sola fila con bandera --}}
            <div class="info-card peru-dashed-rect" style="justify-content: space-between;">
                <div class="info-meta" style="display: flex; gap: 40px; align-items: center;">
                    <div>
                        <span class="label">Docente Responsable</span>
                        <span class="value" style="margin-left:8px;">{{ $docenteNombre ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="label">Grado y Sección</span>
                        <span class="value" style="margin-left:8px;">{{ $gradoSeccion ?? '—' }}</span>
                    </div>
                </div>
                <img src="{{ url('assets/img/asistencia_peru/bandera.jpg') }}" alt="Bandera Perú" class="docente-bandera">
            </div>

            {{-- TABLA DE ASISTENCIA --}}
            <div class="table-responsive" style="position:relative;">
                <!-- Marca de agua Machu Picchu -->
                <img src="{{ url('assets/img/asistencia_peru/machupichu.jpg') }}" class="watermark-machupicchu" alt="Machupicchu">
                <!-- Marca de agua Bandera -->
                <img src="{{ url('assets/img/asistencia_peru/bandera.jpg') }}" class="watermark-bandera" alt="Bandera Perú">

                @php
                    $weeksCount = isset($matrix) && is_array($matrix) ? count($matrix) : 0;
                    $normalizedSelected = [];
                    if (!empty($selectedDates) && is_array($selectedDates)) {
                        foreach ($selectedDates as $sd) {
                            if (is_string($sd) && trim($sd) !== '') {
                                $normalizedSelected[trim($sd)] = true;
                            }
                        }
                    }
                    $validDaysPerWeek = [];
                    $totalVisibleDays = 0;
                    if ($weeksCount > 0) {
                        foreach ($matrix as $wIndex => $week) {
                            $valid = [];
                            foreach ($week as $dayKey => $info) {
                                if (!empty($info['date'])) {
                                    $valid[$dayKey] = $info;
                                }
                            }
                            $validDaysPerWeek[$wIndex] = $valid;
                            $totalVisibleDays += count($valid);
                        }
                    } else {
                        $totalVisibleDays = 4 * 5;
                    }
                @endphp

                <table class="table">
                    <thead>
                        {{-- FILA 1 --}}
                        <tr>
                            <th rowspan="4" class="sticky-col index" style="width:40px;">N°</th>
                            <th rowspan="4" class="sticky-col name" style="min-width:250px;">Apellidos y nombres</th>

                            <th colspan="{{ $totalVisibleDays }}" style="font-size:16px; padding:10px;">
                                {{ strtoupper(($mes ?? now()->translatedFormat('F')) . ' - ' . ($anio ?? date('Y'))) }}
                            </th>

                            {{-- Resumen Vertical --}}
                            <th rowspan="4" class="vertical-col"><span class="vertical-header">Asistencias</span></th>
                            <th rowspan="4" class="vertical-col"><span class="vertical-header">Faltas</span></th>
                            <th rowspan="4" class="vertical-col"><span class="vertical-header">Tardanzas</span></th>
                            <th rowspan="4" class="vertical-col"><span class="vertical-header">Justificadas</span></th>
                        </tr>

                        {{-- FILA 2: Semanas --}}
                        <tr>
                            @if ($weeksCount > 0)
                                @foreach ($validDaysPerWeek as $wIndex => $valid)
                                    @php $colspan = count($valid); @endphp
                                    @if ($colspan > 0)
                                        <th colspan="{{ $colspan }}">Semana {{ $wIndex + 1 }}</th>
                                    @endif
                                @endforeach
                            @else
                                @for ($w = 1; $w <= 4; $w++)
                                    <th colspan="5">Semana {{ $w }}</th>
                                @endfor
                            @endif
                        </tr>

                        {{-- FILA 3: Días Letra --}}
                        <tr>
                            @if ($weeksCount > 0)
                                @foreach ($validDaysPerWeek as $valid)
                                    @foreach (array_keys($valid) as $d)
                                        <th>{{ $d }}</th>
                                    @endforeach
                                @endforeach
                            @else
                                @for ($w = 1; $w <= 4; $w++)
                                    @foreach (['L', 'Ma', 'Mi', 'J', 'V'] as $d)
                                        <th>{{ $d }}</th>
                                    @endforeach
                                @endfor
                            @endif
                        </tr>

                        {{-- FILA 4: Días Número --}}
                        <tr>
                            @if ($weeksCount > 0)
                                @foreach ($validDaysPerWeek as $valid)
                                    @foreach ($valid as $dKey => $info)
                                        @php
                                            $date = $info['date'] ?? null;
                                            $isNoClass = $date && isset($normalizedSelected[$date]);
                                        @endphp
                                        <th class="{{ $isNoClass ? 'no-class-column' : '' }}">
                                            {{ $date ? \Carbon\Carbon::parse($date)->format('d') : '' }}
                                        </th>
                                    @endforeach
                                @endforeach
                            @else
                                @for ($w = 1; $w <= 4; $w++)
                                    @foreach (['L', 'Ma', 'Mi', 'J', 'V'] as $d)
                                        <th></th>
                                    @endforeach
                                @endfor
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php $displayTotalDays = $totalVisibleDays > 0 ? $totalVisibleDays : 20; @endphp

                        @forelse($estudiantes as $index => $est)
                            <tr>
                                <td class="sticky-col index text-center">{{ $index + 1 }}</td>
                                <td class="sticky-col name">{{ $est['nombre'] }}</td>

                                @if ($weeksCount > 0)
                                    @foreach ($validDaysPerWeek as $valid)
                                        @foreach ($valid as $dKey => $info)
                                            @php
                                                $date = $info['date'] ?? null;
                                                $isNoClass = $date && isset($normalizedSelected[$date]);
                                            @endphp
                                            <td class="{{ $isNoClass ? 'no-class-column' : '' }}"></td>
                                        @endforeach
                                    @endforeach
                                @else
                                    @for ($i = 0; $i < $displayTotalDays; $i++)
                                        <td></td>
                                    @endfor
                                @endif

                                {{-- Celdas Resumen --}}
                                <td></td><td></td><td></td><td></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 6 + $displayTotalDays }}" class="text-center p-4">
                                    No hay estudiantes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Marca de agua Llama sigue abajo derecha -->
            <img src="{{ url('assets/img/asistencia_peru/llama.png') }}" class="watermark-llama" alt="Llama">
        </div>
    </div>

    <!-- Scripts de Funcionalidad (SweetAlert y Fullscreen) -->
    <script>
        // Lógica de pantalla completa
        (function() {
            const btn = document.getElementById('toggle-fullscreen');
            if (btn) {
                btn.addEventListener('click', function() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(() => {});
                    } else {
                        document.exitFullscreen().catch(() => {});
                    }
                });
            }
        })();

        // Lógica de descarga Word
        (function() {
            function ensureSwal(cb) {
                if (typeof Swal !== 'undefined') return cb();
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                s.onload = cb;
                document.head.appendChild(s);
            }

            const form = document.getElementById('download-word-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    ensureSwal(function() {
                        Swal.fire({
                            title: 'Atención',
                            text: 'El diseño patriótico (fondo, imágenes) podría no mostrarse igual en Word.',
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonColor: '#D91023', // Rojo Perú
                            confirmButtonText: 'Descargar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) form.submit();
                        });
                    });
                });
            }
        })();
    </script>
</body>
</html>