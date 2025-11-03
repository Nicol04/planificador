<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de cotejo - {{ $sesion->titulo ?? 'Sesión' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
        .document-preview {
            max-width: {{ ($orientacion ?? 'vertical') === 'horizontal' ? '1000px' : '800px' }};
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            min-height: 100vh;
        }
        .section-title { background:#0066cc; color:#fff; padding:10px 15px; margin:20px 0 10px; font-weight:700; border-radius:5px; }
        .table th, .table td { vertical-align: middle; text-align: center; }
        .student-name { text-align: left; padding-left: 10px; white-space: nowrap; }
        .criteria-cell { height: 28px; }
        .small-muted { font-size: 12px; color:#666; }
        .meta-table { width:100%; margin-bottom:12px; }
        .meta-table td { padding:6px 8px; border:1px solid #e9ecef; background:#f8fafc; font-weight:600; }
        .criteria-title { font-weight:700; }
        .cotejo-table th, .cotejo-table td { border:1px solid #dee2e6; padding:8px; text-align:center; vertical-align:middle; }
    </style>
</head>

<body class="bg-light">

    <div class="floating-buttons no-print" style="position:fixed; top:20px; right:20px; z-index:1000;">
        <!-- Descargar Word (primera lista) -->
        @php $firstListaId = $listas->first()?->id ?? null; @endphp
        <button class="btn btn-primary mb-2" type="button" onclick="descargarDocumento();">
            <i class="fas fa-download"></i> Descargar Word
        </button>
        <!-- Botón para imprimir la vista actual -->
        <button class="btn btn-success mb-2" type="button" onclick="imprimirLista();">
            <i class="fas fa-print"></i> Imprimir
        </button>
         <!-- Volver / cerrar -->
         <button class="btn btn-secondary mb-2" type="button" onclick="cerrarVentana();">
             <i class="fas fa-arrow-left"></i> Volver
         </button>
     </div>

    <div class="document-preview p-4">
        <!-- Header -->
        <div class="text-center mb-3">
            <h4 class="text-primary">LISTA DE COTEJO</h4>
            <h5 class="fw-bold">{{ $sesion->titulo ?? 'Sin título' }}</h5>
            <p class="small-muted mb-0">
                Docente: {{ $sesion->docente?->persona ? trim(($sesion->docente->persona->nombre ?? '') . ' ' . ($sesion->docente->persona->apellido ?? '')) : '—' }}
                 | Grado/Sección: {{ $sesion->aulaCurso?->aula?->grado_seccion ?? '—' }}
                 | Área: {{ $sesion->aulaCurso?->curso?->curso ?? '—' }}
            </p>
        </div>

        @foreach($listas as $lista)
            @php
                $criterios = $lista->criterios_array ?? [];
                $niveles = $lista->niveles_array ?? ['Bajo','Medio','Alto'];
                if(count($niveles) < 3) {
                    $niveles = array_pad($niveles, 3, '');
                }

                // obtener nombre de la competencia: preferir relación, si no buscar en propositos_aprendizaje del detalle
                $competenciaNombre = $lista->competencia_nombre ?? ($lista->competencia?->nombre ?? null);

                if (!$competenciaNombre) {
                    $propositos = $sesion->detalle->propositos_aprendizaje ?? [];
                    foreach ($propositos as $prop) {
                        $propCompetenciaId = $prop['competencia_id'] ?? null;
                        if (!$propCompetenciaId) {
                            continue;
                        }
                        // si la lista tiene competencia_id, matchear; si no, tomar la primera que exista
                        if (!empty($lista->competencia_id) && ((int)$propCompetenciaId === (int)$lista->competencia_id)) {
                            $competenciaNombre = \App\Models\Competencia::find($propCompetenciaId)?->nombre;
                            break;
                        }
                        if (empty($lista->competencia_id)) {
                            $competenciaNombre = \App\Models\Competencia::find($propCompetenciaId)?->nombre;
                            break;
                        }
                    }
                }
            @endphp

            <!-- Metadatos (competencia / título) como filas separadas -->
            <table class="meta-table">
                <tr>
                    <td style="width:100%;">Competencia: {{ $competenciaNombre ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Título: {{ $lista->titulo ?? ($competenciaNombre ?? 'Lista de cotejo') }}</td>
                </tr>
            </table>

            <!-- Contador de estudiantes -->
            <p class="small-muted mb-2">Total estudiantes: {{ $estudiantes->count() }}</p>

            <!-- Tabla de cotejo -->
            <div class="table-responsive mb-4">
                <table class="table cotejo-table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        @php
                            $colspan = max(0, count($criterios) * count($niveles));
                        @endphp

                        {{-- Ahora N° y Apellidos/Nombres ocupan 3 filas (rowspan="3") --}}
                        <tr>
                            <th rowspan="3" style="width:5%;">N°</th>
                            <th rowspan="3" style="width:45%;">Apellidos y nombres de los estudiantes</th>
                            <th class="text-center" colspan="{{ $colspan }}">CRITERIOS</th>
                        </tr>

                        {{-- Fila con los títulos de cada criterio (cada criterio abarca los niveles) --}}
                        <tr>
                            @foreach($criterios as $crit)
                                <th class="text-center" colspan="{{ count($niveles) ?: 1 }}" style="min-width:120px;">{{ $crit }}</th>
                            @endforeach
                        </tr>

                        {{-- Fila con los niveles por criterio --}}
                        <tr>
                            @foreach($criterios as $crit)
                                @foreach($niveles as $nivel)
                                    <th class="text-center small">{{ $nivel }}</th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($estudiantes as $index => $est)
                            @php
                                // Soportar distintos formatos: array con 'nombre' o modelo con 'nombre'/'nombres' y 'apellidos'
                                $nombreEst = is_array($est)
                                    ? ($est['nombre'] ?? trim((($est['nombres'] ?? '') . ' ' . ($est['apellidos'] ?? ''))))
                                    : ($est->nombre ?? trim((($est->nombres ?? '') . ' ' . ($est->apellidos ?? ''))));
                            @endphp
                            <tr>
                                <td class="align-middle">{{ $index + 1 }}</td>
                                <td class="student-name align-middle">{{ $nombreEst }}</td>

                                @foreach($criterios as $crit)
                                    @foreach($niveles as $nivel)
                                        <td class="criteria-cell"></td>
                                    @endforeach
                                @endforeach
                            </tr>
                        @endforeach

                        @if(count($estudiantes) === 0)
                            <tr>
                                <td>1</td>
                                <td class="student-name">&nbsp;</td>
                                @foreach($criterios as $crit)
                                    @foreach($niveles as $nivel)
                                        <td class="criteria-cell"></td>
                                    @endforeach
                                @endforeach
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Acciones por lista -->
            <div class="mt-2 d-flex gap-2">
                @if(!empty($lista->id))
                    <a class="btn btn-primary btn-sm" href="{{ url('/listas-cotejo/'.$lista->id.'/previsualizar') }}" target="_blank">
                        <i class="fas fa-download"></i> Descargar .docx
                    </a>
                @else
                    <span class="text-muted small">Lista generada a partir del detalle (sin guardar)</span>
                @endif
            </div>

        @endforeach

    </div>

    <!-- Script: cerrar ventana con ESC o clic fuera, fallback a historial o listado de sesiones -->
    <script>
        function descargarDocumento() {
            const orient = '{{ $orientacion ?? 'vertical' }}';
            const firstId = '{{ $firstListaId ?? '' }}';
            if (!firstId) {
                alert('No hay listas disponibles para descargar.');
                return;
            }
            window.location.href = `/listas-cotejo/${firstId}/previsualizar?orientacion=${orient}`;
        }

         // Imprimir la vista de Lista de Cotejo (imprime la página actual)
         function imprimirLista() {
             try {
                 window.print();
             } catch (e) {
                 console.error('Error al intentar imprimir:', e);
             }
         }

        function cerrarVentana() {
            // Si es popup abierto por window.open()
            try {
                if (window.opener && !window.opener.closed) {
                    window.close();
                    return;
                }
            } catch (e) {
                // ignore
            }

            // Si hay historial, volver atrás
            if (window.history.length > 1) {
                window.history.back();
                return;
            }

            // Fallback: ir a la lista de sesiones
            window.location.href = '/docente/sesions';
        }

        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' || event.key === 'Esc') {
                cerrarVentana();
            }
        });

        // Cerrar si se hace clic fuera del contenedor .document-preview
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.document-preview')) {
                cerrarVentana();
            }
        });

        // Evitar que clicks dentro del documento disparen el cierre si se usan elementos interactivos
        document.querySelectorAll('.document-preview').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>

    @if(request()->get('autoPrint'))
    <script>
        // Esperar a que la vista cargue y disparar impresión automática
        window.addEventListener('load', function() {
            setTimeout(function() {
                try { window.print(); } catch (e) {}
            }, 300);
        });
        // Intentar cerrar la ventana después de imprimir (si es popup)
        window.onafterprint = function() {
            try { window.close(); } catch(e) {}
        };
    </script>
    @endif
 </body>

</html>