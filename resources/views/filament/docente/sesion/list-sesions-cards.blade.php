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
                                @foreach ($this->getCursos(true) as $id => $nombre)
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
                <div
                    class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden group">
                    <div class="p-6">
                        {{-- Título --}}
                        <h3
                            class="text-lg font-bold text-gray-900 dark:text-white mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
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
                                    <x-filament::dropdown.list.item
                                        onclick="abrirModalPreviaSesion({{ $sesion->id }})"
                                        icon="heroicon-o-document-text">
                                        📄 Vista Previa
                                    </x-filament::dropdown.list.item>

                                    @php
                                        // Determinar si la sesión tiene lista de cotejo:
                                        $hasLista = false;
                                        try {
                                            if (
                                                method_exists($sesion, 'listasCotejos') &&
                                                $sesion->listasCotejos()->exists()
                                            ) {
                                                $hasLista = true;
                                            } else {
                                                $detalleTmp = $sesion->detalle ?? null;
                                                if (
                                                    !empty($detalleTmp->propositos_aprendizaje) &&
                                                    is_array($detalleTmp->propositos_aprendizaje)
                                                ) {
                                                    foreach ($detalleTmp->propositos_aprendizaje as $propTmp) {
                                                        $instPreTmp = $propTmp['instrumentos_predefinidos'] ?? null;
                                                        $instTmp = $propTmp['instrumentos'] ?? null;
                                                        if (
                                                            is_array($instPreTmp) &&
                                                            in_array('Lista de cotejo', $instPreTmp, true)
                                                        ) {
                                                            $hasLista = true;
                                                            break;
                                                        }
                                                        if (
                                                            is_string($instPreTmp) &&
                                                            stripos($instPreTmp, 'lista de cotejo') !== false
                                                        ) {
                                                            $hasLista = true;
                                                            break;
                                                        }
                                                        if (
                                                            is_array($instTmp) &&
                                                            in_array('Lista de cotejo', $instTmp, true)
                                                        ) {
                                                            $hasLista = true;
                                                            break;
                                                        }
                                                        if (
                                                            is_string($instTmp) &&
                                                            stripos($instTmp, 'lista de cotejo') !== false
                                                        ) {
                                                            $hasLista = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        } catch (\Throwable $e) {
                                            $hasLista = false;
                                        }
                                    @endphp

                                    <!-- Mostrar solo si existe lista de cotejo -->
                                    @if ($hasLista)
                                        <x-filament::dropdown.list.item
                                            onclick="abrirModalPreviaListas({{ $sesion->id }})"
                                            icon="heroicon-o-document-text">
                                            📄 Listas de cotejo
                                        </x-filament::dropdown.list.item>
                                    @endif

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
                                        onclick="confirmarDuplicacionSesion({{ $sesion->id }}, '{{ addslashes($sesion->titulo) }}')"
                                        icon="heroicon-o-document-duplicate">
                                        📋 Duplicar
                                    </x-filament::dropdown.list.item>

                                    <x-filament::dropdown.list.item
                                        onclick="confirmarEliminacionSesion({{ $sesion->id }}, '{{ addslashes($sesion->titulo) }}')"
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
                    <div
                        class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <div class="mx-auto w-24 h-24 mb-6 text-gray-400">
                            <svg fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
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
            <p style="margin-bottom: 30px; color: #666;">Seleccione el formato para previsualizar:</p>
            <div style="display: flex; gap: 15px; justify-content: center; margin-bottom: 20px;">
                <button onclick="abrirVistaPreviaSesion('vertical')"
                    style="background: #0066cc; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    📄 Vista Previa Vertical
                </button>
                <button onclick="abrirVistaPreviaSesion('horizontal')"
                    style="background: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    📄 Vista Previa Horizontal
                </button>
            </div>
            <button onclick="cerrarModalPreviaSesion()"
                style="background: #6c757d; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer;">
                ❌ Cancelar
            </button>
        </div>
    </div>

    {{-- Modal personalizado para vista previa de lista de cotejo --}}
    <div id="modalPreviaListas"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 10px; padding: 24px; max-width: 420px; text-align: center;">
            <h3 style="margin-bottom: 16px; color: #0066cc;">📄 Vista previa - Lista de cotejo</h3>
            <p style="margin-bottom: 18px; color: #666;">Seleccione la orientación para previsualizar la lista:</p>
            <div style="display:flex; gap:12px; justify-content:center; margin-bottom:18px;">
                <button onclick="abrirVistaPreviaListasConOrientacion('vertical')"
                    style="background:#0066cc; color:white; padding:10px 16px; border:none; border-radius:6px; cursor:pointer;">
                    Vertical
                </button>
                <button onclick="abrirVistaPreviaListasConOrientacion('horizontal')"
                    style="background:#28a745; color:white; padding:10px 16px; border:none; border-radius:6px; cursor:pointer;">
                    Horizontal
                </button>
            </div>
            <div style="display:flex; gap:10px; justify-content:center; margin-bottom:8px;">
                <button onclick="imprimirDesdeModalListas()"
                    style="background:#17a2b8; color:white; padding:8px 12px; border:none; border-radius:6px; cursor:pointer;">
                    🖨️ Imprimir
                </button>
                <button onclick="cerrarModalPreviaListas()"
                    style="background:#6c757d; color:white; padding:8px 12px; border:none; border-radius:6px; cursor:pointer;">
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
        </style>
    @endpush

    @push('scripts')
        <script>
            let sesionIdActual = null;
            let sesionIdListasActual = null;

            function abrirModalPreviaSesion(sesionId) {
                sesionIdActual = sesionId;
                document.getElementById('modalPreviaSesion').style.display = 'flex';
            }

            // Modal para listas de cotejo
            function abrirModalPreviaListas(sesionId) {
                sesionIdListasActual = sesionId;
                document.getElementById('modalPreviaListas').style.display = 'flex';
            }

            function cerrarModalPreviaListas() {
                sesionIdListasActual = null;
                document.getElementById('modalPreviaListas').style.display = 'none';
            }

            // Abrir vista previa con orientación (desde modal)
            function abrirVistaPreviaListasConOrientacion(orientacion) {
                if (!sesionIdListasActual) return;
                const url = `/listas-cotejo/${sesionIdListasActual}/vista-previa?orientacion=${orientacion}`;
                window.open(url, 'vistaPreviaListas', 'width=1100,height=800,scrollbars=yes,resizable=yes');
                cerrarModalPreviaListas();
            }

            // Imprimir directamente (abre vista con autoPrint)
            function imprimirDesdeModalListas() {
                if (!sesionIdListasActual) return;
                const url = `/listas-cotejo/${sesionIdListasActual}/vista-previa?autoPrint=1`;
                window.open(url, 'vistaPreviaListasPrint', 'width=1100,height=800,scrollbars=yes,resizable=yes');
                cerrarModalPreviaListas();
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
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '📋 Duplicar Sesión',
                        html: `<div style="text-align: left; padding: 20px;">
                    <p><strong>¿Estás seguro de que quieres duplicar esta sesión?</strong></p>
                    <br>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #0066cc;">
                        <p><strong>📅 Sesión:</strong> ${tituloSesion}</p>
                        <p><strong>📋 Se creará:</strong> "${tituloSesion} (Copia)"</p>
                        <p><strong>📊 Se incluirán:</strong> Todos los detalles curriculares</p>
                    </div>
                    <br>
                    <p style="color: #666; font-size: 14px;">
                        <i class="fas fa-info-circle"></i> 
                        La nueva sesión se creará como una copia exacta y podrás editarla inmediatamente.
                    </p>
                </div>`,
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
                                // Pasar un objeto con la clave sesion_id
                                @this.call('duplicateSesion', {
                                    sesion_id: sesionId
                                }).then(() => {
                                    resolve();
                                });
                            });
                        }
                    });
                } else {
                    if (confirm(`¿Estás seguro de que quieres duplicar "${tituloSesion}"?`)) {
                        @this.call('duplicateSesion', {
                            sesion_id: sesionId
                        });
                    }
                }
            }

            function confirmarEliminacionSesion(sesionId, tituloSesion) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '🗑️ Eliminar Sesión',
                        html: `<div style="text-align: left; padding: 20px;">
                    <p><strong>⚠️ ¿Estás seguro de que quieres eliminar esta sesión?</strong></p>
                    <br>
                    <div style="background: #fef2f2; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545;">
                        <p><strong>📅 Sesión:</strong> ${tituloSesion}</p>
                        <p><strong>❌ Se eliminará:</strong> La sesión y todos sus datos</p>
                        <p><strong>📊 Incluye:</strong> Detalles curriculares, competencias, etc.</p>
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
                                // Pasar un objeto con la clave sesion_id
                                @this.call('deleteSesion', {
                                    sesion_id: sesionId
                                }).then(() => {
                                    resolve();
                                });
                            });
                        }
                    });
                } else {
                    if (confirm(
                            `⚠️ ¿Estás seguro de que quieres eliminar "${tituloSesion}"? Esta acción no se puede deshacer.`)) {
                        @this.call('deleteSesion', {
                            sesion_id: sesionId
                        });
                    }
                }
            }

            // Reemplazamos la función previa de abrirVistaPreviaListas (si existía) para permitir uso directo con 1 parámetro
            function abrirVistaPreviaListas(sesionId) {
                const url = `/listas-cotejo/${sesionId}/vista-previa`;
                window.open(url, 'vistaPreviaListas', 'width=1100,height=800,scrollbars=yes,resizable=yes');
            }

            function imprimirVistaPreviaListas(sesionId) {
                // Abre la vista previa y solicita impresión automática
                const url = `/listas-cotejo/${sesionId}/vista-previa?autoPrint=1`;
                window.open(url, 'vistaPreviaListasPrint', 'width=1100,height=800,scrollbars=yes,resizable=yes');
            }

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    cerrarModalPreviaSesion();
                    cerrarModalPreviaListas();
                }
            });

            document.getElementById('modalPreviaSesion').addEventListener('click', function(event) {
                if (event.target === this) {
                    cerrarModalPreviaSesion();
                }
            });
            document.getElementById('modalPreviaListas').addEventListener('click', function(event) {
                if (event.target === this) {
                    cerrarModalPreviaListas();
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
