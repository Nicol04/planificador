<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de asistencia - {{ $mes ?? 'Mes no definido' }}</title>
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
        <h5 class="fw-bold">Mes: {{ ucfirst($mes) }}</h5>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered w-100">
            <thead>
                <tr class="week-header">
                    <th rowspan="2" style="width:4%;">N°</th>
                    <th rowspan="2" style="width:36%;">Apellidos y nombres</th>
                    @for($w=1;$w<=4;$w++)
                        <th class="text-center" colspan="5">Semana {{ $w }}</th>
                    @endfor
                    <th rowspan="2" style="width:10%;">Observaciones</th>
                </tr>
                <tr class="week-header">
                    @for($w=1;$w<=4;$w++)
                        @foreach(['L','M','M','J','V'] as $d)
                            <th class="text-center small">{{ $d }}</th>
                        @endforeach
                    @endfor
                </tr>
            </thead>
            <tbody>
                @forelse($estudiantes as $index => $est)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="student-name">{{ $est['nombre'] }}</td>
                        @for($i=0;$i<20;$i++)
                            <td style="height:28px;"></td>
                        @endfor
                        <td></td>
                    </tr>
                @empty
                    <tr><td colspan="23" class="text-center text-muted">No hay estudiantes registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
