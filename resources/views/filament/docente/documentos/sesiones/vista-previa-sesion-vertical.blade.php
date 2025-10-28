<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa - {{ $unidad->nombre }}</title>
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
            max-width: {{ $orientacion === 'horizontal' ? '1000px' : '800px' }};
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
        }

        .document-header {
            border-bottom: 3px solid #0066cc;
            padding: 20px;
            text-align: center;
        }

        .logos-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo-placeholder {
            width: 80px;
            height: 80px;
            border: 2px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            text-align: center;
            color: #666;
        }

        .document-title {
            color: #0066cc;
            font-weight: bold;
            font-size: 24px;
            margin: 10px 0;
        }

        .section-title {
            background: #0066cc;
            color: white;
            padding: 10px 15px;
            margin: 20px 0 10px 0;
            font-weight: bold;
            border-radius: 5px;
        }

        .info-table {
            border: 1px solid #ddd;
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td,
        .info-table th {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }

        .info-table th {
            background: #f8f9fa;
            font-weight: bold;
        }

        .content-text {
            text-align: justify;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .enfoques-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .enfoques-table th {
            background: #0066cc;
            color: white;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .enfoques-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
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

        .table {
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .table th {
            background: #0066cc !important;
            color: white !important;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }

        .table td {
            padding: 8px;
            vertical-align: top;
            border: 1px solid #ddd;
            font-size: 11px;
            line-height: 1.4;
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
            font-size: 10px;
            padding: 3px 6px;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Botones flotantes -->
    <div class="floating-buttons no-print">
        <button class="btn btn-success btn-floating" onclick="descargarDocumento()">
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
        <!-- Header con logos -->
        <div class="document-header">
            <div class="logos-container">
                <!-- Logo Institución (Izquierda) -->
                <div class="logo-container" style="width: 80px; height: 80px;">
                    <img src="{{ url('assets/img/logo_colegio.png') }}" alt="Logo Institución"
                        style="width: 80px; height: 80px; object-fit: contain;">
                </div>

                <!-- Contenido Central -->
                <div class="text-center flex-grow-1">
                    <div class="document-title">SESIÓN DE APRENDIZAJE</div>
                    <h3 style="color: #0066cc;">"{{ $sesion->titulo }}"</h3>
                    <h5 style="color: #0066cc;">{{ date('Y') }}</h5>
                </div>

            </div>
        </div>

        <div class="p-4">
            <!-- 1. DATOS INFORMATIVOS -->
            <div class="section-title">1. DATOS INFORMATIVOS:</div>

            <table class="info-table">
                <tr>
                    <th style="width: 40%;">1.1. Nombre de la sesión de aprendizaje:</th>
                    <td>{{ $sesion->titulo }}</td>
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
                    <th>1.5. Grado y sección:</th>
                    <td>{{ $unidad->grado }}° grado - Secciones: {{ implode(', ', $unidad->secciones ?? []) }}</td>
                </tr>
                <tr>
                    <th>1.6. Temporalización:</th>
                    <td>Inicio: {{ $unidad->fecha_inicio->format('d/m/Y') }} | Término:
                        {{ $unidad->fecha_fin->format('d/m/Y') }}</td>
                </tr>
            </table>

            <!-- 3. PROPÓSITOS DE APRENDIZAJE -->
            <div class="section-title">3. PROPÓSITOS DE APRENDIZAJE:</div>

            @if (count($sesionInfo['propositos']) > 0)
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 20%;">COMPETENCIA</th>
                            <th style="width: 20%;">CAPACIDADES</th>
                            <th style="width: 20%;">DESEMPEÑOS</th>
                            <th style="width: 20%;">CRITERIOS</th>
                            <th style="width: 20%;">INSTRUMENTOS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sesionInfo['propositos'] as $proposito)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary mb-2" style="font-size: 11px;">
                                        <i class="fas fa-target me-1"></i>
                                        {{ $proposito['competencia'] ?? 'No especificado' }}
                                    </div>
                                </td>
                                <td>
                                    @if (!empty($proposito['capacidades']))
                                        @foreach ($proposito['capacidades'] as $capacidad)
                                            <div class="small text-muted mt-1" style="font-size: 9px;">
                                                <i class="fas fa-arrow-right me-1" style="font-size: 7px;"></i>
                                                {{ $capacidad }}
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">No especificado</span>
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($proposito['desempenos']))
                                        @foreach ($proposito['desempenos'] as $desempeno)
                                            <div class="small mb-2" style="font-size: 9px;">
                                                <i class="fas fa-check-circle text-success me-1"
                                                    style="font-size: 7px;"></i>
                                                {{ $desempeno }}
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">No especificado</span>
                                    @endif
                                </td>
                                <td class="small" style="font-size: 9px;">
                                    {{ $proposito['criterios'] ?? 'No especificado' }}
                                </td>
                                <td>
                                    @if (!empty($proposito['instrumentos']))
                                        @foreach ($proposito['instrumentos'] as $instrumento)
                                            <span class="badge bg-secondary small d-block mb-1" style="font-size: 8px;">
                                                {{ $instrumento }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">No especificado</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="content-text">No se han definido propósitos de aprendizaje para esta sesión.</div>
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

            <!-- 5. MATERIALES BÁSICOS Y RECURSOS -->
            <div class="section-title">5. MATERIALES BÁSICOS Y RECURSOS A UTILIZAR EN LA UNIDAD</div>

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
        </div>
    </div>

    <script>
        function descargarDocumento() {
            const orientacion = '{{ $orientacion }}';
            window.location.href = `/unidades/{{ $unidad->id }}/previsualizar?orientacion=${orientacion}`;
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
