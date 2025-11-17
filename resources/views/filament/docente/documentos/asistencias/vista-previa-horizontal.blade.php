<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de asistencia - {{ $mes ?? 'Mes no definido' }} {{ $anio ?? '' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
        .document-preview {
            max-width: {{ ($orientacion ?? 'vertical') === 'horizontal' ? '1000px' : '800px' }};
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .table th, .table td { vertical-align: middle; text-align: center; border: 1px solid #ddd; }
        .student-name { text-align: left; padding-left: 8px; white-space: nowrap; }
        .week-header { background: #f3f4f6; }
        /* estilo para días marcados como "no clase" */
        .no-class-column {
            background: linear-gradient(180deg,#e8f1ff 0,#d6e8ff 100%);
            color: #1e40af;
        }
        /* número de día en encabezado */
        .day-header-number {
            font-size: 12px;
            color: #374151;
            font-weight: 600;
            padding: 6px;
        }

        /* Overrides específicos para asegurar pintado en th y td even si Bootstrap aplica fondo */
        th.no-class-column, td.no-class-column {
            background: linear-gradient(180deg,#e8f1ff 0,#d6e8ff 100%) !important;
            color: #1e40af !important;
            border-color: rgba(37,99,235,0.12) !important;
        }

        /* Si quieres destacar aún más, aplicar borde y sombra ligeros */
        th.no-class-column {
            box-shadow: inset 0 0 0 1px rgba(37,99,235,0.06);
        }
    </style>
</head>
<body class="bg-light">

<div class="no-print" style="position:fixed; top:20px; right:20px; z-index:1000;">
    <a class="btn btn-secondary mb-2" href="javascript:history.back();">Volver</a>
    <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
</div>

<div class="document-preview">
    <div class="text-center mb-3">
        <h4 class="text-primary">LISTA DE ASISTENCIA</h4>
        <h5 class="fw-bold">Mes: {{ ucfirst($mes ?? '') }} {{ $anio ? ' / ' . $anio : '' }}</h5>
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
                <tr class="week-header">
                    <th rowspan="3" style="width:4%;">N°</th>
                    <th rowspan="3" style="width:36%;">Apellidos y nombres</th>

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

                    <th rowspan="3" style="width:10%;">Observaciones</th>
                </tr>

                <tr class="week-header">
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

                <tr class="week-header">
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
                        <td>{{ $index + 1 }}</td>
                        <td class="student-name">{{ $est['nombre'] }}</td>

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
</body>
</html>
