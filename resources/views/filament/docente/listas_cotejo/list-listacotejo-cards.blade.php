<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">📋 Listas de cotejo</h1>
                <p class="text-gray-600 dark:text-gray-400">Gestiona tus listas vinculadas a sesiones</p>
            </div>

            <div class="flex items-center gap-4">
                <div class="w-72">
                    <input type="search"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Buscar por título" wire:model.live.debounce.300ms="search">
                </div>
            </div>
        </div>

        {{-- Grid de tarjetas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" wire:loading.class="opacity-50">
            @forelse($this->getFilteredListas() as $lista)
                <div class="bg-white rounded-xl shadow-sm p-5 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    @php
                        // Normalizar datos usados en la tarjeta (niveles y propositos)
                        $creado = optional($lista->created_at)->format('d/m/Y');
                        $detalle = $lista->sesion?->detalle ?? null;
                        $propositos = $detalle?->propositos_aprendizaje ?? [];
                        if (is_string($propositos)) {
                            $decoded = json_decode($propositos, true);
                            $propositos = is_array($decoded) ? $decoded : [];
                        }
                        $nivelesRaw = $lista->niveles;
                        $niveles = [];
                        if (is_string($nivelesRaw)) {
                            $decodedNiv = json_decode($nivelesRaw, true);
                            $niveles = is_array($decodedNiv) ? $decodedNiv : array_filter(array_map('trim', explode(',', $nivelesRaw)));
                        } elseif (is_array($nivelesRaw)) {
                            $niveles = $nivelesRaw;
                        }
                    @endphp

                    {{-- Niveles arriba del card con círculos de color --}}
                    @if(!empty($niveles))
                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach($niveles as $n)
                                @php
                                    $label = trim($n);
                                    $lc = strtolower(str_replace(['_', '-'], ' ', $label));
                                    // Calcular color HEX para usar inline (garantiza visibilidad)
                                    if ($lc === 'logrado') { $dotColor = '#16a34a'; }          // verde
                                    elseif (strpos($lc, 'en proceso') !== false || strpos($lc, 'enproceso') !== false) { $dotColor = '#f59e0b'; } // amarillo
                                    elseif (strpos($lc, 'no logrado') !== false || strpos($lc, 'nologrado') !== false) { $dotColor = '#ef4444'; } // rojo
                                    else { $dotColor = '#9ca3af'; } // gris por defecto
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 bg-white dark:bg-gray-800 rounded-full border border-gray-200 dark:border-gray-700 text-xs">
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:9999px;margin-right:8px;background-color: {{ $dotColor }};border:1px solid rgba(0,0,0,0.08);"></span>
                                    {{ $label }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                        {{ $lista->titulo }}
                    </h3>

                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-3">
                        {{ $lista->descripcion ?? 'Sin descripción' }}
                    </p>

                    {{-- Bloque modificado: información de sesión, competencias y niveles --}}
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                        <div>📅 {{ $creado }}</div>
                        <div>🔗 Sesión: {{ $lista->sesion?->titulo ?? '-' }}</div>

                        {{-- Mostrar solo el nombre de la competencia --}}
                        @if(!empty($propositos))
                            <div class="mt-2">
                                <div class="font-medium text-gray-700 dark:text-gray-300">🎯 Competencias</div>
                                @foreach($propositos as $p)
                                    @php
                                        $compId = $p['competencia_id'] ?? null;
                                        $compNombre = null;
                                        if ($compId) {
                                            $compNombre = \App\Models\Competencia::find($compId)?->nombre ?? null;
                                        }
                                    @endphp

                                    @if($compNombre)
                                        <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-100 dark:border-gray-600 text-sm font-medium">
                                            {{ $compNombre }}
                                        </div>
                                    @elseif($compId)
                                        <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-100 dark:border-gray-600 text-sm font-medium">
                                            Competencia {{ $compId }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    {{-- /Bloque modificado --}}

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('filament.docente.resources.lista-cotejos.edit', ['record' => $lista->id]) }}"
                            class="flex-1 px-3 py-2 bg-primary-500 text-white rounded-md text-sm hover:bg-primary-600">
                            <x-heroicon-o-pencil-square class="w-4 h-4 inline mr-1" /> Editar
                        </a>

                        <x-filament::button color="gray" size="sm" icon="heroicon-o-eye"
                            onclick="abrirVistaPreviaListasUrl('{{ route('listas-cotejo.vista.previa', ['id' => $lista->sesion_id]) }}')">
                            Vista Previa
                        </x-filament::button>

                        {{-- Dropdown con opciones adicionales --}}
                        <x-filament::dropdown placement="bottom-end">
                            <x-slot name="trigger">
                                <x-filament::button color="gray" size="sm" icon="heroicon-o-ellipsis-horizontal"></x-filament::button>
                            </x-slot>

                            <x-filament::dropdown.list>
                                <x-filament::dropdown.list.item onclick="abrirVistaPreviaListas({{ $lista->sesion_id }})" icon="heroicon-o-document-text">
                                    📄 Vista Previa
                                </x-filament::dropdown.list.item>

                                <x-filament::dropdown.list.item
                                    onclick="(function(){ const u='{{ route('listas-cotejo.previsualizar', ['id' => $lista->id]) }}?orientacion=vertical'; window.open(u,'_blank'); })()"
                                    icon="heroicon-o-arrow-down-tray">
                                    💾 Descargar Word Vertical
                                </x-filament::dropdown.list.item>

                                <x-filament::dropdown.list.item
                                    onclick="(function(){ const u='{{ route('listas-cotejo.previsualizar', ['id' => $lista->id]) }}?orientacion=horizontal'; window.open(u,'_blank'); })()"
                                    icon="heroicon-o-arrow-down-tray">
                                    💾 Descargar Word Horizontal
                                </x-filament::dropdown.list.item>

                                <x-filament::dropdown.list.item
                                    href="{{ route('filament.docente.resources.lista-cotejos.edit', ['record' => $lista->id]) }}"
                                    icon="heroicon-o-pencil-square">
                                    ✏️ Editar
                                </x-filament::dropdown.list.item>
                            </x-filament::dropdown.list>
                        </x-filament::dropdown>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No hay listas de cotejo</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">Crea una nueva lista asociada a una sesión.</p>

                        {{-- Usar la misma ruta de crear que en header; ajusta si tu nombre de ruta es distinto --}}
                        <a href="{{ route('filament.docente.resources.lista-cotejos.create') }}" class="px-6 py-3 bg-primary-500 text-white rounded-lg hover:bg-primary-600">
                            <x-heroicon-o-plus class="w-5 h-5 inline-block mr-2" /> Crear lista
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($this->getFilteredListas()->hasPages())
            <div class="flex justify-center mt-6">
                {{ $this->getFilteredListas()->links() }}
            </div>
        @endif
    </div>

    {{-- Modal personalizado para vista previa de lista de cotejo --}}
    <div id="modalPreviaListas" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
        <div style="background: white; border-radius:10px; padding:24px; max-width:480px; text-align:center;">
            <h3 style="margin-bottom:16px; color:#0066cc;">📄 Vista previa - Lista de cotejo</h3>
            <p style="margin-bottom:18px; color:#666;">Seleccione la orientación para previsualizar la lista:</p>
            <div style="display:flex; gap:12px; justify-content:center; margin-bottom:18px;">
                <button onclick="abrirVistaPreviaListasConOrientacion('vertical')" style="background:#0066cc; color:white; padding:10px 16px; border:none; border-radius:6px; cursor:pointer;">Vertical</button>
                <button onclick="abrirVistaPreviaListasConOrientacion('horizontal')" style="background:#28a745; color:white; padding:10px 16px; border:none; border-radius:6px; cursor:pointer;">Horizontal</button>
            </div>
            <div style="display:flex; gap:10px; justify-content:center; margin-bottom:8px;">
                <button onclick="imprimirDesdeModalListas()" style="background:#17a2b8; color:white; padding:8px 12px; border:none; border-radius:6px; cursor:pointer;">🖨️ Imprimir</button>
                <button onclick="cerrarModalPreviaListas()" style="background:#6c757d; color:white; padding:8px 12px; border:none; border-radius:6px; cursor:pointer;">❌ Cancelar</button>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .line-clamp-2 { overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
            .line-clamp-3 { overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; }
            /* Colores fijos para los puntos de nivel (evitan problemas con purge de Tailwind) */
            .dot-logrado { background-color: #16a34a; }       /* verde */
            .dot-enproceso { background-color: #f59e0b; }     /* amarillo */
            .dot-nologrado { background-color: #ef4444; }    /* rojo */
            .dot-neutral { background-color: #9ca3af; }      /* gris por defecto */
            /* Asegurar contraste en dark mode (opcional) */
            @media (prefers-color-scheme: dark) {
                .dot-enproceso { background-color: #d97706; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            let listaPreviewUrl = null;

            function abrirVistaPreviaListasUrl(url) {
                listaPreviewUrl = url;
                document.getElementById('modalPreviaListas').style.display = 'flex';
            }

            function cerrarModalPreviaListas() {
                listaPreviewUrl = null;
                document.getElementById('modalPreviaListas').style.display = 'none';
            }

            function abrirVistaPreviaListasConOrientacion(orientacion) {
                if (!listaPreviewUrl) return;
                const sep = listaPreviewUrl.includes('?') ? '&' : '?';
                const url = listaPreviewUrl + sep + 'orientacion=' + encodeURIComponent(orientacion);
                window.open(url, 'vistaPreviaListas', 'width=1100,height=800,scrollbars=yes,resizable=yes');
                cerrarModalPreviaListas();
            }

            function imprimirDesdeModalListas() {
                if (!listaPreviewUrl) return;
                const sep = listaPreviewUrl.includes('?') ? '&' : '?';
                const url = listaPreviewUrl + sep + 'autoPrint=1';
                window.open(url, 'vistaPreviaListasPrint', 'width=1100,height=800,scrollbars=yes,resizable=yes');
                cerrarModalPreviaListas();
            }
        </script>
    @endpush
</x-filament-panels::page>