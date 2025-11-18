<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de asistencia - {{ $mes ?? 'Mes no definido' }} {{ $anio ?? '' }}</title>

    <!-- Nuevas fuentes: serif para títulos + sans para UI -->
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&family=Nunito:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root{
            --bg:#f7faf7;
            --paper:#ffffff;
            --muted:#6b7280;
            --accent-dark:#064e3b; /* verde oscuro */
            --accent-warm:#ff7a18; /* naranja */
            --soft:#eef6f2;
            --shadow: 0 10px 28px rgba(6,78,59,0.06);
            --radius:14px;
            --line-dark: rgba(6,78,59,0.18); /* línea oscura para separar */
        }

        html,body{height:100%;margin:0;background:linear-gradient(180deg,var(--soft),#fbfcfb);font-family:'Nunito',system-ui,-apple-system,Segoe UI,Roboto,Arial;color:#042f26;}

        .document-preview{width:100%;padding:26px;box-sizing:border-box;min-height:100vh;}

        /* Marco alrededor de la vista previa */
        .document-frame{
            background: var(--paper);
            border: 2px solid var(--accent-dark);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 18px 40px rgba(4,47,38,0.08);
            /* pequeño separador visual con el fondo de la página */
            margin: 8px 0;
        }

        /* Cabecera renovada: fondo claro, título serif */
        .header-banner{
            display:flex;
            gap:16px;
            align-items:center;
            justify-content:space-between;
            background:linear-gradient(180deg,#ffffff,#f3fbf8);
            padding:18px 22px;
            border-radius:16px;
            border:1px solid rgba(6,78,59,0.06);
            box-shadow:var(--shadow);
            margin-bottom:20px;
        }

        /* Línea divisoria oscura debajo del header */
        .header-banner { border-bottom: 3px solid var(--line-dark); }

        .header-title h1{
            margin:0;
            font-family:'Merriweather', serif;
            font-size:28px;
            color:var(--accent-dark);
            font-weight:900;
        }
        .header-title p{
            margin:4px 0 0 0;
            font-size:13px;
            color:var(--muted);
            font-weight:600;
        }
        .header-logo{ width:88px; height:auto; object-fit:contain; }

        /* Toolbar simplificado */
        .preview-toolbar{ display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; gap:12px; }
        .btn-primary{ background:var(--accent-dark); color:#fff; border:none; padding:8px 12px; border-radius:10px; }
        .btn-ghost{ background:transparent; border:1px solid rgba(4,47,38,0.06); padding:8px 10px; border-radius:10px; color:var(--accent-dark); }

        /* Info card: avatar circular y leyenda tipo chips */
        .info-card{ display:flex; justify-content:space-between; align-items:center; gap:18px; padding:14px; border-radius:var(--radius); background:linear-gradient(180deg,#fff,#fbfffb); box-shadow:var(--shadow); border:1px solid rgba(6,78,59,0.04); margin-bottom:18px; }
        .info-left{ display:flex; gap:14px; align-items:center; }
        .teacher-avatar{ width:68px; height:68px; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center; font-weight:800; color:#fff; font-size:20px; background:linear-gradient(135deg,var(--accent-dark),var(--accent-warm)); box-shadow: 0 8px 18px rgba(6,78,59,0.08); }
        .teacher-avatar img{ width:100%; height:100%; object-fit:cover; display:block; }

        .info-meta{ display:flex; flex-direction:column; gap:4px; }
        .info-meta .label{ font-size:12px; color:var(--muted); font-weight:700; letter-spacing:0.2px; }
        .info-meta .value{ font-size:15px; color:var(--accent-dark); font-weight:800; }

        /* Nueva leyenda: chips verticales con icono y texto debajo */
        .legend-card{ display:flex; gap:14px; align-items:center; justify-content:flex-end; flex-wrap:wrap; }
        .legend-chip{
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:8px;
            background:transparent;
            padding:6px 8px;
            min-width:92px;
        }
        .legend-dot{
            width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800;
            box-shadow: 0 8px 16px rgba(2,6,23,0.06);
        }
        .legend-dot.att { background: linear-gradient(135deg,#10b981,#047857); } /* asistió */
        .legend-dot.abs { background: linear-gradient(135deg,#f97316,#ff4d4f); } /* falta */
        .legend-dot.exc { background: linear-gradient(135deg,#60a5fa,#2563eb); } /* justificado */
        .legend-label{ font-size:12px; color:var(--muted); font-weight:700; text-align:center; }

        /* Tabla: estética más suave, pero con líneas oscuras visibles y marco interno */
        .table{ min-width:1200px; border-collapse:separate; border-spacing:0; width:100%; background:transparent; border: 2px solid var(--line-dark); border-radius:10px; overflow:hidden; }
        .table th, .table td{ border-bottom:1px solid rgba(4,47,38,0.08); padding:10px 8px; vertical-align:middle; }
        thead th{ background:linear-gradient(180deg,#f0fff7,#f7fbfb); color:var(--accent-dark); font-weight:800; font-size:13px; text-align:center; border-bottom:3px solid var(--line-dark); }
        tbody tr:nth-child(even) td{ background:#fcfffb; }
        tbody td{ background:#fff; color:#02352b; }

        /* añadir líneas verticales oscuras entre columnas importantes */
        .table td:not(:last-child), .table th:not(:last-child) {
            border-right: 1px solid rgba(4,47,38,0.06);
        }

        th.sticky-col, td.sticky-col{ position:sticky; left:0; z-index:7; background:#fff; box-shadow: 3px 0 6px rgba(2,6,23,0.02); border-right:2px solid var(--line-dark); }

        /* Columnas "no clase": marcar con borde naranja suave */
        td.no-class-column, td.no-class-column *{ background:linear-gradient(180deg,#fff7f2,#fff9f4) !important; color:#7a2b00 !important; }
        td.no-class-column{ border-left:3px solid rgba(255,122,24,0.95) !important; }

        .day-header-number{ font-weight:800; color:var(--accent-dark); font-size:13px; padding:6px; }

        @media (max-width:900px){
            .table{ min-width:900px; }
            .teacher-avatar{ width:56px; height:56px; }
            .header-title h1{ font-size:20px; }
            .info-card{ flex-direction:column; align-items:flex-start; gap:12px; }
        }

        /* imprimir: ocultar botones y elementos interactivos, simplificar marco/estilos */
        @media print{
            /* ocultar toolbar y controles */
            .no-print,
            .preview-toolbar,
            .preview-actions,
            #download-word-form,
            .btn-ghost,
            .btn-primary,
            #toggle-fullscreen,
            a.btn-primary { display: none !important; }

            /* limpiar sombras/bordes para impresión */
            .document-frame { box-shadow: none !important; border: 1px solid #00000020 !important; padding: 8px !important; }
            .header-banner { box-shadow: none !important; border-bottom: 1px solid #00000020 !important; }
            .info-card { box-shadow: none !important; border: 1px solid #00000010 !important; }

            /* tabla: evitar cortes indeseados y ajustar colores para impresión */
            .table { page-break-inside: avoid; -webkit-print-color-adjust: exact; color-adjust: exact; }
            thead th { background: #f0f0f0 !important; -webkit-print-color-adjust: exact; }
            tbody td { background: #ffffff !important; }

            /* fondo blanco */
            html, body { background: #fff !important; }

            /* asegurar que sticky columns se impriman correctamente */
            th.sticky-col, td.sticky-col { position: static !important; }

            /* eliminar efectos visuales no necesarios en papel */
            * { box-shadow: none !important; text-shadow: none !important; }
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
            <form id="download-word-form"
                  method="GET"
                  action="{{ action('\\App\\Http\\Controllers\\Documents\\AsistenciaDocumentController@descargarDocx', ['id' => request()->route('id') ?? request()->input('id')]) }}"
                  target="_blank"
                  style="display:inline;margin-left:6px;">
                <input type="hidden" name="mes" value="{{ $mes ?? '' }}">
                <input type="hidden" name="anio" value="{{ $anio ?? '' }}">
                <input type="hidden" name="selectedDates" value='@json($selectedDates ?? [])'>
                <input type="hidden" name="plantilla_id" value="{{ request()->input('plantilla_id') ?? '' }}">
                <button type="submit" class="btn-primary" title="Descargar como Word (.docx)">Descargar .docx</button>
            </form>

            <a class="btn-primary" href="#" onclick="window.print();return false;" title="Descargar PDF">Descargar</a>
        </div>
    </div>

    {{-- Encabezado con logos y título --}}
<div class="header-banner">
    <img src="{{ url('assets/img/logo_colegio.png') }}" alt="Logo colegio" class="header-logo left-logo">

    <div class="header-title">
        <h1>Registro de asistencia</h1>
        <p>Institución Educativa Ann Goulden</p>
    </div>

    <img src="{{ url('assets/img/logo_ministerio.png') }}" alt="Logo MINEDU" style="width: 120px; height: 54px; object-fit: contain;" class="header-logo right-logo">
</div>

    {{-- Información del docente y leyenda en tarjeta --}} 
    <div class="info-card">
        <div class="info-left">
            <div class="teacher-avatar" aria-hidden="true">
                {{-- Mostrar imagen de avatar si existe, si no fallback a iniciales --}}
                @php
                    $avatarUrl = null;
                    // 1) si el controlador pasó explícitamente una URL/variable
                    if (!empty($docenteAvatar ?? null)) {
                        $avatarUrl = $docenteAvatar;
                    }
                    // 2) intentar usar la información del usuario autenticado
                    if (empty($avatarUrl) && \Illuminate\Support\Facades\Auth::check()) {
                        $user = \Illuminate\Support\Facades\Auth::user();
                        // Filament / Laravel Breeze / Jetstream suelen exponer profile_photo_url
                        if (method_exists($user, 'profile_photo_url')) {
                            $avatarUrl = $user->profile_photo_url;
                        } elseif (!empty($user->profile_photo_path)) {
                            $avatarUrl = \Illuminate\Support\Facades\Storage::url($user->profile_photo_path);
                        } elseif (method_exists($user, 'getFirstMediaUrl')) {
                            $m = $user->getFirstMediaUrl('avatar');
                            if (!empty($m)) $avatarUrl = $m;
                        }
                    }
                    // 3) si aún no hay avatar, calcular iniciales
                    $initials = 'D';
                    if (empty($avatarUrl) && !empty($docenteNombre) && is_string($docenteNombre)) {
                        $parts = preg_split('/\s+/', trim($docenteNombre));
                        $letters = [];
                        foreach ($parts as $p) {
                            if ($p === '') continue;
                            $letters[] = mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
                            if (count($letters) >= 2) break;
                        }
                        if (!empty($letters)) $initials = implode('', $letters);
                    }
                @endphp

                @if(!empty($avatarUrl))
                    <img src="{{ $avatarUrl }}" alt="Avatar {{ $docenteNombre ?? '' }}">
                @else
                    {{ $initials }}
                @endif
            </div>
            <div class="info-meta">
                <div class="label">Docente</div>
                <div class="value">{{ $docenteNombre ?? '—' }}</div>
                <div class="label" style="margin-top:6px;">Grado / Sección</div>
                <div class="value">{{ $gradoSeccion ?? '—' }}</div>
            </div>
        </div>

        <!-- nueva leyenda: chips circulares con etiqueta debajo -->
        <div class="legend-card" role="list" aria-label="Leyenda de asistencia" title="Leyenda">
            <div class="legend-chip" role="listitem" title="Asistió">
                <div class="legend-dot att" aria-hidden="true">
                    <!-- icono simplificado -->
                    ✓
                </div>
                <div class="legend-label">Asistió<br><small style="color:var(--muted);font-weight:600;">Presente</small></div>
            </div>

            <div class="legend-chip" role="listitem" title="Falta">
                <div class="legend-dot abs" aria-hidden="true">
                    <!-- icono simplificado -->
                    ✕
                </div>
                <div class="legend-label">Falta<br><small style="color:var(--muted);font-weight:600;">Ausente</small></div>
            </div>

            <div class="legend-chip" role="listitem" title="Justificado">
                <div class="legend-dot exc" aria-hidden="true">
                    <!-- icono simplificado -->
                    !
                </div>
                <div class="legend-label">Justificado<br><small style="color:var(--muted);font-weight:600;">Excusa válida</small></div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        @php
            $weeksCount = isset($matrix) && is_array($matrix) ? count($matrix) : 0;
            // selectedDates ya viene normalizado desde el controlador (YYYY-MM-DD),
            // construir lookup para comparaciones rápidas
            $normalizedSelected = [];
            if (!empty($selectedDates) && is_array($selectedDates)) {
                foreach ($selectedDates as $sd) {
                    if (is_string($sd) && trim($sd) !== '') {
                        $normalizedSelected[trim($sd)] = true;
                    }
                }
            }

            // calcular días válidos por semana y total de columnas a mostrar
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
                // fallback: 4 semanas, 5 días cada una
                $totalVisibleDays = 4 * 5;
            }
        @endphp

        <table class="table table-sm table-bordered w-100">
            <thead>
                {{-- FILA 1: N°, ESTUDIANTES y MES-AÑO encima de todas las columnas de días (colspan totalVisibleDays) --}}
                <tr>
                    <th rowspan="4" class="sticky-col index" style="width:4%;">N°</th>
                    <th rowspan="4" class="sticky-col name" style="width:36%;">Apellidos y nombres</th>

                    {{-- MES - AÑO ocupa todas las columnas de días visibles --}}
                    <th colspan="{{ $totalVisibleDays }}" class="text-center" style="vertical-align: middle; padding:12px 8px; font-weight:700;">
                        {{ strtoupper(($mes ?? now()->translatedFormat('F')) . ' - ' . ($anio ?? date('Y'))) }}
                    </th>

                    <th rowspan="4" style="width:10%;">Observaciones</th>
                </tr>

                {{-- FILA 2: Cabeceras por semana (Semana 1, Semana 2, ...) --}}
                <tr>
                    @if($weeksCount > 0)
                        @foreach($validDaysPerWeek as $wIndex => $valid)
                            @php $colspan = count($valid); @endphp
                            @if($colspan > 0)
                                <th class="text-center" colspan="{{ $colspan }}">Semana {{ $wIndex + 1 }}</th>
                            @endif
                        @endforeach
                    @else
                        @for($w=1;$w<=4;$w++)
                            <th class="text-center" colspan="5">Semana {{ $w }}</th>
                        @endfor
                    @endif
                </tr>

                {{-- FILA 3: Días (L, Ma, Mi, J, V) --}}
                <tr>
                    @if($weeksCount > 0)
                        @foreach($validDaysPerWeek as $valid)
                            @foreach(array_keys($valid) as $d)
                                <th class="text-center small">{{ $d }}</th>
                            @endforeach
                        @endforeach
                    @else
                        @for($w=1;$w<=4;$w++)
                            @foreach(['L','Ma','Mi','J','V'] as $d)
                                <th class="text-center small">{{ $d }}</th>
                            @endforeach
                        @endfor
                    @endif
                </tr>

                {{-- FILA 4: Número del día (01,02,...) --}}
                <tr>
                    @if($weeksCount > 0)
                        @foreach($validDaysPerWeek as $valid)
                            @foreach($valid as $dKey => $info)
                                @php
                                    $date = $info['date'] ?? null;
                                    $dateStr = $date ? $date : null;
                                    $isNoClass = $dateStr && isset($normalizedSelected[$dateStr]);
                                @endphp
                                <th class="text-center small day-header-number {{ $isNoClass ? 'no-class-column' : '' }}">
                                    {{ $date ? \Carbon\Carbon::parse($date)->format('d') : '' }}
                                </th>
                            @endforeach
                        @endforeach
                    @else
                        @for($w=1;$w<=4;$w++)
                            @foreach(['L','Ma','Mi','J','V'] as $d)
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

                        @if($weeksCount > 0)
                            @foreach($validDaysPerWeek as $valid)
                                @foreach($valid as $dKey => $info)
                                    @php
                                        $date = $info['date'] ?? null;
                                        $dateStr = $date ? $date : null; // ya en formato YYYY-MM-DD
                                        $isNoClass = $dateStr && isset($normalizedSelected[$dateStr]);
                                    @endphp
                                    {{-- celdas de estudiante vacías; colorear si está en selectedDates --}}
                                    <td class="{{ $isNoClass ? 'no-class-column' : '' }}" title="{{ $date ?? '' }}"></td>
                                @endforeach
                            @endforeach
                        @else
                            @for($i=0;$i<$displayTotalDays;$i++)
                                <td></td>
                            @endfor
                        @endif

                        <td></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 3 + $displayTotalDays }}" class="text-center text-muted">No hay estudiantes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Script: fullscreen toggle -->
<script>
    (function(){
        const btn = document.getElementById('toggle-fullscreen');
        if (!btn) return;
        btn.addEventListener('click', function(){
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(()=>{ alert('No se pudo activar pantalla completa'); });
            } else {
                document.exitFullscreen().catch(()=>{});
            }
        });
    })();
</script>
</body>
</html>
