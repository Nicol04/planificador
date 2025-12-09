<x-filament-panels::page>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
                @php
                    // Mapeo de tipo_ejercicio a icono y color
                    $tipoIconos = [
                        'SelectionExercise' => [
                            'icon' => 'SelectionExercise.png',
                            'label' => 'Selección',
                            'color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                        ],
                        'ClassificationExercise' => [
                            'icon' => 'seleccionar.png',
                            'label' => 'Clasificación',
                            'color' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                        ],
                        'ClozeExercise' => [
                            'icon' => 'ClozeExercise.png',
                            'label' => 'Completar',
                            'color' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                        ],
                        'ReflectionExercise' => [
                            'icon' => 'ReflectionExercise.png',
                            'label' => 'Reflexión',
                            'color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                        ],
                    ];
                    $tipoActual = $ficha->tipo_ejercicio ?? null;
                    $esTodos = $tipoActual === 'Todos' || $tipoActual === 'todos';
                    $tipoInfo = !$esTodos ? $tipoIconos[$tipoActual] ?? null : null;
                @endphp
                <div
                    class="bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 dark:bg-gray-800/95 border border-gray-100 dark:border-gray-700/50 overflow-hidden group flex flex-col">

                    {{-- Header con badge de tipo de ejercicio --}}
                    <div class="relative bg-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            {{-- Badge de estado público/privado --}}
                            @if ($ficha->public)
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/90 text-white shadow-lg backdrop-blur-sm">
                                    <span class="w-1.5 h-1.5 bg-white rounded-full mr-1.5 animate-pulse"></span>
                                    Publicado
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-600/90 text-white shadow-lg backdrop-blur-sm">
                                    <x-heroicon-s-lock-closed class="w-3 h-3 mr-1" />
                                    Privado
                                </span>
                            @endif

                            {{-- Iconos del tipo de ejercicio --}}
                            @if ($esTodos)
                                {{-- Mostrar todos los iconos cuando es "Todos" --}}
                                <div class="flex items-center gap-1">
                                    @foreach ($tipoIconos as $key => $info)
                                        <div class="w-9 h-9 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-md"
                                            title="{{ $info['label'] }}">
                                            <img src="{{ asset('assets/img/icons/' . $info['icon']) }}"
                                                alt="{{ $info['label'] }}" class="w-6 h-6 object-contain">
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($tipoInfo)
                                {{-- Mostrar solo el icono correspondiente --}}
                                <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg"
                                    title="{{ $tipoInfo['label'] }}">
                                    <img src="{{ asset('assets/img/icons/' . $tipoInfo['icon']) }}"
                                        alt="{{ $tipoInfo['label'] }}" class="w-8 h-8 object-contain">
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Contenido principal --}}
                    <div class="p-5 flex flex-col flex-grow">
                        {{-- Título --}}
                        <h3
                            class="text-lg font-bold text-gray-900 dark:text-white mb-3 line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-300 leading-snug">
                            {{ $ficha->nombre }}
                        </h3>

                        {{-- Información clave --}}
                        <div class="space-y-2.5 mb-4 flex-grow">
                            {{-- Fecha y usuario en una fila --}}
                            <div class="flex items-center gap-3 flex-wrap">
                                <div
                                    class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                                    <x-heroicon-s-calendar-days class="w-4 h-4" />
                                    <span
                                        class="text-xs font-medium">{{ \Carbon\Carbon::parse($ficha->created_at)->format('d M, Y') }}</span>
                                </div>
                                <div
                                    class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                                    <x-heroicon-s-user class="w-4 h-4" />
                                    <span class="text-xs font-medium truncate max-w-[100px]">
                                        {{ $ficha->user?->persona ? explode(' ', $ficha->user->persona->nombre)[0] : '-' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Grado --}}
                            @if ($ficha->grado)
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                    <div
                                        class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-s-academic-cap
                                            class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <span class="text-sm">{{ $ficha->grado }}° de primaria</span>
                                </div>
                            @endif

                            {{-- Tipo de ejercicio con icono --}}
                            @if ($esTodos)
                                {{-- Mostrar badge "Todos" con todos los iconos --}}
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-semibold">
                                        <x-heroicon-s-squares-2x2 class="w-4 h-4" />
                                        Todos los tipos
                                    </span>
                                    <div class="flex items-center gap-1">
                                        @foreach ($tipoIconos as $key => $info)
                                            <img src="{{ asset('assets/img/icons/' . $info['icon']) }}"
                                                alt="{{ $info['label'] }}" title="{{ $info['label'] }}"
                                                class="w-5 h-5 object-contain opacity-80 hover:opacity-100 transition-opacity">
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($tipoInfo)
                                <div class="flex items-center gap-2">
                                    <div
                                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg {{ $tipoInfo['color'] }}">
                                        <img src="{{ asset('assets/img/icons/' . $tipoInfo['icon']) }}"
                                            alt="{{ $tipoInfo['label'] }}" class="w-5 h-5 object-contain">
                                        <span class="text-xs font-semibold">{{ $tipoInfo['label'] }}</span>
                                    </div>
                                </div>
                            @endif

                            {{-- Cantidad de ejercicios --}}
                            @php
                                $cantEjercicios = $ficha->ejercicios()->count();
                            @endphp
                            @if ($cantEjercicios > 0)
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                    <div
                                        class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-s-puzzle-piece
                                            class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <span class="text-sm">{{ $cantEjercicios }}
                                        ejercicio{{ $cantEjercicios > 1 ? 's' : '' }}</span>
                                </div>
                            @endif

                            {{-- Sesión asociada --}}
                            @php
                                $sesionAsociada = $ficha->fichaSesiones->first()?->sesion;
                            @endphp
                            @if ($sesionAsociada)
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                    <div
                                        class="w-6 h-6 rounded-full bg-cyan-100 dark:bg-cyan-900/40 flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-s-bookmark class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400" />
                                    </div>
                                    <span class="text-sm truncate" title="{{ $sesionAsociada->titulo }}">
                                        Sesión: {{ Str::limit($sesionAsociada->titulo, 25) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Botones de acción --}}
                        <div
                            class="flex flex-wrap items-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-700/50">
                            {{-- Botón Editar --}}
                            <a href="{{ route('filament.docente.resources.ficha-aprendizajes.edit', ['record' => $ficha->id]) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                <x-heroicon-s-pencil-square class="w-4 h-4" />
                                Editar
                            </a>

                            {{-- Botón Ver --}}
                            <button onclick="abrirModalPreviaFicha({{ $ficha->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                <x-heroicon-s-eye class="w-4 h-4" />
                                Ver
                            </button>

                            {{-- Botón publicar/quitar --}}
                            @if ($ficha->public)
                                <button
                                    onclick="confirmarTogglePublicacionFicha({{ $ficha->id }}, '{{ addslashes($ficha->nombre) }}', true)"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-lg transition-all duration-200"
                                    title="Quitar publicación">
                                    <x-heroicon-s-lock-closed class="w-4 h-4" />
                                    Quitar
                                </button>
                            @else
                                <button
                                    onclick="confirmarTogglePublicacionFicha({{ $ficha->id }}, '{{ addslashes($ficha->nombre) }}', false)"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-emerald-100 hover:bg-emerald-200 text-emerald-700 rounded-lg transition-all duration-200"
                                    title="Publicar">
                                    <x-heroicon-s-globe-alt class="w-4 h-4" />
                                    Publicar
                                </button>
                            @endif

                            {{-- Menú dropdown --}}
                            <x-filament::dropdown placement="bottom-end">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center justify-center w-9 h-9 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-all duration-200">
                                        <x-heroicon-s-ellipsis-vertical class="w-5 h-5" />
                                    </button>
                                </x-slot>

                                <x-filament::dropdown.list>
                                    <x-filament::dropdown.list.item
                                        onclick="abrirModalPreviaFicha({{ $ficha->id }})"
                                        icon="heroicon-o-document-text">
                                        Vista Previa
                                    </x-filament::dropdown.list.item>

                                    <x-filament::dropdown.list.item
                                        onclick="abrirModalCambiarNombreFicha({{ $ficha->id }}, '{{ addslashes($ficha->nombre) }}')"
                                        icon="heroicon-o-pencil">
                                        Cambiar nombre
                                    </x-filament::dropdown.list.item>

                                    <x-filament::dropdown.list.item
                                        onclick="confirmarEliminacionFicha({{ $ficha->id }}, '{{ addslashes($ficha->nombre) }}')"
                                        icon="heroicon-o-trash" color="danger">
                                        Eliminar
                                    </x-filament::dropdown.list.item>
                                </x-filament::dropdown.list>
                            </x-filament::dropdown>
                        </div>
                    </div>
                </div>
            @empty
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

    {{-- Modal para cambiar nombre --}}
    <div id="modalCambiarNombreFicha"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 10px; padding: 30px; max-width: 400px; text-align: center;">
            <h3 style="margin-bottom: 20px; color: #0066cc; font-size: 1.5rem; font-weight: bold;">✏️ Cambiar nombre de
                la ficha</h3>
            <input id="nuevoNombreFichaInput" type="text"
                style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 20px;" />
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button onclick="guardarNuevoNombreFicha()"
                    style="background: #0066cc; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    ✓ Guardar
                </button>
                <button onclick="cerrarModalCambiarNombreFicha()"
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

            // Cambiar nombre ficha
            let fichaIdCambiarNombre = null;

            function abrirModalCambiarNombreFicha(fichaId, nombreActual) {
                fichaIdCambiarNombre = fichaId;
                document.getElementById('nuevoNombreFichaInput').value = nombreActual;
                document.getElementById('modalCambiarNombreFicha').style.display = 'flex';
                setTimeout(() => {
                    document.getElementById('nuevoNombreFichaInput').focus();
                }, 100);
            }

            function cerrarModalCambiarNombreFicha() {
                fichaIdCambiarNombre = null;
                document.getElementById('modalCambiarNombreFicha').style.display = 'none';
            }

            function guardarNuevoNombreFicha() {
                const nuevoNombre = document.getElementById('nuevoNombreFichaInput').value.trim();
                if (!nuevoNombre) {
                    alert('El nombre no puede estar vacío.');
                    return;
                }
                @this.call('cambiarNombreFicha', fichaIdCambiarNombre, nuevoNombre).then(() => {
                    cerrarModalCambiarNombreFicha();
                });
            }

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    cerrarModalPreviaFicha();
                    cerrarModalCambiarNombreFicha();
                }
            });

            document.getElementById('modalPreviaFicha').addEventListener('click', function(event) {
                if (event.target === this) {
                    cerrarModalPreviaFicha();
                }
            });
            document.getElementById('modalCambiarNombreFicha').addEventListener('click', function(event) {
                if (event.target === this) {
                    cerrarModalCambiarNombreFicha();
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
