<x-filament-panels::page>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/main.js'])

	<div class="space-y-6">
		{{-- Header --}}
		<div class="flex justify-between items-center">
			<div>
				<h1 class="text-3xl font-bold text-gray-900 dark:text-white">📄 Plantillas de Asistencia</h1>
				<p class="text-gray-600 dark:text-gray-400">Selecciona una plantilla para crear tu asistencia</p>
			</div>
		</div>

		{{-- Reemplazo: Botones toggle centrados con mejor diseño --}}
		<div class="w-full flex justify-center mt-2">
			<div class="inline-flex items-center rounded-full bg-white/60 dark:bg-gray-800/60 shadow-sm p-1 gap-2">
				<button id="btn-plantillas" type="button" class="tab-btn" aria-pressed="true" data-tab="plantillas">
					<span class="hidden sm:inline">📁</span> Plantillas
				</button>
				<button id="btn-mis-asistencias" type="button" class="tab-btn" aria-pressed="false" data-tab="mis">
					<span class="hidden sm:inline">🗂️</span> Mis asistencias
				</button>
			</div>
		</div>

		{{-- Sección: Plantillas --}}
		<div id="plantillas-section">
			{{-- Cards de plantillas --}}
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
				@forelse($plantillas as $plantilla)
					<div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden group">
						{{-- Imagen preview --}}
						<img src="{{ $plantilla->imagen_preview_url }}" alt="{{ $plantilla->nombre }}" class="h-40 w-full object-cover rounded-t-xl">

						<div class="p-6">
							{{-- Nombre --}}
							<h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $plantilla->nombre }}</h3>
							<p class="text-sm text-gray-500 mb-4">Tipo: {{ ucfirst($plantilla->tipo) }}</p>

							<div class="flex justify-between">
								<button class="btn btn-primary btn-use-template" data-id="{{ $plantilla->id }}">
									Usar plantilla
								</button>
							</div>
						</div>
					</div>
				@empty
					<p>No hay plantillas de asistencia disponibles.</p>
				@endforelse
			</div>
		</div>

		{{-- Sección: Mis Asistencias (oculta por defecto) --}}
		<div id="misasistencias-section" class="hidden">
			<div>
				<div class="flex items-center justify-between">
					<div>
						<h2 class="text-2xl font-semibold text-gray-900 dark:text-white">🗂️ Mis Asistencias</h2>
						<p class="text-gray-600 dark:text-gray-400">Asistencias que has creado</p>
					</div>
				</div>

				<div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
					@forelse($misAsistencias as $asistencia)
						<div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden">
							<div class="p-6">
								<h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
									{{ $asistencia->nombre_aula ?? 'Asistencia #' . $asistencia->id }}
								</h3>
								<p class="text-sm text-gray-500 mb-2">
									{{ \Illuminate\Support\Str::title(\DateTime::createFromFormat('!m', $asistencia->mes)->format('F') ?? '') ?? '' }}
									{{ $asistencia->anio ?? '' }}
								</p>
								@if($asistencia->plantilla)
									<p class="text-sm text-gray-500 mb-3">Plantilla: {{ $asistencia->plantilla->nombre }}</p>
								@endif

								<div class="flex justify-between">
									<a href="{{ \App\Filament\Docente\Resources\AsistenciaResource::getUrl('edit', ['record' => $asistencia->id]) }}" class="btn btn-secondary">
										Abrir
									</a>
									<span class="text-xs text-gray-400 self-center">ID: {{ $asistencia->id }}</span>
								</div>
							</div>
						</div>
					@empty
						<p class="text-gray-500">No has creado asistencias aún.</p>
					@endforelse
				</div>
			</div>
		</div>
	</div>

	@push('scripts')
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				// --- Estilos & clases para los botones (coherentes y con transición) ---
				const activeClasses = ['bg-indigo-600','text-white','shadow-md'];
				const inactiveClasses = ['bg-white','text-gray-700','border','border-gray-200','dark:bg-gray-800','dark:text-gray-200','dark:border-gray-700'];
				const tabButtons = document.querySelectorAll('.tab-btn');

				// Estilizar botones base
				tabButtons.forEach(b => {
					b.classList.add('px-4','py-2','rounded-full','text-sm','transition','duration-200','focus:outline-none','focus:ring','focus:ring-indigo-300');
				});

				function setButtonActive(btn) {
					// limpiar
					tabButtons.forEach(b => {
						b.classList.remove(...activeClasses);
						b.classList.remove(...inactiveClasses);
						b.setAttribute('aria-pressed', 'false');
					});
					// activar seleccionado
					btn.classList.add(...activeClasses);
					btn.setAttribute('aria-pressed', 'true');
				}

				// --- Toggle de secciones (plantillas / mis asistencias) ---
				const btnPlantillas = document.getElementById('btn-plantillas');
				const btnMis = document.getElementById('btn-mis-asistencias');
				const sectionPlantillas = document.getElementById('plantillas-section');
				const sectionMis = document.getElementById('misasistencias-section');

				function setActiveTab(tab) {
					if (tab === 'plantillas') {
						sectionPlantillas.classList.remove('hidden');
						sectionMis.classList.add('hidden');
						setButtonActive(btnPlantillas);
					} else {
						sectionPlantillas.classList.add('hidden');
						sectionMis.classList.remove('hidden');
						setButtonActive(btnMis);
					}
				}

				btnPlantillas.addEventListener('click', () => setActiveTab('plantillas'));
				btnMis.addEventListener('click', () => setActiveTab('mis'));

				// Inicializar estado (plantillas por defecto)
				setActiveTab('plantillas');

				// --- Integración con código existente de confirmar uso de plantilla ---
				const createUrl = @json(\App\Filament\Docente\Resources\AsistenciaResource::getUrl('create'));

				function confirmarUsoPlantilla(plantillaId, nombrePlantilla) {
					let targetUrl;
					try {
						const urlObj = new URL(createUrl, window.location.origin);
						urlObj.searchParams.set('plantilla_id', plantillaId);
						targetUrl = urlObj.toString();
					} catch (e) {
						targetUrl = createUrl + (createUrl.includes('?') ? '&' : '?') + 'plantilla_id=' + encodeURIComponent(plantillaId);
					}

					if (typeof Swal !== 'undefined') {
						const title = '🌐 Usar plantilla';
						const htmlMessage = `<p>¿Deseas usar la plantilla <strong>${nombrePlantilla}</strong> para crear una nueva asistencia?</p>
											 <p class="text-muted">Se abrirá el formulario de creación con la plantilla seleccionada.</p>`;

						Swal.fire({
							title: title,
							html: htmlMessage,
							icon: 'question',
							showCancelButton: true,
							confirmButtonText: '<i class="fas fa-check-circle"></i> Sí, usar plantilla',
							cancelButtonText: 'Cancelar',
							confirmButtonColor: '#0066cc',
							cancelButtonColor: '#6c757d',
							showLoaderOnConfirm: true,
							preConfirm: () => {
								return new Promise((resolve) => {
									setTimeout(() => {
										window.location.href = targetUrl;
										resolve();
									}, 400);
								});
							},
							allowOutsideClick: () => !Swal.isLoading()
						});
					} else {
						if (confirm(`Usar plantilla "${nombrePlantilla}" para crear asistencia?`)) {
						 window.location.href = targetUrl;
						}
					}
				}

				function initHandlers() {
					document.querySelectorAll('.btn-use-template').forEach(btn => {
						btn.addEventListener('click', function () {
							const plantillaId = this.dataset.id;
							const nombre = this.closest('div').querySelector('h3')?.innerText ?? `#${plantillaId}`;
							confirmarUsoPlantilla(plantillaId, nombre);
						});
					});
				}

				// Cargar Swal desde CDN si no existe
				if (typeof Swal === 'undefined') {
					const s = document.createElement('script');
					s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
					s.onload = initHandlers;
					document.head.appendChild(s);
				} else {
					initHandlers();
				}
			});
		</script>
	@endpush
</x-filament-panels::page>
