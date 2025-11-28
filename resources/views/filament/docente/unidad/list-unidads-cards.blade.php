<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header con título --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">📚 Mis Unidades Didácticas</h1>
                <p class="text-gray-600 dark:text-gray-400">Gestiona y organiza tus unidades de aprendizaje</p>
            </div>
        </div>


        {{-- Filtros de búsqueda --}}
        <div class="bg-white rounded-xl shadow-sm p-6 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        🔍 Buscar unidades
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="search" placeholder="Nombre, situación significativa..."
                            wire:model.live.debounce.300ms="search" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        📊 Estado
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="filterEstado">
                            <option value="">Todos los estados</option>
                            <option value="activa">🟢 Activas</option>
                            <option value="finalizada">🔴 Finalizadas</option>
                            <option value="proxima">🟡 Próximas</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>
        </div>

        {{-- Vista de tarjetas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" wire:loading.class="opacity-50">
            @forelse($this->getFilteredUnidades() as $unidad)
                <div
                    class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden group">
                    {{-- Estado y Grado --}}
                    <div
                        class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 border-b">
                        <div class="flex justify-between items-center">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $this->getEstadoColor($unidad) }}">
                                {{ $this->getEstadoTexto($unidad) }}
                            </span>
                            <span
                                class="bg-white dark:bg-gray-800 px-3 py-1 rounded-full text-sm font-medium text-gray-600 dark:text-gray-300 shadow-sm">
                                {{ $unidad->grado }}
                            </span>
                        </div>
                    </div>

                    {{-- Contenido principal --}}
                    <div class="p-6">
                        {{-- Título --}}
                        <h3
                            class="text-lg font-bold text-gray-900 dark:text-white mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
                            {{ $unidad->nombre }}
                        </h3>

                        {{-- Información clave --}}
                        <div class="space-y-3 mb-4">
                            {{-- Fechas --}}
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-calendar class="w-4 h-4 mr-2 text-green-500" />
                                    {{ $unidad->fecha_inicio->format('d/m/Y') }}
                                </div>
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-calendar class="w-4 h-4 mr-2 text-red-500" />
                                    {{ $unidad->fecha_fin->format('d/m/Y') }}
                                </div>
                            </div>

                            {{-- Duración --}}
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <x-heroicon-o-clock class="w-4 h-4 mr-2 text-blue-500" />
                                <span
                                    class="font-medium">{{ $unidad->fecha_inicio->diffInDays($unidad->fecha_fin) + 1 }}
                                    días de duración</span>
                            </div>

                            {{-- Profesores --}}
                            @if ($unidad->nombres_profesores)
                                <div class="flex items-start text-sm text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-users class="w-4 h-4 mr-2 mt-0.5 text-purple-500 flex-shrink-0" />
                                    <span class="line-clamp-2">{{ $unidad->nombres_profesores }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Preview de situación significativa --}}
                        @if ($unidad->situacion_significativa)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 mb-4">
                                <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3">
                                    {{ Str::limit($unidad->situacion_significativa, 120) }}
                                </p>
                            </div>
                        @endif

                        {{-- Botones de acción --}}
                        <div class="flex flex-wrap gap-2">
                            {{-- Botón principal: Editar --}}
                            <a href="{{ route('filament.docente.resources.unidads.edit', ['record' => $unidad->id]) }}"
                                class="flex-1 px-4 py-2 bg-primary-500 text-white rounded-md hover:bg-primary-600 transition">
                                <x-heroicon-o-pencil-square class="w-5 h-5 inline-block mr-2" />
                                Editar
                            </a>

                            {{-- Botón: Ver vista previa --}}
                            <x-filament::button color="info" size="sm" icon="heroicon-o-eye"
                                onclick="abrirModalPrevia({{ $unidad->id }})">
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
                                    <x-filament::dropdown.list.item onclick="abrirModalPrevia({{ $unidad->id }})"
                                        icon="heroicon-o-document-text">
                                        📄 Vista Previa
                                    </x-filament::dropdown.list.item>

                                    <x-filament::dropdown.list.item
                                        onclick="descargarWord({{ $unidad->id }}, 'vertical')"
                                        icon="heroicon-o-arrow-down-tray">
                                        💾 Descargar Word Vertical
                                    </x-filament::dropdown.list.item>

                                    <x-filament::dropdown.list.item
                                        onclick="descargarWord({{ $unidad->id }}, 'horizontal')"
                                        icon="heroicon-o-arrow-down-tray">
                                        💾 Descargar Word Horizontal
                                    </x-filament::dropdown.list.item>

                                    <x-filament::dropdown.list.item
                                        onclick="confirmarDuplicacion({{ $unidad->id }}, '{{ $unidad->nombre }}')"
                                        icon="heroicon-o-document-duplicate">
                                        📋 Duplicar
                                    </x-filament::dropdown.list.item>

                                    <x-filament::dropdown.list.item
                                        onclick="confirmarEliminacion({{ $unidad->id }}, '{{ $unidad->nombre }}')"
                                        icon="heroicon-o-trash" color="danger">
                                        🗑️ Eliminar
                                    </x-filament::dropdown.list.item>
                                </x-filament::dropdown.list>
                            </x-filament::dropdown>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Estado vacío --}}
                <div class="col-span-full">
                    <div
                        class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <div class="mx-auto w-24 h-24 mb-6 text-gray-400">
                            <svg fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            No hay unidades didácticas
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">
                            Comienza creando tu primera unidad didáctica para organizar tu planificación académica.
                        </p>
                        <x-filament::button href="{{ route('filament.docente.resources.unidads.create') }}"
                            icon="heroicon-o-plus" size="lg">
                            Crear Primera Unidad
                        </x-filament::button>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        @if ($this->getFilteredUnidades()->hasPages())
            <div class="flex justify-center">
                {{ $this->getFilteredUnidades()->links() }}
            </div>
        @endif
    </div>

    {{-- Modal personalizado para vista previa --}}
    <div id="modalPrevia"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 10px; padding: 30px; max-width: 500px; text-align: center;">
            <h3 style="margin-bottom: 20px; color: #0066cc;">📄 Vista Previa del Documento</h3>
            <p style="margin-bottom: 30px; color: #666;">Seleccione el formato para previsualizar:</p>

            <div style="display: flex; gap: 15px; justify-content: center; margin-bottom: 20px;">
                <button id="btnVertical" onclick="abrirVistaPrevia('vertical')"
                    style="background: #0066cc; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    📄 Vista Previa Vertical
                </button>

                <button id="btnHorizontal" onclick="abrirVistaPrevia('horizontal')"
                    style="background: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    📄 Vista Previa Horizontal
                </button>
            </div>

            <button onclick="cerrarModalPrevia()"
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

            .line-clamp-3 {
                overflow: hidden;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 3;
            }

            #modalPrevia button:hover {
                opacity: 0.9;
                transform: translateY(-1px);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            let unidadIdActual = null;

            function abrirModalPrevia(unidadId) {
                unidadIdActual = unidadId;
                document.getElementById('modalPrevia').style.display = 'flex';
            }

            function cerrarModalPrevia() {
                document.getElementById('modalPrevia').style.display = 'none';
                unidadIdActual = null;
            }

            function abrirVistaPrevia(orientacion) {
                if (unidadIdActual) {
                    const url = `/unidades/${unidadIdActual}/vista-previa?orientacion=${orientacion}`;
                    window.open(url, 'vistaPreviaUnidad', 'width=1200,height=800,scrollbars=yes,resizable=yes');
                    cerrarModalPrevia();
                }
            }

            // 🆕 FUNCIÓN PARA DESCARGAR WORD DIRECTAMENTE
            function descargarWord(unidadId, orientacion) {
                const url = `/unidades/${unidadId}/previsualizar?orientacion=${orientacion}&descargar=1&raw=1`;

                // Crear un enlace temporal para forzar la descarga
                const link = document.createElement('a');
                link.href = url;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                // Mostrar notificación de descarga
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

            // 🆕 FUNCIÓN PARA CONFIRMAR DUPLICACIÓN
            function confirmarDuplicacion(unidadId, nombreUnidad) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '📋 Duplicar Unidad',
                        html: `
                        <div style="text-align: left; padding: 20px;">
                            <p><strong>¿Estás seguro de que quieres duplicar esta unidad?</strong></p>
                            <br>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #0066cc;">
                                <p><strong>📚 Unidad:</strong> ${nombreUnidad}</p>
                                <p><strong>📋 Se creará:</strong> "${nombreUnidad} (Copia)"</p>
                                <p><strong>📊 Se incluirán:</strong> Todos los detalles curriculares</p>
                            </div>
                            <br>
                            <p style="color: #666; font-size: 14px;">
                                <i class="fas fa-info-circle"></i> 
                                La nueva unidad se creará como una copia exacta y podrás editarla inmediatamente.
                            </p>
                        </div>
                    `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-copy"></i> Sí, duplicar',
                        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                        confirmButtonColor: '#0066cc',
                        cancelButtonColor: '#6c757d',
                        reverseButtons: true,
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return new Promise((resolve) => {
                                @this.call('duplicateUnidad', unidadId).then(() => {
                                    resolve();
                                });
                            });
                        }
                    });
                } else {
                    // Fallback sin SweetAlert
                    if (confirm(`¿Estás seguro de que quieres duplicar "${nombreUnidad}"?`)) {
                        @this.call('duplicateUnidad', unidadId);
                    }
                }
            }

            // 🆕 FUNCIÓN PARA CONFIRMAR ELIMINACIÓN
            function confirmarEliminacion(unidadId, nombreUnidad) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '🗑️ Eliminar Unidad',
                        html: `
                        <div style="text-align: left; padding: 20px;">
                            <p><strong>⚠️ ¿Estás seguro de que quieres eliminar esta unidad?</strong></p>
                            <br>
                            <div style="background: #fef2f2; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545;">
                                <p><strong>📚 Unidad:</strong> ${nombreUnidad}</p>
                                <p><strong>❌ Se eliminará:</strong> La unidad y todos sus datos</p>
                                <p><strong>📊 Incluye:</strong> Detalles curriculares, competencias, etc.</p>
                            </div>
                            <br>
                            <p style="color: #dc3545; font-size: 14px; font-weight: bold;">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Esta acción no se puede deshacer.
                            </p>
                        </div>
                    `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
                        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        reverseButtons: true,
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return new Promise((resolve) => {
                                @this.call('deleteUnidad', unidadId).then(() => {
                                    resolve();
                                });
                            });
                        }
                    });
                } else {
                    // Fallback sin SweetAlert
                    if (confirm(
                        `⚠️ ¿Estás seguro de que quieres eliminar "${nombreUnidad}"? Esta acción no se puede deshacer.`)) {
                        @this.call('deleteUnidad', unidadId);
                    }
                }
            }

            // Cerrar modal con ESC
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    cerrarModalPrevia();
                }
            });

            // Cerrar modal al hacer clic fuera
            document.getElementById('modalPrevia').addEventListener('click', function(event) {
                if (event.target === this) {
                    cerrarModalPrevia();
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
