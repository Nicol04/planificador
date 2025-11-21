<x-filament-panels::page>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/main.js'])

    <div class="space-y-6">
        {{-- Header mejorado --}}
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">📄 Plantillas de
                    Asistencia</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Selecciona una plantilla para crear tu
                    asistencia — <span class="font-medium text-indigo-600">{{ $plantillas->count() }} disponibles</span>
                </p>
            </div>

            <div class="flex items-center gap-3">

                <div class="inline-flex items-center rounded-full bg-white/60 dark:bg-gray-800/60 shadow-sm p-1">
                    <button id="btn-plantillas" type="button" class="tab-btn px-3 py-1 rounded-full text-sm font-medium"
                        aria-pressed="true" data-tab="plantillas">Plantillas</button>
                    <button id="btn-mis-asistencias" type="button"
                        class="tab-btn px-3 py-1 rounded-full text-sm font-medium" aria-pressed="false"
                        data-tab="mis">Mis asistencias</button>
                </div>
            </div>
        </div>

        {{-- Sección: Plantillas --}}
        <div id="plantillas-section">
            {{-- Cards de plantillas (rediseñadas) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($plantillas as $plantilla)
                    <article
                        class="tpl-card group overflow-hidden rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-lg transition-transform transform hover:-translate-y-1">
                        <div class="relative">
                            @php
                                $preview = $plantilla->imagen_preview ?? null;
                                // ruta por defecto (coloca tu placeholder en public/images/placeholder-template.png)
                                $placeholder = asset('images/placeholder-template.png');
                                if (empty($preview)) {
                                    $previewUrl = $placeholder;
                                } elseif (preg_match('/^https?:\\/\\//i', $preview)) {
                                    $previewUrl = $preview;
                                } else {
                                    $previewUrl = \Illuminate\Support\Facades\Storage::url($preview);
                                }
                            @endphp
                            <img src="{{ $previewUrl }}" alt="{{ $plantilla->nombre }}" loading="lazy"
                                class="w-full h-40 object-cover"
                                onerror="this.onerror=null;this.src='{{ $placeholder }}'">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                            </div>

                            <div class="absolute left-3 top-3">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded bg-white/80 text-gray-800">
                                    {{ ucfirst($plantilla->tipo) }}
                                </span>
                            </div>

                            <div class="absolute right-3 top-3">
                                @if ($plantilla->public)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded bg-indigo-100 text-indigo-700">Público</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded bg-gray-100 text-gray-700">Privado</span>
                                @endif
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $plantilla->nombre }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                {{ $plantilla->archivo ? 'Plantilla cargada' : 'Sin archivo' }}</p>

                            <div class="flex items-center justify-between mt-4 gap-2">
                                <div class="card-actions flex gap-2 items-center">
                                    <button class="btn btn-primary btn-use-template" data-id="{{ $plantilla->id }}"
                                        title="Usar plantilla">Usar</button>

                                    <button class="btn btn-secondary btn-view-template" data-image="{{ $previewUrl }}"
                                        data-name="{{ $plantilla->nombre }}" title="Ver plantilla">
                                        Ver
                                    </button>
                                </div>

                                <a href="#" class="text-xs text-gray-400">ID {{ $plantilla->id }}</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-center text-gray-500">No hay plantillas de asistencia disponibles.</p>
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
                        <div
                            class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                                    {{ $asistencia->nombre_aula ?? 'Asistencia #' . $asistencia->id }}
                                </h3>
                                <p class="text-sm text-gray-500 mb-2">
                                    {{ \Illuminate\Support\Str::title(\DateTime::createFromFormat('!m', $asistencia->mes)->format('F') ?? '') ?? '' }}
                                    {{ $asistencia->anio ?? '' }}
                                </p>
                                @if ($asistencia->plantilla)
                                    <p class="text-sm text-gray-500 mb-3">Plantilla:
                                        {{ $asistencia->plantilla->nombre }}</p>
                                @endif

                                <div class="flex justify-between items-center">
                                    <div class="card-actions flex gap-2 items-center">
                                        <a href="{{ \App\Filament\Docente\Resources\AsistenciaResource::getUrl('edit', ['record' => $asistencia->id]) }}"
                                            class="btn btn-secondary" title="Editar asistencia">
                                            Editar
                                        </a>
                                        <a href="{{ route('asistencias.previsualizar.show', ['id' => $asistencia->id]) }}"
                                            target="_blank" rel="noopener" class="btn btn-primary"
                                            title="Previsualizar asistencia">
                                            Previsualizar
                                        </a>
                                        <button
                                            onclick="confirmarEliminacionAsistencia({{ $asistencia->id }}, '{{ addslashes($asistencia->nombre_aula) }}')"
                                            class="inline-flex items-center gap-2 px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-lg shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 transition">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                            Eliminar
                                        </button>
                                    </div>
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
            function confirmarEliminacionAsistencia(id, aula) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '🗑️ Eliminar Asistencia',
                        html: `
                <div style="text-align:left; padding:20px;">
                    <p><strong>¿Deseas eliminar esta asistencia?</strong></p>
                    <br>
                    <div style="background:#fef2f2; padding:15px; border-radius:8px; border-left:4px solid #dc3545;">
                        <p><strong>🏫 Aula:</strong> ${aula}</p>
                        <p><strong>❌ Se eliminará:</strong> La asistencia completa</p>
                    </div>
                    <br>
                    <p style="color:#dc3545; font-size:14px; font-weight:bold;">
                        Esta acción no se puede deshacer.
                    </p>
                </div>
            `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        reverseButtons: true,
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return new Promise((resolve) => {
                                @this.call('deleteAsistencias', id).then(() => {
                                    resolve();
                                });
                            });
                        }
                    });
                } else {
                    if (confirm(`¿Eliminar asistencia del aula "${aula}"?`)) {
                        @this.call('deleteAsistencias', {
                            asistencia_id: id
                        });
                    }
                }
            }
            document.addEventListener('DOMContentLoaded', function() {
                // --- Estilos & clases para los botones (coherentes y con transición) ---
                const activeClasses = ['bg-indigo-600', 'text-white', 'shadow-md'];
                const inactiveClasses = ['bg-white', 'text-gray-700', 'border', 'border-gray-200', 'dark:bg-gray-800',
                    'dark:text-gray-200', 'dark:border-gray-700'
                ];
                const tabButtons = document.querySelectorAll('.tab-btn');

                // Estilizar botones base
                tabButtons.forEach(b => {
                    b.classList.add('px-4', 'py-2', 'rounded-full', 'text-sm', 'transition', 'duration-200',
                        'focus:outline-none', 'focus:ring', 'focus:ring-indigo-300');
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
                        targetUrl = createUrl + (createUrl.includes('?') ? '&' : '?') + 'plantilla_id=' +
                            encodeURIComponent(plantillaId);
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
                        btn.addEventListener('click', function() {
                            const plantillaId = this.dataset.id;
                            const nombre = this.closest('div').querySelector('h3')?.innerText ??
                                `#${plantillaId}`;
                            confirmarUsoPlantilla(plantillaId, nombre);
                        });
                    });

                    // nuevo: abrir modal de preview al hacer click en "Ver"
                    document.querySelectorAll('.btn-view-template').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const src = this.dataset.image || '';
                            const name = this.dataset.name || '';
                            const modal = document.getElementById('previewModal');
                            const img = document.getElementById('previewImage');
                            const title = document.getElementById('previewTitle');
                            const backdrop = modal ? modal.querySelector('.preview-modal-backdrop') :
                                null;
                            // asignar contenido
                            if (img) {
                                img.src = src;
                                img.alt = name;
                            }
                            if (title) title.textContent = name;
                            // mostrar modal y backdrop inmediatamente (evita depender de otros listeners)
                            if (modal) {
                                modal.classList.remove('hidden');
                                modal.setAttribute('aria-hidden', 'false');
                                if (backdrop) backdrop.style.display = 'flex';
                            }
                        });
                    });
                    // handlers de cierre (cerrar botón y click en backdrop)
                    const closeBtn = document.getElementById('previewClose');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', function() {
                            const modal = document.getElementById('previewModal');
                            if (!modal) return;
                            const backdrop = modal.querySelector('.preview-modal-backdrop');
                            const img = document.getElementById('previewImage');
                            modal.classList.add('hidden');
                            modal.setAttribute('aria-hidden', 'true');
                            if (backdrop) backdrop.style.display = 'none';
                            if (img) img.src = '';
                        });
                    }
                    // click fuera del contenido -> cerrar
                    const modal = document.getElementById('previewModal');
                    if (modal) {
                        const backdrop = modal.querySelector('.preview-modal-backdrop');
                        if (backdrop) {
                            backdrop.addEventListener('click', function(e) {
                                if (e.target === backdrop) {
                                    modal.classList.add('hidden');
                                    modal.setAttribute('aria-hidden', 'true');
                                    backdrop.style.display = 'none';
                                    const img = document.getElementById('previewImage');
                                    if (img) img.src = '';
                                }
                            });
                        }
                    }
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

    {{-- Nuevo CSS para mejorar diseño --}}
    <style>
        /* compact card helpers */
        .tpl-card img {
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        }

        .tpl-card .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ajustar botones tab */
        .tab-btn {
            background: transparent;
            border: 1px solid transparent;
            color: #374151;
        }

        .tab-btn[aria-pressed="true"] {
            background: linear-gradient(90deg, #4f46e5, #06b6d4);
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.12);
        }

        /* Normalizar botones dentro de las tarjetas */
        .card-actions .btn {
            height: 36px;
            padding: 0 12px;
            font-size: 0.9rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
            border: 1px solid rgba(37, 99, 235, 0.12);
        }

        .btn-primary:hover {
            background: #1e40af;
        }

        .btn-secondary {
            background: #ffffff;
            color: #334155;
            border: 1px solid #e6eaf0;
        }

        .btn-secondary:hover {
            background: #f8fafc;
        }

        /* Ajuste para botones con iconos o texto corto */
        .btn {
            line-height: 1;
        }

        /* responsive tweaks */
        @media (max-width: 640px) {
            .tpl-card img {
                height: 120px;
                object-fit: cover;
            }
        }

        /* Modal (estilos reutilizables) */
        .preview-modal-backdrop {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1050;
        }

        .preview-modal-content {
            max-width: 1100px;
            width: 100%;
            max-height: 90vh;
            overflow: auto;
            border-radius: 8px;
            background: #fff;
            padding: 12px;
            position: relative;
        }

        .preview-modal-close {
            position: absolute;
            right: 8px;
            top: 8px;
            background: #111;
            color: #fff;
            border: none;
            padding: 6px 8px;
            border-radius: 6px;
            cursor: pointer;
        }

        .preview-modal-img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .preview-modal-title {
            margin-bottom: 8px;
            font-weight: 700;
            font-size: 1rem;
            color: #111827;
        }
    </style>

    {{-- Modal para previsualización de imagen (único, oculto por defecto) --}}
    <div id="previewModal" class="hidden" aria-hidden="true">
        <div class="preview-modal-backdrop" role="dialog" aria-modal="true" style="display:none;">
            <div class="preview-modal-content">
                <button id="previewClose" class="preview-modal-close" aria-label="Cerrar preview"
                    onclick="document.getElementById('previewModal').classList.add('hidden'); document.querySelector('#previewModal .preview-modal-backdrop').style.display='none';">
                    Cerrar
                </button>
                <div class="p-4">
                    <h3 id="previewTitle" class="preview-modal-title"></h3>
                    <img id="previewImage" class="preview-modal-img" src="" alt="Previsualización">
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
