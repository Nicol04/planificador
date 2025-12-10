{{-- filepath: c:\xampp\htdocs\planificador-v2\resources\views\filament\docente\sesion\list-sesions-cards.blade.php --}}
<x-filament-panels::page>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <div class="space-y-6">
        {{-- Header con título --}}
        <div class="flex justify-between items-center">
            <div>
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

        {{-- Cards de sesiones - Grid de 2 columnas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5" wire:loading.class="opacity-50">
            @forelse($this->getFilteredSesiones() as $sesion)
            <div
                class="bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 dark:bg-gray-800/95 border border-gray-100 dark:border-gray-700/50 overflow-hidden group flex flex-row backdrop-blur-sm">

                {{-- Imagen del curso (columna izquierda) --}}
                <div class="relative w-2/5 min-h-[220px] overflow-hidden">
                    @php
                    $cursoImageUrl = $sesion->aulaCurso?->curso?->image_url;
                    $defaultImage = 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=400&auto=format&fit=crop';
                    $imageUrl = $cursoImageUrl
                    ? (str_starts_with($cursoImageUrl, 'http') ? $cursoImageUrl : asset('storage/' . $cursoImageUrl))
                    : $defaultImage;
                    @endphp
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $sesion->aulaCurso?->curso?->curso ?? 'Curso' }}"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 ease-out"
                        onerror="this.src='{{ $defaultImage }}'" />
                    
                    {{-- Overlay degradado superior con estado --}}
                    <div class="absolute top-0 left-0 right-0 bg-gradient-to-b from-black/60 via-black/20 to-transparent p-3">
                        @if ($sesion->public)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/90 text-white shadow-lg backdrop-blur-sm">
                            <span class="w-1.5 h-1.5 bg-white rounded-full mr-1.5 animate-pulse"></span>
                            Publicado
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-600/90 text-white shadow-lg backdrop-blur-sm">
                            <x-heroicon-s-lock-closed class="w-3 h-3 mr-1" />
                            Privado
                        </span>
                        @endif
                    </div>

                    {{-- Overlay degradado inferior con curso --}}
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent p-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                <x-heroicon-s-book-open class="w-4 h-4 text-white" />
                            </div>
                            <span class="text-white text-sm font-semibold line-clamp-2 leading-tight">
                                {{ $sesion->aulaCurso?->curso?->curso ?? 'Sin curso' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Contenido (columna derecha) --}}
                <div class="w-3/5 p-5 flex flex-col justify-between">
                    {{-- Encabezado con título --}}
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-300 leading-snug">
                            {{ $sesion->titulo }}
                        </h3>

                        {{-- Información clave con mejor diseño --}}
                        <div class="space-y-2.5 mb-4">
                            {{-- Fecha y tiempo en una fila --}}
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                                    <x-heroicon-s-calendar-days class="w-4 h-4" />
                                    <span class="text-xs font-medium">{{ \Carbon\Carbon::parse($sesion->fecha)->format('d M, Y') }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                    <x-heroicon-s-clock class="w-4 h-4" />
                                    <span class="text-xs font-medium">{{ is_numeric($sesion->tiempo_estimado) ? $sesion->tiempo_estimado . ' min' : $sesion->tiempo_estimado }}</span>
                                </div>
                            </div>

                            {{-- Docente --}}
                            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <div class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center flex-shrink-0">
                                    <x-heroicon-s-user class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" />
                                </div>
                                <span class="text-sm truncate">{{ $sesion->docente?->persona?->nombre ?? 'Sin asignar' }}</span>
                            </div>

                            {{-- Tema --}}
                            <div class="flex items-start gap-2 text-gray-600 dark:text-gray-400">
                                <div class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <x-heroicon-s-light-bulb class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" />
                                </div>
                                <span class="text-sm line-clamp-1">{{ $sesion->tema ?? 'Sin tema definido' }}</span>
                            </div>

                            {{-- Indicadores de recursos asociados --}}
                            @php
                                $hasFichas = $sesion->fichasAprendizaje()->exists();
                                $hasListas = $sesion->listasCotejos()->exists();
                            @endphp
                            @if($hasFichas || $hasListas)
                            <div class="flex items-center gap-2 flex-wrap pt-1">
                                @if($hasFichas)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-medium" title="Tiene fichas de aprendizaje">
                                    <x-heroicon-s-document-text class="w-3.5 h-3.5" />
                                    Ficha
                                </span>
                                @endif
                                @if($hasListas)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 text-xs font-medium" title="Tiene lista de cotejo">
                                    <x-heroicon-s-clipboard-document-check class="w-3.5 h-3.5" />
                                    Lista
                                </span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Botones de acción con diseño mejorado --}}
                    <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-700/50">
                        {{-- Botón Editar principal --}}
                        <a href="{{ route('filament.docente.resources.sesions.edit', ['record' => $sesion->id]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                            <x-heroicon-s-pencil-square class="w-4 h-4" />
                            Editar
                        </a>

                        {{-- Botón Ver --}}
                        <button onclick="abrirModalPreviaSesion({{ $sesion->id }})"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                            <x-heroicon-s-eye class="w-4 h-4" />
                            Ver
                        </button>

                        {{-- Botón publicar/quitar con mejor diseño --}}
                        @if ($sesion->public)
                        <button onclick="confirmarTogglePublicacionSesion({{ $sesion->id }}, '{{ addslashes($sesion->titulo) }}', true)"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-lg transition-all duration-200"
                            title="Quitar publicación">
                            <x-heroicon-s-lock-closed class="w-4 h-4" />
                            Quitar
                        </button>
                        @else
                        <button onclick="confirmarTogglePublicacionSesion({{ $sesion->id }}, '{{ addslashes($sesion->titulo) }}', false)"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-emerald-100 hover:bg-emerald-200 text-emerald-700 rounded-lg transition-all duration-200"
                            title="Publicar">
                            <x-heroicon-s-globe-alt class="w-4 h-4" />
                            Publicar
                        </button>
                        @endif

                        {{-- Menú dropdown mejorado --}}
                        <x-filament::dropdown placement="bottom-end">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center justify-center w-9 h-9 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-all duration-200">
                                    <x-heroicon-s-ellipsis-vertical class="w-5 h-5" />
                                </button>
                            </x-slot>

                            <x-filament::dropdown.list>
                                <x-filament::dropdown.list.item
                                    onclick="abrirModalPreviaSesion({{ $sesion->id }})"
                                    icon="heroicon-o-document-text">
                                    Vista Previa
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

                                @if ($hasLista)
                                <x-filament::dropdown.list.item
                                    onclick="abrirModalPreviaListas({{ $sesion->id }})"
                                    icon="heroicon-o-clipboard-document-list">
                                    Listas de cotejo
                                </x-filament::dropdown.list.item>
                                @endif

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

                                <x-filament::dropdown.list.item
                                    onclick="confirmarDuplicacionSesion({{ $sesion->id }}, '{{ addslashes($sesion->titulo) }}')"
                                    icon="heroicon-o-document-duplicate">
                                    Duplicar
                                </x-filament::dropdown.list.item>

                                <x-filament::dropdown.list.item
                                    onclick="confirmarEliminacionSesion({{ $sesion->id }}, '{{ addslashes($sesion->titulo) }}')"
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

            <button onclick="abrirVistaPreviaListasConOrientacion('horizontal')"
                style="background:#28a745; color:white; padding:10px 16px; border:none; border-radius:6px; cursor:pointer;">
                Horizontal
            </button>
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
                    @this.call('duplicateSesion', sesionId);
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
                            // PASAR SOLO EL ID (no objeto)
                            @this.call('deleteSesion', sesionId).then(() => {
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

        function confirmarTogglePublicacionSesion(sesionId, tituloSesion, isCurrentlyPublic) {
            if (typeof Swal !== 'undefined') {
                const title = isCurrentlyPublic ? '🔒 Quitar publicación' : '🌐 Publicar Sesión';
                const confirmText = isCurrentlyPublic ? '<i class="fas fa-times-circle"></i> Sí, quitar' :
                    '<i class="fas fa-globe"></i> Sí, publicar';
                const htmlMessage = isCurrentlyPublic ?
                    `<p>¿Deseas quitar la publicación de <strong>${tituloSesion}</strong>?</p>
                           <p class="text-muted">La sesión dejará de estar visible para el grupo docente.</p>` :
                    `<p>¿Deseas publicar la sesión <strong>${tituloSesion}</strong>?</p>
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
                            @this.call('togglePublicacion', sesionId).then(() => {
                                resolve();
                            }).catch(() => {
                                resolve();
                            });
                        });
                    }
                });
            } else {
                const ok = confirm(isCurrentlyPublic ?
                    `Quitar publicación de "${tituloSesion}"?` :
                    `Publicar "${tituloSesion}"?`);
                if (ok) {
                    @this.call('togglePublicacion', sesionId);
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