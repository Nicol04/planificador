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
        .student-name { text-align: left; white-space: nowrap; }
        .criteria-cell { height: 28px; }
        .small-muted { font-size: 12px; color:#666; }
    </style>
</head>

<body class="bg-light">

    <div class="floating-buttons no-print" style="position:fixed; top:20px; right:20px; z-index:1000;">
        <a class="btn btn-secondary mb-2" href="javascript:history.back();"><i class="fas fa-arrow-left"></i> Volver</a>
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
                // asegurar 3 niveles
                if(count($niveles) < 3) {
                    $niveles = array_pad($niveles, 3, '');
                }
                $competenciaNombre = $lista->competencia_nombre ?? ($lista->competencia?->nombre ?? null);
            @endphp

            <div class="card mb-4">
                <div class="card-body">
                    <!-- Fila 1: competencia -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <strong>Competencia:</strong> {{ $competenciaNombre ?? '—' }}
                        </div>
                    </div>

                    <!-- Fila 2: título -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Título:</strong> {{ $lista->titulo ?? ($competenciaNombre ?? 'Lista de cotejo') }}
                        </div>
                    </div>

                    <!-- Fila 3: tabla dinámica -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th rowspan="3" style="width:5%;">N°</th>
                                    <th rowspan="3" style="width:35%;">Nombres y apellidos</th>

                                    @foreach($criterios as $crit)
                                        <th class="text-center" colspan="3" style="min-width:120px;">{{ $crit }}</th>
                                    @endforeach
                                </tr>

                                <tr>
                                    @foreach($criterios as $crit)
                                        <th colspan="3"></th>
                                    @endforeach
                                </tr>

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
                                    <tr>
                                        <td class="align-middle">{{ $index + 1 }}</td>
                                        <td class="student-name align-middle">{{ is_array($est) ? ($est['nombre'] ?? '') : ($est->nombre ?? '') }}</td>

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
                            <a class="btn btn-success btn-sm" href="{{ url('/listas-cotejo/'.$lista->id.'/vista-previa') }}" target="_blank">
                                <i class="fas fa-eye"></i> Ver individual
                            </a>
                            <a class="btn btn-primary btn-sm" href="{{ url('/listas-cotejo/'.$lista->id.'/previsualizar') }}" target="_blank">
                                <i class="fas fa-download"></i> Descargar .docx
                            </a>
                        @else
                            <span class="text-muted small">Lista generada a partir del detalle (sin guardar)</span>
                        @endif
                    </div>

                </div>
            </div>
        @endforeach

    </div>

</body>

</html>