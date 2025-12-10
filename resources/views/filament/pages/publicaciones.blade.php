<x-filament-panels::page>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-600 dark:text-gray-400">Explora las publicaciones compartidas por los docentes</p>
            </div>
        </div>
        {{-- Tabs de navegación --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex space-x-4" aria-label="Tabs">
                <button wire:click="setActiveTab('unidades')"
                    class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors duration-200
                        {{ $activeTab === 'unidades'
                            ? 'bg-primary-500 text-white'
                            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    <x-heroicon-o-squares-2x2 class="w-5 h-5 inline-block mr-1" />
                    Unidades Públicas
                </button>
                <button wire:click="setActiveTab('sesiones')"
                    class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors duration-200
                        {{ $activeTab === 'sesiones'
                            ? 'bg-primary-500 text-white'
                            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    <x-heroicon-o-academic-cap class="w-5 h-5 inline-block mr-1" />
                    Sesiones Públicas
                </button>
                <button wire:click="setActiveTab('fichas')"
                    class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors duration-200
                        {{ $activeTab === 'fichas'
                            ? 'bg-primary-500 text-white'
                            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    <x-heroicon-o-document-text class="w-5 h-5 inline-block mr-1" />
                    Fichas de Aprendizaje
                </button>
            </nav>
        </div>

        {{-- Contenido de las tabs --}}
        <div class="mt-4">
            {{-- Tab: Unidades Públicas --}}
            @if ($activeTab === 'unidades')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($this->getUnidadesPublicas() as $unidad)
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                            <div class="p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $unidad->grado ?? 'Sin grado' }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $unidad->created_at?->diffForHumans() }}
                                    </span>
                                </div>

                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ $unidad->nombre }}
                                </h3>

                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3 line-clamp-2">
                                    {{ Str::limit($unidad->situacion_significativa, 100) ?? 'Sin descripción' }}
                                </p>

                                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3">
                                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-2">
                                        <x-heroicon-o-calendar class="w-4 h-4 mr-1" />
                                        <span>{{ $unidad->fecha_inicio?->format('d/m/Y') }} -
                                            {{ $unidad->fecha_fin?->format('d/m/Y') }}</span>
                                    </div>

                                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-3">
                                        <x-heroicon-o-user class="w-4 h-4 mr-1" />
                                        <span>{{ $unidad->nombres_profesores ?: 'Sin docentes asignados' }}</span>
                                    </div>

                                    {{-- Botones de acción para Unidades --}}
                                    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/50">
                                        {{-- Botón Ver Vista Previa --}}
                                        <button onclick="abrirModalPreviaUnidad({{ $unidad->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                            <x-heroicon-s-eye class="w-4 h-4" />
                                            Ver
                                        </button>

                                        {{-- Menú dropdown descargas --}}
                                        <x-filament::dropdown placement="bottom-end">
                                            <x-slot name="trigger">
                                                <button class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                                    <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                                                    Descargar
                                                </button>
                                            </x-slot>
                                            <x-filament::dropdown.list>
                                                <x-filament::dropdown.list.item
                                                    onclick="descargarWordUnidad({{ $unidad->id }}, 'vertical')"
                                                    icon="heroicon-o-arrow-down-tray">
                                                    Word Vertical
                                                </x-filament::dropdown.list.item>
                                                <x-filament::dropdown.list.item
                                                    onclick="descargarWordUnidad({{ $unidad->id }}, 'horizontal')"
                                                    icon="heroicon-o-arrow-down-tray">
                                                    Word Horizontal
                                                </x-filament::dropdown.list.item>
                                            </x-filament::dropdown.list>
                                        </x-filament::dropdown>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <x-filament::section>
                                <div class="text-center py-8">
                                    <x-heroicon-o-document-minus class="w-12 h-12 mx-auto text-gray-400" />
                                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No hay unidades
                                        públicas</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Aún no se han publicado
                                        unidades.</p>
                                </div>
                            </x-filament::section>
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- Tab: Sesiones Públicas --}}
            @if ($activeTab === 'sesiones')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($this->getSesionesPublicas() as $sesion)
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                            <div class="p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ $sesion->dia ?? 'Sin día' }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $sesion->fecha?->format('d/m/Y') }}
                                    </span>
                                </div>

                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ $sesion->titulo }}
                                </h3>

                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                    <strong>Tema:</strong> {{ $sesion->tema ?? 'Sin tema' }}
                                </p>

                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3 line-clamp-2">
                                    {{ Str::limit($sesion->proposito_sesion, 100) ?? 'Sin propósito' }}
                                </p>

                                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3">
                                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-2">
                                        <x-heroicon-o-clock class="w-4 h-4 mr-1" />
                                        <span>{{ $sesion->tiempo_estimado ?? 'Sin tiempo estimado' }}</span>
                                    </div>

                                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-3">
                                        <x-heroicon-o-user class="w-4 h-4 mr-1" />
                                        <span>{{ $this->getNombreDocente($sesion->docente) }}</span>
                                    </div>

                                    {{-- Botones de acción para Sesiones --}}
                                    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/50">
                                        {{-- Botón Ver Vista Previa --}}
                                        <button onclick="abrirModalPreviaSesion({{ $sesion->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                            <x-heroicon-s-eye class="w-4 h-4" />
                                            Ver
                                        </button>

                                        {{-- Menú dropdown descargas --}}
                                        <x-filament::dropdown placement="bottom-end">
                                            <x-slot name="trigger">
                                                <button class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-green-500 hover:bg-green-600 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                                    <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                                                    Descargar
                                                </button>
                                            </x-slot>
                                            <x-filament::dropdown.list>
                                                <x-filament::dropdown.list.item
                                                    onclick="descargarWordSesion({{ $sesion->id }}, 'vertical')"
                                                    icon="heroicon-o-arrow-down-tray">
                                                    Word Vertical
                                                </x-filament::dropdown.list.item>
                                                <x-filament::dropdown.list.item
                                                    onclick="descargarWordSesion({{ $sesion->id }}, 'horizontal')"
                                                    icon="heroicon-o-arrow-down-tray">
                                                    Word Horizontal
                                                </x-filament::dropdown.list.item>
                                            </x-filament::dropdown.list>
                                        </x-filament::dropdown>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <x-filament::section>
                                <div class="text-center py-8">
                                    <x-heroicon-o-document-minus class="w-12 h-12 mx-auto text-gray-400" />
                                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No hay sesiones
                                        públicas</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Aún no se han publicado
                                        sesiones.</p>
                                </div>
                            </x-filament::section>
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- Tab: Fichas de Aprendizaje --}}
            @if ($activeTab === 'fichas')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($this->getFichasPublicas() as $ficha)
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                            <div class="p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                        {{ $ficha->grado ?? 'Sin grado' }}
                                    </span>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        {{ $ficha->tipo_ejercicio ?? 'Sin tipo' }}
                                    </span>
                                </div>

                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ $ficha->nombre }}
                                </h3>

                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3 line-clamp-3">
                                    {{ Str::limit($ficha->descripcion, 120) ?? 'Sin descripción' }}
                                </p>

                                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3">
                                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-2">
                                        <x-heroicon-o-calendar class="w-4 h-4 mr-1" />
                                        <span>{{ $ficha->created_at?->diffForHumans() }}</span>
                                    </div>

                                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-3">
                                        <x-heroicon-o-user class="w-4 h-4 mr-1" />
                                        <span>{{ $this->getNombreDocente($ficha->user) }}</span>
                                    </div>

                                    {{-- Botones de acción para Fichas --}}
                                    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/50">
                                        {{-- Botón Ver Vista Previa --}}
                                        <button onclick="abrirModalPreviaFicha({{ $ficha->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                            <x-heroicon-s-eye class="w-4 h-4" />
                                            Ver
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <x-filament::section>
                                <div class="text-center py-8">
                                    <x-heroicon-o-document-minus class="w-12 h-12 mx-auto text-gray-400" />
                                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No hay fichas
                                        de aprendizaje públicas</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Aún no se han publicado
                                        fichas de aprendizaje.</p>
                                </div>
                            </x-filament::section>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    {{-- Modal para vista previa de Unidad --}}
    <div id="modalPreviaUnidad"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 10px; padding: 30px; max-width: 500px; text-align: center;" class="dark:bg-gray-800">
            <h3 style="margin-bottom: 20px; color: #0066cc;" class="dark:text-blue-400">📄 Vista Previa de la Unidad</h3>
            <p style="margin-bottom: 30px;" class="text-gray-600 dark:text-gray-300">Seleccione el formato para previsualizar:</p>
            <div style="display: flex; gap: 15px; justify-content: center; margin-bottom: 20px;">
                <button onclick="abrirVistaPreviaUnidad('vertical')"
                    style="background: #0066cc; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    📄 Vista Vertical
                </button>
                <button onclick="abrirVistaPreviaUnidad('horizontal')"
                    style="background: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    📄 Vista Horizontal
                </button>
            </div>
            <button onclick="cerrarModalPreviaUnidad()"
                style="background: #6c757d; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer;">
                ❌ Cancelar
            </button>
        </div>
    </div>

    {{-- Modal para vista previa de Sesión --}}
    <div id="modalPreviaSesion"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 10px; padding: 30px; max-width: 500px; text-align: center;" class="dark:bg-gray-800">
            <h3 style="margin-bottom: 20px; color: #0066cc;" class="dark:text-blue-400">📄 Vista Previa de la Sesión</h3>
            <p style="margin-bottom: 30px;" class="text-gray-600 dark:text-gray-300">Seleccione el formato para previsualizar:</p>
            <div style="display: flex; gap: 15px; justify-content: center; margin-bottom: 20px;">
                <button onclick="abrirVistaPreviaSesion('vertical')"
                    style="background: #0066cc; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    📄 Vista Vertical
                </button>
                <button onclick="abrirVistaPreviaSesion('horizontal')"
                    style="background: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    📄 Vista Horizontal
                </button>
            </div>
            <button onclick="cerrarModalPreviaSesion()"
                style="background: #6c757d; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer;">
                ❌ Cancelar
            </button>
        </div>
    </div>

    {{-- Modal para vista previa de Ficha --}}
    <div id="modalPreviaFicha"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 10px; padding: 30px; max-width: 400px; text-align: center;" class="dark:bg-gray-800">
            <h3 style="margin-bottom: 20px; color: #0066cc; font-size: 1.5rem; font-weight: bold;" class="dark:text-blue-400">📄 Vista Previa</h3>
            <p style="margin-bottom: 30px;" class="text-gray-600 dark:text-gray-300">¿Deseas abrir la vista previa de la ficha?</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button onclick="abrirVistaPreviaFicha()"
                    style="background: #0066cc; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    👁️ Ver Ficha
                </button>
                <button onclick="cerrarModalPreviaFicha()"
                    style="background: #6c757d; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer;">
                    ❌ Cancelar
                </button>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .line-clamp-2 {
                overflow: hidden;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
            }
            .line-clamp-3 {
                overflow: hidden;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 3;
            }
            #modalPreviaUnidad button:hover,
            #modalPreviaSesion button:hover,
            #modalPreviaFicha button:hover {
                opacity: 0.9;
                transform: translateY(-1px);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // ========== UNIDADES ==========
            let unidadIdActual = null;

            function abrirModalPreviaUnidad(unidadId) {
                unidadIdActual = unidadId;
                document.getElementById('modalPreviaUnidad').style.display = 'flex';
            }

            function cerrarModalPreviaUnidad() {
                document.getElementById('modalPreviaUnidad').style.display = 'none';
                unidadIdActual = null;
            }

            function abrirVistaPreviaUnidad(orientacion) {
                if (unidadIdActual) {
                    const url = `/unidades/${unidadIdActual}/vista-previa?orientacion=${orientacion}`;
                    window.open(url, 'vistaPreviaUnidad', 'width=1200,height=800,scrollbars=yes,resizable=yes');
                    cerrarModalPreviaUnidad();
                }
            }

            function descargarWordUnidad(unidadId, orientacion) {
                const url = `/unidades/${unidadId}/previsualizar?orientacion=${orientacion}&descargar=1&raw=1`;
                const link = document.createElement('a');
                link.href = url;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '📥 Descarga iniciada',
                        text: 'El documento se está generando...',
                        icon: 'info',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            }

            // ========== SESIONES ==========
            let sesionIdActual = null;

            function abrirModalPreviaSesion(sesionId) {
                sesionIdActual = sesionId;
                document.getElementById('modalPreviaSesion').style.display = 'flex';
            }

            function cerrarModalPreviaSesion() {
                document.getElementById('modalPreviaSesion').style.display = 'none';
                sesionIdActual = null;
            }

            function abrirVistaPreviaSesion(orientacion) {
                if (sesionIdActual) {
                    const url = `/sesiones/${sesionIdActual}/vista-previa?orientacion=${orientacion}`;
                    window.open(url, 'vistaPreviaSesion', 'width=1200,height=800,scrollbars=yes,resizable=yes');
                    cerrarModalPreviaSesion();
                }
            }

            function descargarWordSesion(sesionId, orientacion) {
                const url = `/sesiones/${sesionId}/previsualizar?orientacion=${orientacion}`;
                const link = document.createElement('a');
                link.href = url;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '📥 Descarga iniciada',
                        text: 'El documento se está generando...',
                        icon: 'info',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            }

            // ========== FICHAS ==========
            let fichaIdActual = null;

            function abrirModalPreviaFicha(fichaId) {
                fichaIdActual = fichaId;
                document.getElementById('modalPreviaFicha').style.display = 'flex';
            }

            function cerrarModalPreviaFicha() {
                fichaIdActual = null;
                document.getElementById('modalPreviaFicha').style.display = 'none';
            }

            function abrirVistaPreviaFicha() {
                if (fichaIdActual) {
                    const url = `/fichas/${fichaIdActual}/preview`;
                    window.open(url, 'vistaPreviaFicha', 'width=1200,height=800,scrollbars=yes,resizable=yes');
                    cerrarModalPreviaFicha();
                }
            }

            // ========== EVENTOS GLOBALES ==========
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    cerrarModalPreviaUnidad();
                    cerrarModalPreviaSesion();
                    cerrarModalPreviaFicha();
                }
            });

            document.getElementById('modalPreviaUnidad')?.addEventListener('click', function(event) {
                if (event.target === this) cerrarModalPreviaUnidad();
            });

            document.getElementById('modalPreviaSesion')?.addEventListener('click', function(event) {
                if (event.target === this) cerrarModalPreviaSesion();
            });

            document.getElementById('modalPreviaFicha')?.addEventListener('click', function(event) {
                if (event.target === this) cerrarModalPreviaFicha();
            });
        </script>
    @endpush
</x-filament-panels::page>
