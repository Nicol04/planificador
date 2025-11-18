<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Lista de cotejo - {{ $sesion->titulo ?? 'Sesión' }} (Horizontal)</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&family=Nunito:wght@300;400;700&display=swap" rel="stylesheet">
	<style>
		@page { size: A4 landscape; margin: 10mm; }
		@media print {
			.no-print { display: none !important; }
			body { margin: 0; }
			.document-preview { max-width: 100% !important; width: auto !important; margin: 0; box-shadow: none !important; padding: 6mm !important; }
			table { table-layout: fixed !important; width: 100% !important; word-break: break-word !important; }
			.cotejo-table th, .cotejo-table td { font-size: 10px !important; padding: 4px !important; }
		}

		/* NUEVOS ESTILOS: ocupar todo el ancho disponible */
		:root{
			--frame-border: #092f26;
			--accent:#0b6b4a;
			--muted:#6b7280;
			--header-bg:#083927;
		}
		body { font-family: 'Nunito', system-ui, -apple-system, "Segoe UI", Roboto, Arial; color:#042f26; }
		/* eliminar max-width para ocupar todo el espacio */
		.document-wrapper { width:100%; max-width:none; margin: 0; padding: 12px 18px; background:transparent; border-radius:0; border: none; box-shadow: none; }
		.document-preview { background:#fff; padding:12px; border-radius:6px; box-shadow: 0 6px 18px rgba(4,47,38,0.05); }

		/* Asegurar que la tabla ocupe todo el ancho y permita escritura/manual filling */
		.cotejo-table { width:100%; border-collapse:collapse; table-layout:fixed; }
		.cotejo-table th, .cotejo-table td { border: 1px solid rgba(5,36,30,0.14); padding:8px; vertical-align:middle; text-align:center; font-size:13px; word-break:break-word; }
		.cotejo-table thead th { background: linear-gradient(180deg,#eaf6ef,#dff0e7); color:var(--frame-border); font-weight:800; }

		/* Ajustes responsivos: permitir scroll horizontal cuando sea necesario */
		.table-responsive { overflow-x:auto; width:100%; }
		.toolbar { position:fixed; top:18px; right:18px; z-index:1200; display:flex; flex-direction:column; gap:8px; }
		.toolbar .btn { min-width:180px; }

		/* ocultar floating en impresión (ya se cubre con .no-print pero aseguramos) */
		@media print { .toolbar { display:none !important; } }

		/* responsivo: reducir ancho mínimo de columnas en pantallas pequeñas */
		@media (max-width:1100px) {
			.cotejo-table th, .cotejo-table td { font-size:12px; padding:6px; }
			.toolbar .btn { min-width:140px; font-size:13px; }
		}

		/* Forzar alineación izquierda en columna de nombres */
		.cotejo-table td.student-name { text-align:left !important; padding-left:12px; white-space:normal; }

		/* Header mejorado */
		.doc-header { text-align:left; margin-bottom:14px; }
		.doc-title { font-family:'Merriweather',serif; font-size:26px; color:#0b6b4a; font-weight:900; margin:0 0 6px 0; text-align:center; width:100%; } /* centrado */
		.doc-subtitle { font-family:'Nunito',sans-serif; color:#374151; font-size:15px; margin:0; text-align:center; width:100%; } /* centrado */

		.meta-chips { display:flex; gap:10px; flex-wrap:wrap; margin-top:8px; }
		.meta-chip{ display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; background:linear-gradient(180deg,#f3faf7,#ecfdf5); color:#064e3b; font-weight:700; border:1px solid rgba(6,78,59,0.06); }
		.meta-chip i{ width:16px; text-align:center; }

		/* Badges (sin emoji) */
		.level-badge{ display:inline-flex; align-items:center; justify-content:center; padding:.28rem .6rem; border-radius:999px; color:#fff; font-weight:700; font-size:.85rem; }
		.level-no { background: linear-gradient(180deg,#ef4444,#c2410c); }
		.level-pro{ background: linear-gradient(180deg,#f59e0b,#d97706); }
		.level-dest{ background: linear-gradient(180deg,#10b981,#065f46); }

		/* Estados de criterios: en proceso (amarillo), no logrado (rojo), destacado (verde) */
		.criteria-cell { cursor: pointer; min-width:28px; padding:10px; transition: background-color .12s ease, border-color .12s ease; outline: none; font-weight:700; }
		.criteria-cell:focus { box-shadow: 0 0 0 3px rgba(11,107,74,0.08); }

		/* Column-level classes: colorear sólo los encabezados (th). td quedarán en blanco hasta selección */
		th.nivel-no { background: #fff1f2; border-color:#fca5a5; color:#7f1d1d; }
		th.nivel-pro { background: #fffbeb; border-color:#fcd34d; color:#92400e; }
		th.nivel-dest { background: #ecfdf5; border-color:#86efac; color:#065f46; }

		/* Estados individuales (resaltan más cuando se marca) */
		.criteria-cell[data-state="no"] { background: #fee2e2; border-color: #ef4444; color:#7f1d1d; }
		.criteria-cell[data-state="pro"] { background: #fef3c7; border-color: #f59e0b; color:#92400e; }
		.criteria-cell[data-state="dest"] { background: #dcfce7; border-color: #10b981; color:#065f46; }

		/* Asegurar impresión de colores */
		@media print {
			th.nivel-no, td.nivel-no, .criteria-cell[data-state="no"] { -webkit-print-color-adjust: exact; background: #fee2e2 !important; }
			th.nivel-pro, td.nivel-pro, .criteria-cell[data-state="pro"] { -webkit-print-color-adjust: exact; background: #fef3c7 !important; }
			th.nivel-dest, td.nivel-dest, .criteria-cell[data-state="dest"] { -webkit-print-color-adjust: exact; background: #dcfce7 !important; }
		}
	</style>
</head>
<body>
	<!-- Toolbar mejorada (botones más explícitos) -->
	<div class="toolbar no-print">
		@php $firstListaId = $listas->first()?->id ?? null; @endphp
		<button class="btn btn-outline-dark" type="button" onclick="cerrarVentana();"><i class="fas fa-arrow-left"></i> Volver</button>

		@if($firstListaId)
			<!-- Mantener sólo descarga sin plantilla y forzar horizontal (identificable para el JS) -->
			<a class="btn btn-success btn-download-word" href="{{ url('/listas-cotejo/'.$firstListaId.'/previsualizar') }}?orientacion=horizontal&raw=1" target="_blank"><i class="fas fa-file-word"></i> .docx</a>
		@else
			<button class="btn btn-primary" onclick="alert('No hay listas disponibles para descargar');">Descargar</button>
		@endif

		<button class="btn btn-secondary" type="button" onclick="imprimirLista();"><i class="fas fa-print"></i> Imprimir</button>
	</div>

	<div class="document-wrapper">
		<div class="document-preview p-3">
			<!-- Encabezado reemplazado -->
			<div class="doc-header">
				<div class="doc-title">{{ $listaTitle ?? ($sesion->titulo ?? 'LISTA DE COTEJO') }}</div>
				<div class="doc-subtitle">{{ $listaSub ?? '' }}</div>

				<div class="meta-chips">
					<div class="meta-chip"><i class="fas fa-user"></i> Docente: {{ $sesion->docente?->persona ? trim(($sesion->docente->persona->nombre ?? '') . ' ' . ($sesion->docente->persona->apellido ?? '')) : '—' }}</div>
					<div class="meta-chip"><i class="fas fa-school"></i> Grado/Sección: {{ $sesion->aulaCurso?->aula?->grado_seccion ?? '—' }}</div>
					<div class="meta-chip"><i class="fas fa-book"></i> Área: {{ $sesion->aulaCurso?->curso?->curso ?? '—' }}</div>
				</div>

				<!-- Leyenda eliminada según solicitud -->
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
					<table class="cotejo-table">
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
									@foreach($niveles as $i => $nivel)
										@php
											// forzar etiquetas y clases por posición: 0=>No logrado, 1=>En proceso, 2=>Destacado
											$pos = $i % 3;
											$label = ($pos === 0) ? 'No logrado' : (($pos === 1) ? 'En proceso' : 'Destacado');
											$cls = ($pos === 0) ? 'nivel-no' : (($pos === 1) ? 'nivel-pro' : 'nivel-dest');
										@endphp
										<th class="text-center small {{ $cls }}">{{ $label }}</th>
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
										@foreach($niveles as $i => $nivel)
											@php $pos = $i % 3; $colClass = ($pos === 0) ? 'nivel-no' : (($pos === 1) ? 'nivel-pro' : 'nivel-dest'); @endphp
											<td class="criteria-cell {{ $colClass }}" tabindex="0" role="button" aria-label="Criterio: {{ $crit }} - Nivel: {{ $label ?? $nivel }}" data-state="none" title="Clic: cambiar estado · Doble clic: limpiar · 1=No logrado 2=En proceso 3=Destacado"></td>
										@endforeach
									@endforeach
								</tr>
							@empty
								<tr>
									<td>1</td>
									<td class="student-name">&nbsp;</td>
									@foreach($criterios as $crit)
										@foreach($niveles as $i => $nivel)
											@php $pos = $i % 3; $colClass = ($pos === 0) ? 'nivel-no' : (($pos === 1) ? 'nivel-pro' : 'nivel-dest'); @endphp
											<td class="criteria-cell {{ $colClass }}" tabindex="0" role="button" aria-label="Criterio: {{ $crit }} - Nivel: {{ $label ?? $nivel }}" data-state="none" title="Clic: cambiar estado · Doble clic: limpiar · 1=No logrado 2=En proceso 3=Destacado"></td>
										@endforeach
									@endforeach
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

			@endforeach
		</div>
	</div>

	<script>
		// Advertencia antes de descargar .docx desde listas de cotejo
		(function(){
			function ensureSwal(cb){
				if (typeof Swal !== 'undefined') return cb();
				const s = document.createElement('script');
				s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
				s.onload = cb;
				document.head.appendChild(s);
			}
			document.querySelectorAll('.btn-download-word').forEach(function(btn){
				btn.addEventListener('click', function(e){
					e.preventDefault();
					const href = this.getAttribute('href');
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
							if(result.isConfirmed){
								window.open(href, '_blank');
							}
						});
					});
				});
			});
		})();

		function imprimirLista() { try { window.print(); } catch (e) { console.error(e); } }
		function cerrarVentana() {
			try { if (window.opener && !window.opener.closed) { window.close(); return; } } catch(e){}
			if (window.history.length > 1) { window.history.back(); return; }
			window.location.href = '/docente/sesions';
		}
		document.addEventListener('keydown', function(e){ if (e.key === 'Escape') cerrarVentana(); });

		// Ciclar estados y asignar según columna (click asigna estado de la columna; si ya está, lo quita)
		(function(){
			// order fallback: none -> no -> pro -> dest -> none
			function nextState(current){
				if(current === 'none') return 'no';
				if(current === 'no') return 'pro';
				if(current === 'pro') return 'dest';
				return 'none';
			}
			function emoticonFor(state){
				if(state === 'no') return '😞';   // No logrado
				if(state === 'pro') return '😐';  // En proceso
				if(state === 'dest') return '😊'; // Destacado
				return '';
			}
			function applyState(cell, state){
				cell.dataset.state = state;
				cell.classList.remove('status-no','status-pro','status-dest');
				if(state === 'no') cell.classList.add('status-no');
				if(state === 'pro') cell.classList.add('status-pro');
				if(state === 'dest') cell.classList.add('status-dest');
				// mostrar emoticon (ASCII según preferencia) y accesibilidad
				cell.textContent = emoticonFor(state);
				let label = state === 'none' ? 'Sin marcar' : (state === 'no' ? 'No logrado' : (state === 'pro' ? 'En proceso' : 'Destacado'));
				cell.setAttribute('title', label + ' — Clic: marcar según columna (clic nuevamente quita) · Doble clic: limpiar · 1=No logrado 2=En proceso 3=Destacado');
			}
			// Determina el estado asociado a la columna donde está la celda
			function stateForColumn(cell){
				if(cell.classList.contains('nivel-no')) return 'no';
				if(cell.classList.contains('nivel-pro')) return 'pro';
				if(cell.classList.contains('nivel-dest')) return 'dest';
				return null;
			}
			function onClick(e){
				let cell = e.currentTarget;
				let colState = stateForColumn(cell);
				if(colState){
					// si ya está marcado con el mismo estado, lo quitamos (toggle)
					let current = cell.dataset.state || 'none';
					if(current === colState){
						applyState(cell, 'none');
					} else {
						applyState(cell, colState);
					}
				} else {
					// fallback: ciclo clásico si no está en columna conocida
					applyState(cell, nextState(cell.dataset.state || 'none'));
				}
			}
			function onDblClick(e){
				let cell = e.currentTarget;
				applyState(cell, 'none');
			}
			function onKey(e){
				let cell = e.currentTarget;
				if(e.key === '1') applyState(cell,'no');     // No logrado
				if(e.key === '2') applyState(cell,'pro');    // En proceso
				if(e.key === '3') applyState(cell,'dest');   // Destacado
				if(e.key === 'Escape') applyState(cell,'none');
				if(e.key === 'Enter' || e.key === ' ') { e.preventDefault(); 
					// si la columna tiene estado asignado, alternamos con esa columna; si no, ciclo
					let colState = stateForColumn(cell);
					if(colState){
						let current = cell.dataset.state || 'none';
						if(current === colState) applyState(cell,'none'); else applyState(cell,colState);
					} else {
						applyState(cell, nextState(cell.dataset.state || 'none'));
					}
				}
			}
			// inicializar TODAS las celdas: estado 'none' y contenido vacío
			document.querySelectorAll('.criteria-cell').forEach(function(cell){
				// asegurar atributo y contenido en blanco
				applyState(cell, 'none');
			});

			// agregar listeners (ya existían)
			document.querySelectorAll('.criteria-cell').forEach(function(cell){
				cell.addEventListener('click', onClick);
				cell.addEventListener('dblclick', onDblClick);
				cell.addEventListener('keydown', onKey);
			});
		})();
	</script>
</body>
</html>
