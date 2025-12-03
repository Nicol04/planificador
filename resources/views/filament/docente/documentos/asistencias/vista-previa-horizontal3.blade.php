<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de asistencia - {{ $mes ?? 'Mes no definido' }} {{ $anio ?? '' }}</title>

    <!-- Nuevas fuentes: serif para títulos + sans para UI -->
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@300;400;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/style/asistencia/3.css') }}">

</head>

<body class="bg-light">

    <div class="document-preview document-frame">
        {{-- Toolbar y título --}}
        <div class="preview-toolbar no-print">

            <div class="preview-actions">
                <button class="btn-ghost" onclick="window.close()" title="Cerrar">Cerrar</button>
                <button class="btn-ghost" onclick="window.print()" title="Imprimir">Imprimir</button>
                <button id="toggle-fullscreen" class="btn-outline" title="Expandir">Expandir</button>

                <!-- Nuevo: formulario para descargar como Word (.docx) -->
                <form id="download-word-form" method="GET"
                    action="{{ action('\\App\\Http\\Controllers\\Documents\\AsistenciaDocumentController@descargarDocx', ['id' => request()->route('id') ?? request()->input('id')]) }}"
                    target="_blank" style="display:inline;margin-left:6px;">
                    <input type="hidden" name="mes" value="{{ $mes ?? '' }}">
                    <input type="hidden" name="anio" value="{{ $anio ?? '' }}">
                    <input type="hidden" name="selectedDates" value='@json($selectedDates ?? [])'>
                    <input type="hidden" name="plantilla_id" value="{{ request()->input('plantilla_id') ?? '' }}">
                    <button type="submit" class="btn-primary" title="Descargar como Word (.docx)">Descargar
                        .docx</button>
                </form>

                <a class="btn-primary" href="#" onclick="window.print();return false;"
                    title="Descargar PDF">Descargar</a>
            </div>
        </div>

        {{-- Encabezado con logos y título --}}
        <div class="header-banner">
            <img src="{{ url('assets/img/logo_colegio.png') }}" alt="Logo colegio" class="header-logo left-logo">

            <div class="header-title">
                <h1>Registro de asistencia</h1>
                <p>Institución Educativa Ann Goulden</p>
            </div>

            <img src="{{ url('assets/img/logo_ministerio.png') }}" alt="Logo MINEDU"
                style="width: 120px; height: 54px; object-fit: contain;" class="header-logo right-logo">
        </div>

        {{-- Información del docente y leyenda en tarjeta --}}
        <div class="info-card">
            <div class="info-left" style="align-items:flex-start;">
                {{-- Eliminado: avatar/initials dinámico por petición del usuario --}}
                <div class="info-meta">
                    <div class="label">Docente</div>
                    <div class="value">{{ $docenteNombre ?? '—' }}</div>
                    <div class="label" style="margin-top:6px;">Grado / Sección</div>
                    <div class="value">{{ $gradoSeccion ?? '—' }}</div>
                </div>
            </div>

            <!-- leyenda mejorada: añadir Tardanza -->
            <div class="legend-card" role="list" aria-label="Leyenda de asistencia" title="Leyenda">
                <div class="legend-chip" role="listitem" title="Asistió">
                    <div class="legend-dot att" aria-hidden="true">✓</div>
                    <div class="legend-label">Asistió<br><small
                            style="color:var(--muted);font-weight:600;">Presente</small></div>
                </div>

                <div class="legend-chip" role="listitem" title="Falta">
                    <div class="legend-dot abs" aria-hidden="true">✕</div>
                    <div class="legend-label">Falta<br><small
                            style="color:var(--muted);font-weight:600;">Ausente</small></div>
                </div>

                <div class="legend-chip" role="listitem" title="Justificado">
                    <div class="legend-dot exc" aria-hidden="true">J</div>
                    <div class="legend-label">Justificado<br><small style="color:var(--muted);font-weight:600;">Excusa
                            válida</small></div>
                </div>

                <div class="legend-chip" role="listitem" title="Tardanza">
                    <div class="legend-dot lat" aria-hidden="true">T</div>
                    <div class="legend-label">Tardanza<br><small style="color:var(--muted);font-weight:600;">Llegada
                            tarde</small></div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
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

            <table class="table table-sm table-bordered w-100">
                <thead>
                    {{-- FILA 1: N°, ESTUDIANTES y MES-AÑO encima de todas las columnas de días (colspan totalVisibleDays) --}}
                    <tr>
                        <th rowspan="4" class="sticky-col index" style="width:4%;">N°</th>
                        <th rowspan="4" class="sticky-col name" style="width:34%;">Apellidos y nombres</th>

                        {{-- MES - AÑO ocupa todas las columnas de días visibles --}}
                        <th colspan="{{ $totalVisibleDays }}" class="text-center"
                            style="vertical-align: middle; padding:12px 8px; font-weight:700;">
                            {{ strtoupper(($mes ?? now()->translatedFormat('F')) . ' - ' . ($anio ?? date('Y'))) }}
                        </th>

                        {{-- Reemplazamos Observaciones por 4 columnas resumen con texto vertical para ahorrar espacio --}}
                        <th rowspan="4" class="vertical-col" title="Asistencias">
                            <span class="vertical-header">Asistencias</span>
                        </th>
                        <th rowspan="4" class="vertical-col" title="Faltas">
                            <span class="vertical-header">Faltas</span>
                        </th>
                        <th rowspan="4" class="vertical-col" title="Tardanzas">
                            <span class="vertical-header">Tardanzas</span>
                        </th>
                        <th rowspan="4" class="vertical-col" title="Justificadas">
                            <span class="vertical-header">Justificadas</span>
                        </th>
                    </tr>

                    {{-- FILA 2: Cabeceras por semana (Semana 1, Semana 2, ...) --}}
                    <tr>
                        @if ($weeksCount > 0)
                            @foreach ($validDaysPerWeek as $wIndex => $valid)
                                @php $colspan = count($valid); @endphp
                                @if ($colspan > 0)
                                    <th class="text-center" colspan="{{ $colspan }}">Semana {{ $wIndex + 1 }}
                                    </th>
                                @endif
                            @endforeach
                        @else
                            @for ($w = 1; $w <= 4; $w++)
                                <th class="text-center" colspan="5">Semana {{ $w }}</th>
                            @endfor
                        @endif
                    </tr>

                    {{-- FILA 3: Días (L, Ma, Mi, J, V) --}}
                    <tr>
                        @if ($weeksCount > 0)
                            @foreach ($validDaysPerWeek as $valid)
                                @foreach (array_keys($valid) as $d)
                                    <th class="text-center small">{{ $d }}</th>
                                @endforeach
                            @endforeach
                        @else
                            @for ($w = 1; $w <= 4; $w++)
                                @foreach (['L', 'Ma', 'Mi', 'J', 'V'] as $d)
                                    <th class="text-center small">{{ $d }}</th>
                                @endforeach
                            @endfor
                        @endif
                    </tr>

                    {{-- FILA 4: Número del día (01,02,...) --}}
                    <tr>
                        @if ($weeksCount > 0)
                            @foreach ($validDaysPerWeek as $valid)
                                @foreach ($valid as $dKey => $info)
                                    @php
                                        $date = $info['date'] ?? null;
                                        $dateStr = $date ? $date : null;
                                        $isNoClass = $dateStr && isset($normalizedSelected[$dateStr]);
                                    @endphp
                                    <th
                                        class="text-center small day-header-number {{ $isNoClass ? 'no-class-column' : '' }}">
                                        {{ $date ? \Carbon\Carbon::parse($date)->format('d') : '' }}
                                    </th>
                                @endforeach
                            @endforeach
                        @else
                            @for ($w = 1; $w <= 4; $w++)
                                @foreach (['L', 'Ma', 'Mi', 'J', 'V'] as $d)
                                    <th class="text-center small day-header-number"></th>
                                @endforeach
                            @endfor
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php $displayTotalDays = $totalVisibleDays > 0 ? $totalVisibleDays : 4*5; @endphp

                    @forelse($estudiantes as $index => $est)
                        <tr>
                            <td class="sticky-col index">{{ $index + 1 }}</td>
                            <td class="sticky-col name student-name">{{ $est['nombre'] }}</td>

                            @if ($weeksCount > 0)
                                @foreach ($validDaysPerWeek as $valid)
                                    @foreach ($valid as $dKey => $info)
                                        @php
                                            $date = $info['date'] ?? null;
                                            $dateStr = $date ? $date : null;
                                            $isNoClass = $dateStr && isset($normalizedSelected[$dateStr]);
                                        @endphp
                                        <td class="{{ $isNoClass ? 'no-class-column' : '' }}"
                                            title="{{ $date ?? '' }}"></td>
                                    @endforeach
                                @endforeach
                            @else
                                @for ($i = 0; $i < $displayTotalDays; $i++)
                                    <td></td>
                                @endfor
                            @endif

                            {{-- nuevas 4 columnas resumen por alumno --}}
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + $displayTotalDays }}" class="text-center text-muted">No hay
                                estudiantes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Script: fullscreen toggle (existente) -->
    <script>
        (function() {
            const btn = document.getElementById('toggle-fullscreen');
            if (!btn) return;
            btn.addEventListener('click', function() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(() => {
                        alert('No se pudo activar pantalla completa');
                    });
                } else {
                    document.exitFullscreen().catch(() => {});
                }
            });
        })();
    </script>

    {{-- Advertencia antes de descargar .docx (usa SweetAlert2) --}}
    <script>
        (function() {
            function ensureSwal(cb) {
                if (typeof Swal !== 'undefined') return cb();
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                s.onload = cb;
                document.head.appendChild(s);
            }

            const form = document.getElementById('download-word-form');
            if (!form) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                ensureSwal(function() {
                    Swal.fire({
                        title: 'Advertencia',
                        html: 'El archivo .docx puede no conservar exactamente el mismo diseño visual que la vista (colores, tipografías o posiciones). ¿Deseas continuar de todas formas?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, descargar',
                        cancelButtonText: 'Cancelar',
                        focusCancel: true,
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        })();
    </script>
</body>

</html>
