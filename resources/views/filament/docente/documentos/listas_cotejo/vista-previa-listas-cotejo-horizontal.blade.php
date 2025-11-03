<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Lista de cotejo - {{ $sesion->titulo ?? 'Sesión' }} (Horizontal)</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<style>
		@page { size: A4 landscape; margin: 10mm; }
		@media print {
			.no-print { display: none !important; }
			body { margin: 0; }
			.document-preview { max-width: 100% !important; width: auto !important; margin: 0; box-shadow: none !important; padding: 6mm !important; }
			table { table-layout: fixed !important; width: 100% !important; word-break: break-word !important; }
			.cotejo-table th, .cotejo-table td { font-size: 10px !important; padding: 4px !important; }
		}
		/* Estilos pantalla */
		.document-preview {
			max-width: 1200px;
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
		.cotejo-table th, .cotejo-table td { border:1px solid #dee2e6; padding:8px; text-align:center; vertical-align:middle; }
	</style>
</head>
<body class="bg-light">
	<div class="floating-buttons no-print" style="position:fixed; top:20px; right:20px; z-index:1000;">
		@php $firstListaId = $listas->first()?->id ?? null; @endphp
		<button class="btn btn-primary mb-2" type="button" onclick="descargarDocumento();"><i class="fas fa-download"></i> Descargar Word</button>
		<button class="btn btn-success mb-2" type="button" onclick="imprimirLista();"><i class="fas fa-print"></i> Imprimir</button>
		<button class="btn btn-secondary mb-2" type="button" onclick="cerrarVentana();"><i class="fas fa-arrow-left"></i> Volver</button>
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
				if(count($niveles) < 3) { $niveles = array_pad($niveles, 3, ''); }
				$competenciaNombre = $lista->competencia_nombre ?? ($lista->competencia?->nombre ?? null);
				if (!$competenciaNombre) {
					$propositos = $sesion->detalle->propositos_aprendizaje ?? [];
					foreach ($propositos as $prop) {
						$propCompetenciaId = $prop['competencia_id'] ?? null;
						if (!$propCompetenciaId) continue;
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

			<table class="meta-table">
				<tr><td style="width:100%;">Competencia: {{ $competenciaNombre ?? '—' }}</td></tr>
				<tr><td>Título: {{ $lista->titulo ?? ($competenciaNombre ?? 'Lista de cotejo') }}</td></tr>
			</table>

			<p class="small-muted mb-2">Total estudiantes: {{ $estudiantes->count() }}</p>

			<div class="table-responsive mb-4">
				<table class="table cotejo-table" style="width:100%; border-collapse:collapse;">
					<thead>
						@php $colspan = max(0, count($criterios) * count($niveles)); @endphp
						<tr>
							<th rowspan="3" style="width:4%;">N°</th>
							<th rowspan="3" style="width:36%;">Apellidos y nombres de los estudiantes</th>
							<th class="text-center" colspan="{{ $colspan }}">CRITERIOS</th>
						</tr>
						<tr>
							@foreach($criterios as $crit)
								<th class="text-center" colspan="{{ count($niveles) ?: 1 }}" style="min-width:160px;">{{ $crit }}</th>
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
						@forelse($estudiantes as $index => $est)
							@php
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
						@empty
							<tr>
								<td>1</td>
								<td class="student-name">&nbsp;</td>
								@foreach($criterios as $crit)
									@foreach($niveles as $nivel)
										<td class="criteria-cell"></td>
									@endforeach
								@endforeach
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

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

	<script>
		function descargarDocumento() {
			const orient = '{{ $orientacion ?? 'horizontal' }}';
			const firstId = '{{ $firstListaId ?? '' }}';
			if (!firstId) {
				alert('No hay listas disponibles para descargar.');
				return;
			}
			window.location.href = `/listas-cotejo/${firstId}/previsualizar?orientacion=${orient}`;
		}

		function imprimirLista() { try { window.print(); } catch (e) { console.error(e); } }
		function cerrarVentana() {
			try { if (window.opener && !window.opener.closed) { window.close(); return; } } catch(e){}
			if (window.history.length > 1) { window.history.back(); return; }
			window.location.href = '/docente/sesions';
		}
		document.addEventListener('keydown', function(e){ if (e.key === 'Escape') cerrarVentana(); });
		document.addEventListener('click', function(event){ if (!event.target.closest('.document-preview')) cerrarVentana(); });
		document.querySelectorAll('.document-preview').forEach(function(el){ el.addEventListener('click', e => e.stopPropagation()); });
		@if(request()->get('autoPrint'))
		window.addEventListener('load', function(){ setTimeout(()=>{ try{ window.print(); }catch(e){} }, 300); });
		window.onafterprint = function(){ try{ window.close(); }catch(e){} };
		@endif
	</script>
</body>
</html>
