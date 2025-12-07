<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de asistencia - {{ $mes ?? 'Mes no definido' }} {{ $anio ?? '' }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- NUESTRO CSS NAVIDEÑO (Asegúrate que la ruta coincida con donde guardaste el CSS de arriba) -->
    <link rel="stylesheet" href="{{ asset('assets/style/asistencia/navidad.css') }}">
</head>

<body>
    @php
        function docenteGenero()
        {
            $user = auth()->user();
            if ($user && $user->persona && isset($user->persona->genero)) {
                $genero = strtolower($user->persona->genero);
                if ($genero === 'femenino') {
                    return 'Femenino';
                }
                if ($genero === 'masculino') {
                    return 'Masculino';
                }
            }
            return null;
        }
        $docenteGenero = docenteGenero();

        // Avatar logic
        $avatarUrl = null;
        if (!empty($docenteAvatar ?? null)) {
            $avatarUrl = $docenteAvatar;
        }
        if (empty($avatarUrl) && \Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if (method_exists($user, 'profile_photo_url')) {
                $avatarUrl = $user->profile_photo_url;
            } elseif (!empty($user->profile_photo_path)) {
                $avatarUrl = \Illuminate\Support\Facades\Storage::url($user->profile_photo_path);
            } elseif (method_exists($user, 'getFirstMediaUrl')) {
                $m = $user->getFirstMediaUrl('avatar');
                if (!empty($m)) {
                    $avatarUrl = $m;
                }
            }
        }
    @endphp

    <div class="document-preview">

        <!-- TOOLBAR -->
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

                <a class="btn-primary" href="#" onclick="window.print();return false;"
                    title="Descargar PDF">Descargar PDF</a>
            </div>
        </div>

        <!-- MARCO DEL DOCUMENTO -->
        <div class="document-frame">

            {{-- Encabezado --}}
            <div class="header-banner">
                <!-- Logo Izquierda -->
                <img src="{{ url('assets/img/logo_colegio.png') }}" alt="Logo colegio"
                    class="header-logo left-logo logo-big"
                    style="width: 150px; height: auto;"> <!-- Aumentar tamaño -->

                <!-- Título Central con Mascotas Navideñas -->
                <div class="header-title">
                    <div class="title-container">
                        <!-- Papá Noel dentro del contenedor del título -->
                        <img src="{{ url('assets/img/navidad/papanuel.png') }}" class="decoration-santa" alt="Santa Claus"
                            style="position: absolute; top: -20px; right: -100px; width: 120px; height: auto; z-index: 10;">
                        <!-- Mascota Navidad según género -->
                        @if ($docenteGenero === 'Femenino')
                            <div
                                style="display: flex; flex-direction: column; align-items: center; position: relative;">
                                @if ($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="Avatar docente"
                                        style="width:48px;height:48px;border-radius:50%;border:2.5px solid #fff;box-shadow:0 2px 8px #0002;margin-bottom:-100px;margin-top:16px;position:absolute;top:16px;left:48%;transform:translateX(-50%);z-index:3;">
                                @endif
                                <img src="{{ url('assets/img/navidad/chica_navidad.png') }}" alt="Chica Navidad"
                                    class="header-mascot chica-navidad d-none d-md-block"
                                    style="width:120px;height:auto;position:relative;z-index:2;">
                            </div>
                        @elseif ($docenteGenero === 'Masculino')
                            <div
                                style="display: flex; flex-direction: column; align-items: center; position: relative;">
                                @if ($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="Avatar docente"
                                        style="width:48px;height:48px;border-radius:50%;border:1.5px solid #fff;box-shadow:0 2px 8px #0002;margin-bottom:-100px;margin-top:14px;position:absolute;top:16px;left:50%;transform:translateX(-40%);z-index:3;">
                                @endif
                                <img src="{{ url('assets/img/navidad/chico_navidad.png') }}" alt="Chico Navidad"
                                    class="header-mascot chico-navidad d-none d-md-block"
                                    style="width:120px;height:auto;position:relative;z-index:2;">
                            </div>
                        @endif
                        <div>
                            <h1>Registro de Asistencia</h1>
                            <p>Institución Educativa Ann Goulden</p>
                        </div>
                        <!-- Mascota opuesta (opcional, puedes quitar si solo quieres mostrar una) -->
                        {{-- Si quieres mostrar ambos, deja el código original aquí --}}
                    </div>
                </div>

                <!-- Logo Derecha -->
                <img src="{{ url('assets/img/logo_ministerio.png') }}" alt="Logo MINEDU"
                    class="header-logo right-logo"
                    style="width: 150px; height: auto;"> <!-- Aumentar tamaño -->
            </div>

            {{-- Información Docente: CAJA DE REGALO --}}
            <div class="gift-box-container">
                <div class="gift-box">
                    <div class="gift-bow"></div> <!-- El lazo decorativo -->
                    <div class="gift-content" style="align-items: center;">
                        <div class="info-item">
                            <div class="info-label">Docente Responsable</div>
                            <div class="info-value">{{ $docenteNombre ?? '—' }}</div>
                        </div>
                        <div style="font-size:20px; color:var(--xmas-gold);">★</div>
                        <div class="info-item">
                            <div class="info-label">Grado y Sección</div>
                            <div class="info-value">{{ $gradoSeccion ?? '—' }}</div>
                        </div>
                        <!-- Leyenda (Esferas) al lado del regalo -->
                        <div class="legend-card legend-gift-side" style="margin-left:30px;">
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
                </div>
            </div>

            {{-- TABLA DE ASISTENCIA --}}
            <div class="table-responsive">

                <!-- IMÁGENES DENTRO DE LA TABLA (Marcas de agua transparentes) -->
                <img src="{{ url('assets/img/navidad/galletas_navidad.png') }}" class="watermark-overlay img-cookies"
                    alt="Galletas">
                <img src="{{ url('assets/img/navidad/muñeco_nieve.png') }}" class="watermark-overlay img-snowman"
                    alt="Muñeco Nieve">
                <img src="{{ url('assets/img/navidad/arbol_navideño.png') }}"
                    class="watermark-overlay img-arbol-navideno" alt="Árbol Navideño">

                {{-- CÓDIGO PHP/BLADE ORIGINAL DE LA TABLA (Sin cambios en lógica) --}}
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
                            <th rowspan="4" class="sticky-col name" style="min-width:250px;">Apellidos y nombres
                            </th>

                            <th colspan="{{ $totalVisibleDays }}"
                                style="font-size:18px; padding:10px; font-family:'Mountains of Christmas', cursive;">
                                {{ strtoupper(($mes ?? now()->translatedFormat('F')) . ' - ' . ($anio ?? date('Y'))) }}
                                🎄
                            </th>

                            {{-- Resumen Vertical --}}
                            <th rowspan="4" class="vertical-col"><span class="vertical-header">Asistencias</span>
                            </th>
                            <th rowspan="4" class="vertical-col"><span class="vertical-header">Faltas</span></th>
                            <th rowspan="4" class="vertical-col"><span class="vertical-header">Tardanzas</span>
                            </th>
                            <th rowspan="4" class="vertical-col"><span class="vertical-header">Justificadas</span>
                            </th>
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
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
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
        </div>
    </div>

    <!-- Scripts de Funcionalidad -->
    <script>
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
                            text: 'El diseño navideño podría no mostrarse igual en Word.',
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonColor: '#BB2528',
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
