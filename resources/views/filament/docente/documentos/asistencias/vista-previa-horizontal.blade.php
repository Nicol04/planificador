<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de asistencia - {{ $mes ?? 'Mes no definido' }} {{ $anio ?? '' }}</title>

    <!-- Fuentes bonitas -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .header-banner{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:linear-gradient(180deg,#eaf3ff,#f8fbff);
    padding:16px 20px;
    border-radius:16px;
    margin-bottom:22px;
    border:1px solid rgba(15,23,42,0.05);
    box-shadow:0 10px 26px rgba(2,6,23,0.04);
}

.header-logo{
    width:95px;
    height:auto;
    object-fit:contain;
}

.header-title{
    text-align:center;
}

.header-title h1{
    margin:0;
    font-size:32px;
    font-weight:700;
    color:#2563eb;
    text-shadow:0 2px 6px rgba(37,99,235,0.18);
}

.header-title p{
    margin:6px 0 0 0;
    font-size:16px;
    font-weight:500;
    color:#0f172a;
}

/* RESPONSIVE */
@media (max-width:850px){
    .header-logo{
        width:70px;
    }
    .header-title h1{
        font-size:24px;
    }
}

@media (max-width:600px){
    .header-banner{
        flex-direction:column;
        gap:10px;
        padding:14px;
    }
    .header-title p{
        font-size:14px;
    }
}
        :root{
            --bg:#f3f6fb;
            --card:#ffffff;
            --muted:#64748b;
            --accent:#2563eb;
            --accent-2:#06b6d4;
            --success:#10b981;
            --danger:#ef4444;
            --shadow: 0 12px 30px rgba(2,6,23,0.06);
            --radius:12px;
            --glass: rgba(255,255,255,0.6);
        }
        html, body { height:100%; margin:0; background: linear-gradient(180deg,#f6f9ff 0%, #eef5ff 100%); font-family: 'Poppins', Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial; color:#0f172a; }

        .document-preview { width:100%; padding:28px; box-sizing:border-box; min-height:100vh; }

        /* Toolbar (sin cambios funcionales, más estética) */
        .preview-toolbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; }
        .preview-title { font-size:1.35rem; font-weight:700; color:var(--accent); margin:0; letter-spacing:0.2px; }
        .btn-primary { background:var(--accent); border:none; padding:8px 14px; border-radius:10px; color:#fff; box-shadow: 0 8px 22px rgba(37,99,235,0.12); }
        .btn-ghost { background:transparent; border:1px solid rgba(15,23,42,0.06); padding:8px 12px; border-radius:10px; color:#0f172a; }

        /* Info card / leyenda (nuevo diseño) */
        .info-card { display:flex; justify-content:space-between; align-items:center; background:linear-gradient(180deg, rgba(255,255,255,0.85), rgba(255,255,255,0.95)); padding:14px; border-radius:var(--radius); box-shadow:var(--shadow); gap:18px; margin-bottom:18px; border: 1px solid rgba(15,23,42,0.04); }
        .info-left { display:flex; gap:18px; align-items:center; }
        .teacher-avatar {
            width:64px; height:64px; border-radius:14px; display:flex; align-items:center; justify-content:center;
            background: linear-gradient(135deg,var(--accent-2),var(--accent)); color:white; font-weight:700; font-size:18px;
            box-shadow: 0 10px 24px rgba(6,182,212,0.12);
        }
        /* imagen dentro del avatar */
        .teacher-avatar img { width:100%; height:100%; object-fit:cover; border-radius:inherit; display:block; }
        .info-meta { display:flex; flex-direction:column; gap:6px; }
        .info-meta .label { font-size:12px; color:var(--muted); }
        .info-meta .value { font-size:15px; font-weight:700; color:#0f172a; }

        /* Leyenda visual con tarjetas y iconos SVG */
        .legend-card { display:flex; gap:12px; align-items:center; }
        .legend-item {
            display:flex; gap:12px; align-items:center; background: linear-gradient(180deg,#fff, #fbfdff);
            padding:10px 12px; border-radius:10px; border:1px solid rgba(15,23,42,0.04); box-shadow: 0 8px 22px rgba(2,6,23,0.04);
            min-width:150px;
        }
        .legend-icon {
            width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800;
            box-shadow: 0 8px 20px rgba(2,6,23,0.06);
        }
        .legend-icon.a { background: linear-gradient(90deg,#06b6d4,#0ea5a4); }
        .legend-icon.f { background: linear-gradient(90deg,#fb7185,#ef4444); }
        .legend-icon.j { background: linear-gradient(90deg,#60a5fa,#2563eb); }
        .legend-text { display:flex; flex-direction:column; }
        .legend-text .title { font-weight:700; font-size:13px; color:#0f172a; }
        .legend-text .desc { font-size:11px; color:var(--muted); margin-top:2px; }

        /* Tabla: líneas oscuras y más color */
        .table {
            min-width:1200px;
            width:100%;
            border-collapse: collapse; /* líneas continuas */
            background:transparent;
            border-radius:10px;
            overflow:hidden;
        }
        /* bordes oscuros en toda la tabla */
        .table, .table th, .table td {
            border: 1px solid #0f172a; /* línea oscura */
        }
        thead th {
            background: linear-gradient(180deg,#e6f0ff,#eef7ff);
            padding:12px 10px;
            font-weight:800;
            text-align:center;
            color:#071030;
            border-bottom:2px solid #0b1220;
        }
        tbody td {
            padding:12px 10px;
            color:#0f172a;
            background: #ffffff;
        }
        tbody tr:nth-child(even) td { background: #fbfdff; } /* filas alternadas suaves */
        tbody tr:hover td { background: #f3f9ff; }

        /* sticky columns mantienen borde oscuro */
        th.sticky-col, td.sticky-col { position:sticky; left:0; z-index:7; background:#fff; border-right:1px solid #0f172a; }

        /* Dias "no clase": amarillo por defecto; puedes cambiar a rojo/verde si necesitas otros estados */
        td.no-class-column, td.no-class-column * {
            background: linear-gradient(180deg,#fff8dc,#fff3b0) !important;
            color: #4b2e00 !important;
        }
        /* enfatizar borde izquierdo para visual */
        td.no-class-column { border-left: 3px solid #f59e0b !important; }

        .day-header-number { font-size:13px; font-weight:800; color:#071030; padding:6px; }

        /* Leyenda: actualizar para mostrar el color amarillo */
        .legend-item { display:flex; gap:12px; align-items:center; }
        .legend-dot.legend-no-class { width:14px;height:14px;border-radius:50%; background:linear-gradient(180deg,#fff8dc,#fff3b0); border:1px solid #f59e0b; }

        /* responsive tweaks */
        @media (max-width:900px){ .table { min-width:900px; } .info-card{ flex-direction:column; align-items:flex-start; } .teacher-avatar{ width:56px; height:56px; } }

        /* imprimir: ocultar botones y elementos interactivos, simplificar apariencia */
        @media print{
            .no-print,
            .preview-toolbar,
            .preview-actions,
            #download-word-form,
            .btn-ghost,
            .btn-primary,
            #toggle-fullscreen,
            a.btn-primary { display: none !important; }

            /* limpiar bordes/sombras para impresión */
            .document-preview { box-shadow: none !important; border: none !important; background: #fff !important; }
            .header-banner { box-shadow: none !important; border-bottom: 1px solid rgba(0,0,0,0.08) !important; }
            .info-card { box-shadow: none !important; border: 1px solid rgba(0,0,0,0.06) !important; }

            .table { page-break-inside: avoid; -webkit-print-color-adjust: exact; color-adjust: exact; }
            thead th { background: #f2f2f2 !important; -webkit-print-color-adjust: exact; }
            tbody td { background: #fff !important; }

            html, body { background: #fff !important; }

            th.sticky-col, td.sticky-col { position: static !important; }

            * { box-shadow: none !important; text-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-light">

<div class="document-preview">
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

    <img src="{{ url('assets/img/logo_ministerio.png') }}" alt="Logo MINEDU" style="width: 140px; height: 60px; object-fit: contain;" class="header-logo right-logo">
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

        <div class="legend-card" role="list" aria-label="Leyenda de asistencia" title="Leyenda">
            <div class="legend-item" role="listitem" title="Asistió">
                <div class="legend-icon a" aria-hidden="true">
                    <!-- SVG A -->
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 12l4 4L20 4" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="legend-text">
                    <div class="title">Asistió</div>
                    <div class="desc">Marca de asistencia</div>
                </div>
            </div>

            <div class="legend-item" role="listitem" title="Falta">
                <div class="legend-icon f" aria-hidden="true">
                    <!-- SVG F -->
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 5v6" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 19h8" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="legend-text">
                    <div class="title">Falta</div>
                    <div class="desc">Inasistencia</div>
                </div>
            </div>

            <div class="legend-item" role="listitem" title="Justificado">
                <div class="legend-icon j" aria-hidden="true">
                    <!-- SVG J -->
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 8v8" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 12h8" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="legend-text">
                    <div class="title">Justificado</div>
                    <div class="desc">Inasistencia justificada</div>
                </div>
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

<!-- Script: fullscreen toggle y advertencia antes de descargar .docx -->
<script>
	(function(){
		// fullscreen toggle (existente)
		const btnFs = document.getElementById('toggle-fullscreen');
		if (btnFs) {
			btnFs.addEventListener('click', function(){
				if (!document.fullscreenElement) {
					document.documentElement.requestFullscreen().catch(()=>{ alert('No se pudo activar pantalla completa'); });
				} else {
					document.exitFullscreen().catch(()=>{});
				}
			});
		}

		// Helper para cargar SweetAlert2 si no está presente
		function ensureSwal(cb){
			if (typeof Swal !== 'undefined') return cb();
			const s = document.createElement('script');
			s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
			s.onload = cb;
			document.head.appendChild(s);
		}

		// Interceptar envío del form de descarga y mostrar advertencia
		const form = document.getElementById('download-word-form');
		if (form) {
			form.addEventListener('submit', function(e){
				e.preventDefault();
				ensureSwal(function(){
					Swal.fire({
						title: 'Advertencia',
						html: 'El archivo .docx puede no conservar exactamente el mismo diseño visual que la vista (colores, tipografías o posiciones). ¿Deseas continuar de todas formas?',
						icon: 'warning',
						showCancelButton: true,
						confirmButtonText: 'Sí, descargar',
						cancelButtonText: 'Cancelar',
						focusCancel: true,
					}).then(function(result){
						if (result.isConfirmed) {
							form.submit();
						}
					});
				});
			});
		}
	})();
</script>
</body>
</html>
