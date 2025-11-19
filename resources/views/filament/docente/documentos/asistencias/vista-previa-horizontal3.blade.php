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

    <style>
        :root {
            /* Paleta neutra / beige suave */
            --bg: #f6f1eb;
            --paper: #fffdf8;
            --muted: #6b5b4c;
            --accent-dark: #8b5e3c;
            /* marrón suave */
            --accent-warm: #cba45f;
            /* dorado claro */
            --soft: #fbf8f5;
            --shadow: 0 10px 28px rgba(139, 94, 60, 0.06);
            --radius: 14px;
            --line-dark: rgba(139, 94, 60, 0.14);
            /* línea oscura */
        }

        html,
        body {
            height: 100%;
            margin: 0;
            background: linear-gradient(180deg, var(--soft), #fbfbf8);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Arial;
            color: var(--accent-dark);
        }

        .document-preview {
            width: 100%;
            padding: 26px;
            box-sizing: border-box;
            min-height: 100vh;
        }

        /* Marco alrededor de la vista previa */
        .document-frame {
            background: var(--paper);
            border: 2px solid var(--accent-dark);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 18px 40px rgba(4, 47, 38, 0.08);
            /* pequeño separador visual con el fondo de la página */
            margin: 8px 0;
        }

        /* Cabecera renovada: fondo claro, título serif */
        .header-banner {
            display: flex;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(180deg, #ffffff, #f3fbf8);
            padding: 18px 22px;
            border-radius: 16px;
            border: 1px solid rgba(6, 78, 59, 0.06);
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        /* Línea divisoria oscura debajo del header */
        .header-banner {
            border-bottom: 3px solid var(--line-dark);
        }

        .header-title h1 {
            margin: 0;
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: var(--accent-dark);
            font-weight: 900;
        }

        .header-title p {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: var(--muted);
            font-weight: 600;
        }

        .header-logo {
            width: 88px;
            height: auto;
            object-fit: contain;
        }

        /* Toolbar simplificado */
        .preview-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            gap: 12px;
        }

        .btn-primary {
            background: var(--accent-dark);
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 10px;
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid rgba(4, 47, 38, 0.06);
            padding: 8px 10px;
            border-radius: 10px;
            color: var(--accent-dark);
        }

        /* Info card: avatar circular y leyenda tipo chips */
        .info-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            padding: 14px;
            border-radius: var(--radius);
            background: linear-gradient(180deg, #fff, #fbfffb);
            box-shadow: var(--shadow);
            border: 1px solid rgba(6, 78, 59, 0.04);
            margin-bottom: 18px;
        }

        .info-left {
            display: flex;
            gap: 14px;
            align-items: center;
        }

        .teacher-avatar {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #fff;
            font-size: 20px;
            background: linear-gradient(135deg, var(--accent-dark), var(--accent-warm));
            box-shadow: 0 8px 18px rgba(6, 78, 59, 0.08);
        }

        .teacher-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .info-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-meta .label {
            font-size: 12px;
            color: var(--muted);
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .info-meta .value {
            font-size: 15px;
            color: var(--accent-dark);
            font-weight: 800;
        }

        /* Nueva leyenda: chips verticales con icono y texto debajo */
        .legend-card {
            display: flex;
            gap: 14px;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .legend-chip {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            background: transparent;
            padding: 6px 8px;
            min-width: 92px;
        }

        .legend-dot {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            box-shadow: 0 8px 16px rgba(2, 6, 23, 0.06);
        }

        .legend-dot.att {
            background: linear-gradient(135deg, #16a34a, #065f46);
        }

        /* asistió - verde */
        .legend-dot.abs {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
        }

        /* falta - rojo */
        .legend-dot.exc {
            background: linear-gradient(135deg, #2563eb, #1e40af);
        }

        /* justificado - azul */
        .legend-dot.lat {
            background: linear-gradient(135deg, #f59e0b, #b45309);
        }

        /* tardanza - ámbar */
        .legend-label {
            font-size: 12px;
            color: var(--muted);
            font-weight: 700;
            text-align: center;
        }

        /* Tabla: estética más suave, pero con líneas oscuras visibles y marco interno */
        .table {
            min-width: 1200px;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            background: transparent;
            border: 2px solid #000;
            border-radius: 10px;
            overflow: hidden;
        }

        .table th,
        .table td {
            border-bottom: 1.5px solid #000;
            padding: 10px 8px;
            vertical-align: middle;
        }

        thead th {
            background: linear-gradient(180deg, #f8f4ef, #fbf8f5);
            color: var(--accent-dark);
            font-weight: 800;
            font-size: 13px;
            text-align: center;
            border-bottom: 2px solid #000;
        }

        tbody tr:nth-child(even) td {
            background: #fcfffb;
        }

        /* quitar fondo fijo en celdas; ahora cada fila maneja su propio fondo */
        tbody td {
            background: transparent;
            color: #2b2b2b;
        }

        /* Alternancia fuerte de color por fila (mejor para tomar asistencia e imprimir) */
        tbody tr:nth-child(odd) td {
            background: #fffdf8 !important;
            /* tono beige claro */
            color: #2b2b2b !important;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        tbody tr:nth-child(even) td {
            background: #f3efe6 !important;
            /* tono beige alterno */
            color: #2b2b2b !important;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        /* asegurar que "no-class-column" preserve su estilo incluso en filas coloreadas */
        td.no-class-column,
        td.no-class-column * {
            background: linear-gradient(180deg, #fff7f2, #fff9f4) !important;
            color: #7a2b00 !important;
        }

        /* DIAS NO CLASE: pintar el fondo completo de la columna (encabezados + celdas) */
        .table th.no-class-column,
        .table td.no-class-column,
        th.no-class-column,
        td.no-class-column {
            background: linear-gradient(180deg,#fff8e6,#fff1d9) !important;
            background-color: #fff6e8 !important;
            color: #7a2b00 !important;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        /* Asegurar que los elementos internos hereden colores */
        .table th.no-class-column *,
        .table td.no-class-column * {
            background: transparent !important;
            color: inherit !important;
        }

        /* Si una celda sticky también es no-class, aplicar el mismo fondo */
        th.sticky-col.no-class-column,
        td.sticky-col.no-class-column {
            background: linear-gradient(180deg,#fff8e6,#fff1d9) !important;
            background-color: #fff6e8 !important;
            color: #7a2b00 !important;
        }

        @media print {
            .table th.no-class-column,
            .table td.no-class-column,
            th.no-class-column,
            td.no-class-column {
                background: linear-gradient(180deg,#fff8e6,#fff1d9) !important;
                background-color: #fff6e8 !important;
                color: #7a2b00 !important;
                -webkit-print-color-adjust: exact;
            }
        }

        /* añadir líneas verticales oscuras entre columnas importantes */
        .table td:not(:last-child),
        .table th:not(:last-child) {
            border-right: 1.5px solid #000;
        }

        th.sticky-col,
        td.sticky-col {
            position: sticky;
            left: 0;
            z-index: 7;
            background: var(--paper);
            box-shadow: none;
            border-right: 2px solid #000;
        }

        /* Columnas "no clase": marcar con borde naranja suave */
        td.no-class-column {
            border-left: 3px solid rgba(255, 122, 24, 0.95) !important;
        }

        .day-header-number {
            font-weight: 800;
            color: var(--accent-dark);
            font-size: 13px;
            padding: 6px;
        }

        @media (max-width:900px) {
            .table {
                min-width: 900px;
            }

            .teacher-avatar {
                width: 56px;
                height: 56px;
            }

            .header-title h1 {
                font-size: 20px;
            }

            .info-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }

        /* imprimir: ocultar botones y elementos interactivos, simplificar marco/estilos */
        @media print {

            /* ocultar toolbar y controles */
            .no-print,
            .preview-toolbar,
            .preview-actions,
            #download-word-form,
            .btn-ghost,
            .btn-primary,
            #toggle-fullscreen,
            a.btn-primary {
                display: none !important;
            }

            /* limpiar sombras/bordes para impresión */
            .document-frame {
                box-shadow: none !important;
                border: 1px solid #00000020 !important;
                padding: 8px !important;
            }

            .header-banner {
                box-shadow: none !important;
                border-bottom: 1px solid #00000020 !important;
            }

            .info-card {
                box-shadow: none !important;
                border: 1px solid #00000010 !important;
            }

            /* tabla: evitar cortes indeseados y ajustar colores para impresión */
            .table {
                page-break-inside: avoid;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            thead th {
                background: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
            }

            /* respetar alternancia de filas al imprimir */
            tbody tr:nth-child(odd) td {
                background: #fffdf8 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            tbody tr:nth-child(even) td {
                background: #f3efe6 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            /* mantener la columna no-clase visible */
            td.no-class-column,
            td.no-class-column * {
                background: linear-gradient(180deg, #fff7f2, #fff9f4) !important;
                color: #7a2b00 !important;
                -webkit-print-color-adjust: exact;
            }

            /* fondo blanco */
            html,
            body {
                background: #fff !important;
            }

            /* asegurar que sticky columns se impriman correctamente */
            th.sticky-col,
            td.sticky-col {
                position: static !important;
            }

            /* eliminar efectos visuales no necesarios en papel */
            * {
                box-shadow: none !important;
                text-shadow: none !important;
            }

            /* asegurar que los fondos de fila se impriman cuando el navegador lo permita */
            /* ya cubierto por nth-child(odd/even) arriba */
        }

        /* encabezados verticales compactos (texto apuntando hacia arriba) */
        th.vertical-col {
            width: 48px;
            /* ajustar al ancho deseado */
            max-width: 48px;
            padding: 4px 6px;
            vertical-align: middle;
            text-align: center;
            white-space: nowrap;
        }

        th.vertical-col .vertical-header {
            display: inline-block;
            writing-mode: vertical-rl;
            /* texto vertical */
            transform: rotate(180deg);
            /* orientar letras hacia arriba */
            white-space: nowrap;
            font-weight: 800;
            font-size: 12px;
            line-height: 1;
            padding: 2px 0;
        }

        /* asegurar impresión similar */
        @media print {
            th.vertical-col {
                width: 48px !important;
                max-width: 48px !important;
            }

            th.vertical-col .vertical-header {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
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
