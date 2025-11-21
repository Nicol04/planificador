<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-600 dark:text-gray-400">Gestiona y organiza tus fichas de aprendizaje</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <form wire:submit.prevent>
                <div class="flex flex-col md:flex-row md:items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            🔍 Buscar por título
                        </label>
                        <input type="search"
                            class="block w-full rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2.5"
                            placeholder="Ej: Resolución de problemas..." wire:model.live.debounce.300ms="search" />
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Ordenar por
                        </label>
                        <select
                            class="block w-full rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2.5"
                            wire:model.live="orderBy">
                            <option value="created_desc">Más recientes</option>
                            <option value="created_asc">Más antiguas</option>
                            <option value="titulo_asc">Título A-Z</option>
                            <option value="titulo_desc">Título Z-A</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" wire:loading.class="opacity-50">
            @forelse($this->getFilteredFichas() as $ficha)
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden group">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
                            {{ $ficha->nombre }}
                        </h3>
                        <div class="space-y-3 mb-4">
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-calendar class="w-4 h-4 mr-2 text-green-500" />
                                    {{ \Carbon\Carbon::parse($ficha->created_at)->format('d/m/Y') }}
                                </div>
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-user class="w-4 h-4 mr-2 text-purple-500" />
                                    <span class="font-medium">
                                        {{
                                            $ficha->user?->persona
                                                ? explode(' ', $ficha->user->persona->nombre)[0] . ' ' . explode(' ', $ficha->user->persona->apellido)[0]
                                                : '-'
                                        }}
                                    </span>
                                </div>
                            </div>
                            @if($ficha->grado)
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <x-heroicon-o-academic-cap class="w-4 h-4 mr-2 text-indigo-500" />
                                <span class="font-medium">{{ $ficha->grado }}</span>
                            </div>
                            @endif
                        </div>
                        
                        {{-- Botones de acción --}}
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('filament.docente.resources.ficha-aprendizajes.edit', ['record' => $ficha->id]) }}"
                                class="flex-1 px-4 py-2 bg-primary-500 text-white rounded-md hover:bg-primary-600 transition">
                                <x-heroicon-o-pencil-square class="w-5 h-5 inline-block mr-2" />
                                Editar
                            </a>

                            {{-- Botón: Ver vista previa --}}
                            <x-filament::button color="info" size="sm" icon="heroicon-o-eye"
                                onclick="abrirModalPreviaFicha({{ $ficha->id }})">
                                Ver
                            </x-filament::button>

                            {{-- Botones táctiles rápidos --}}
                            <div class="flex items-center gap-2">
                                @if ($ficha->public)
                                    <x-filament::button color="warning" size="sm" icon="heroicon-o-lock-closed"
                                        class="px-3 py-2 rounded-md shadow-sm hover:shadow-md transition inline-flex items-center gap-2"
                                        onclick="confirmarTogglePublicacionFicha({{ $ficha->id }}, '{{ addslashes($ficha->nombre) }}', true)"
                                        aria-label="Quitar publicación">
                                        🔒
                                        <span class="ml-1">Quitar publicación</span>
                                    </x-filament::button>
                                @else
                                    <x-filament::button color="success" size="sm" icon="heroicon-o-globe-alt"
                                        class="px-3 py-2 rounded-md shadow-sm hover:shadow-md transition inline-flex items-center gap-2"
                                        onclick="confirmarTogglePublicacionFicha({{ $ficha->id }}, '{{ addslashes($ficha->nombre) }}', false)"
                                        aria-label="Publicar">
                                        🌐
                                        <span class="ml-1">Publicar</span>
                                    </x-filament::button>
                                @endif
                            </div>

                            {{-- Menú dropdown --}}
                            <x-filament::dropdown>
                                <x-slot name="trigger">
                                    <x-filament::button color="gray" size="sm"
                                        icon="heroicon-o-ellipsis-horizontal">
                                    </x-filament::button>
                                </x-slot>

                                <x-filament::dropdown.list>
                                    <x-filament::dropdown.list.item
                                        onclick="abrirModalPreviaFicha({{ $ficha->id }})"
                                        icon="heroicon-o-document-text">
                                        📄 Vista Previa
                                    </x-filament::dropdown.list.item>

                                    <x-filament::dropdown.list.item
                                        onclick="confirmarEliminacionFicha({{ $ficha->id }}, '{{ addslashes($ficha->nombre) }}')"
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
                            No hay fichas registradas
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">
                            Comienza creando tu primera ficha de aprendizaje.
                        </p>
                        <a href="{{ route('filament.docente.resources.ficha-aprendizajes.create') }}"
                            class="px-6 py-3 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition text-lg">
                            <x-heroicon-o-plus class="w-5 h-5 inline-block mr-2" />
                            Crear Ficha
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($this->getFilteredFichas()->hasPages())
            <div class="flex justify-center mt-6">
                {{ $this->getFilteredFichas()->links() }}
            </div>
        @endif
    </div>
    {{-- Modal para vista previa --}}
    <div id="modalPreviaFicha"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 10px; padding: 30px; max-width: 400px; text-align: center;">
            <h3 style="margin-bottom: 20px; color: #0066cc; font-size: 1.5rem; font-weight: bold;">📄 Vista Previa</h3>
            <p style="margin-bottom: 30px; color: #666;">¿Deseas abrir la vista previa de la ficha?</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button onclick="abrirVistaPreviaFicha()"
                    style="background: #0066cc; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    ✓ Abrir Vista Previa
                </button>
                <button onclick="cerrarModalPreviaFicha()"
                    style="background: #6c757d; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    ✕ Cancelar
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
        </style>
    @endpush

    @push('scripts')
        <script>
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

            function confirmarTogglePublicacionFicha(fichaId, nombreFicha, isCurrentlyPublic) {
                if (typeof Swal !== 'undefined') {
                    const title = isCurrentlyPublic ? '🔒 Quitar publicación' : '🌐 Publicar Ficha';
                    const confirmText = isCurrentlyPublic ? '<i class="fas fa-times-circle"></i> Sí, quitar' :
                        '<i class="fas fa-globe"></i> Sí, publicar';
                    const htmlMessage = isCurrentlyPublic ?
                        `<p>¿Deseas quitar la publicación de <strong>${nombreFicha}</strong>?</p>
                           <p class="text-muted">La ficha dejará de estar visible para el grupo docente.</p>` :
                        `<p>¿Deseas publicar la ficha <strong>${nombreFicha}</strong>?</p>
                           <p class="text-muted">Estará visible para el grupo docente de la institución.</p>`;

                    Swal.fire({
                        title: title,
                        html: htmlMessage,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: confirmText,
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#0066cc',
                        cancelButtonColor: '#6c757d',
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return new Promise((resolve) => {
                                @this.call('togglePublicacionFicha', fichaId).then(() => {
                                    resolve();
                                }).catch(() => {
                                    resolve();
                                });
                            });
                        }
                    });
                } else {
                    const ok = confirm(isCurrentlyPublic ?
                        `Quitar publicación de "${nombreFicha}"?` :
                        `Publicar "${nombreFicha}"?`);
                    if (ok) {
                        @this.call('togglePublicacionFicha', fichaId);
                    }
                }
            }

            function confirmarEliminacionFicha(fichaId, nombreFicha) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '🗑️ Eliminar Ficha',
                        html: `<div style="text-align: left; padding: 20px;">
                    <p><strong>⚠️ ¿Estás seguro de que quieres eliminar esta ficha?</strong></p>
                    <br>
                    <div style="background: #fef2f2; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545;">
                        <p><strong>📑 Ficha:</strong> ${nombreFicha}</p>
                        <p><strong>❌ Se eliminará:</strong> La ficha y todos sus datos</p>
                        <p><strong>📊 Incluye:</strong> Ejercicios asociados</p>
                    </div>
                    <br>
                    <p style="color: #dc3545; font-size: 14px; font-weight: bold;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Esta acción no se puede deshacer.
                    </p>
                </div>`,
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
                                @this.call('deleteFicha', fichaId).then(() => {
                                    resolve();
                                });
                            });
                        }
                    });
                } else {
                    if (confirm(
                            `⚠️ ¿Estás seguro de que quieres eliminar "${nombreFicha}"? Esta acción no se puede deshacer.`)) {
                        @this.call('deleteFicha', fichaId);
                    }
                }
            }

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    cerrarModalPreviaFicha();
                }
            });

            document.getElementById('modalPreviaFicha').addEventListener('click', function(event) {
                if (event.target === this) {
                    cerrarModalPreviaFicha();
                }
            });
        </script>
    @endpush
</x-filament-panels::page>