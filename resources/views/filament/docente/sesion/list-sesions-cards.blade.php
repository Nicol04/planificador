{{-- filepath: c:\xampp\htdocs\planificador-v2\resources\views\filament\docente\sesion\list-sesions-cards.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header con título --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">📅 Mis Sesiones</h1>
                <p class="text-gray-600 dark:text-gray-400">Gestiona y organiza tus sesiones de aprendizaje</p>
            </div>
        </div>

        {{-- Filtros de búsqueda y orden --}}
        <div class="bg-white rounded-xl shadow-sm p-6 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <form wire:submit.prevent>
                <div class="flex flex-col md:flex-row md:items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            🔍 Buscar por título
                        </label>
                        <input type="search"
                            class="block w-full rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2.5"
                            placeholder="Ej: Fracciones equivalentes..." wire:model.live.debounce.300ms="search" />
                    </div>
                    <div class="flex gap-2 flex-1">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                📅 Desde
                            </label>
                            <input type="date"
                                class="block w-full rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2.5"
                                wire:model.live="filterFechaDesde" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                📅 Hasta
                            </label>
                            <input type="date"
                                class="block w-full rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2.5"
                                wire:model.live="filterFechaHasta" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            📖 Curso
                        </label>
                        <div class="relative">
                            <select
                                class="pl-10 block w-full rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2.5"
                                wire:model.live="filterCurso">
                                <option value="">Todos los cursos</option>
                                @foreach($this->getCursos(true) as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Cards de sesiones --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" wire:loading.class="opacity-50">
            @forelse($this->getFilteredSesiones() as $sesion)
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden group">
                    <div class="p-6">
                        {{-- Título --}}
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
                            {{ $sesion->titulo }}
                        </h3>

                        {{-- Información clave --}}
                        <div class="space-y-3 mb-4">
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-calendar class="w-4 h-4 mr-2 text-green-500" />
                                    {{ \Carbon\Carbon::parse($sesion->fecha)->format('d/m/Y') }}
                                </div>
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-clock class="w-4 h-4 mr-2 text-blue-500" />
                                    {{ is_numeric($sesion->tiempo_estimado) ? $sesion->tiempo_estimado . ' min' : $sesion->tiempo_estimado }}
                                </div>
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <x-heroicon-o-book-open class="w-4 h-4 mr-2 text-indigo-500" />
                                <span class="font-medium">{{ $sesion->aulaCurso?->curso?->curso ?? '-' }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <x-heroicon-o-user class="w-4 h-4 mr-2 text-purple-500" />
                                <span class="font-medium">{{ $sesion->docente?->persona?->nombre ?? '-' }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <x-heroicon-o-light-bulb class="w-4 h-4 mr-2 text-yellow-500" />
                                <span class="font-medium">{{ $sesion->tema ?? '-' }}</span>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('filament.docente.resources.sesions.edit', ['record' => $sesion->id]) }}"
                                class="flex-1 px-4 py-2 bg-primary-500 text-white rounded-md hover:bg-primary-600 transition">
                                <x-heroicon-o-pencil-square class="w-5 h-5 inline-block mr-2" />
                                Editar
                            </a>

                            {{-- Botón: Ver vista previa --}}
                            <x-filament::button color="info" size="sm" icon="heroicon-o-eye"
                                onclick="abrirModalPreviaSesion({{ $sesion->id }})">
                                Ver
                            </x-filament::button>

                            {{-- Menú dropdown --}}
                            <x-filament::dropdown>
                                <x-slot name="trigger">
                                    <x-filament::button color="gray" size="sm"
                                        icon="heroicon-o-ellipsis-horizontal">
                                    </x-filament::button>
                                </x-slot>

                                <x-filament::dropdown.list>
                                    <x-filament::dropdown.list.item onclick="abrirModalPreviaSesion({{ $sesion->id }})"
                                        icon="heroicon-o-document-text">
                                        📄 Vista Previa
                                    </x-filament::dropdown.list.item>

                                    <x-filament::dropdown.list.item
                                        onclick="descargarWordSesion({{ $sesion->id }}, 'vertical')"
                                        icon="heroicon-o-arrow-down-tray">
                                        💾 Descargar Word Vertical
                                    </x-filament::dropdown.list.item>

                                    <x-filament::dropdown.list.item
                                        onclick="descargarWordSesion({{ $sesion->id }}, 'horizontal')"
                                        icon="heroicon-o-arrow-down-tray">
                                        💾 Descargar Word Horizontal
                                    </x-filament::dropdown.list.item>

                                    <x-filament::dropdown.list.item
                                        onclick="confirmarDuplicacionSesion({{ $sesion->id }}, '{{ $sesion->titulo }}')"
                                        icon="heroicon-o-document-duplicate">
                                        📋 Duplicar
                                    </x-filament::dropdown.list.item>

                                    <x-filament::dropdown.list.item
                                        onclick="confirmarEliminacionSesion({{ $sesion->id }}, '{{ $sesion->titulo }}')"
                                        icon="heroicon-o-trash" color="danger">
                                        🗑️ Eliminar
                                    </x-filament::dropdown.list.item>
                                </x-filament::dropdown.list>
                            </x-filament::dropdown>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <div class="mx-auto w-24 h-24 mb-6 text-gray-400">
                            <svg fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            No hay sesiones registradas
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">
                            Comienza creando tu primera sesión para organizar tu clase.
                        </p>
                        <a href="{{ route('filament.docente.resources.sesions.create') }}"
                           class="px-6 py-3 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition text-lg">
                            <x-heroicon-o-plus class="w-5 h-5 inline-block mr-2" />
                            Crear Sesión
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($this->getFilteredSesiones()->hasPages())
            <div class="flex justify-center mt-6">
                {{ $this->getFilteredSesiones()->links() }}
            </div>
        @endif
    </div>

    {{-- Modal personalizado para vista previa de sesión --}}
    <div id="modalPreviaSesion"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 10px; padding: 30px; max-width: 500px; text-align: center;">
            <h3 style="margin-bottom: 20px; color: #0066cc;">📄 Vista Previa de la Sesión</h3>
            <p style="margin-bottom: 30px; color: #666;">(Función pendiente de implementar)</p>
            <button onclick="cerrarModalPreviaSesion()"
                style="background: #6c757d; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer;">
                ❌ Cancelar
            </button>
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
        </style>
    @endpush

    @push('scripts')
        <script>
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

            function confirmarDuplicacionSesion(sesionId, tituloSesion) {
                // Función pendiente de implementar
            }

            function confirmarEliminacionSesion(sesionId, tituloSesion) {
                // Función pendiente de implementar
            }

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    cerrarModalPreviaSesion();
                }
            });

            document.getElementById('modalPreviaSesion').addEventListener('click', function(event) {
                if (event.target === this) {
                    cerrarModalPreviaSesion();
                }
            });
        </script>
    @endpush
</x-filament-panels::page>