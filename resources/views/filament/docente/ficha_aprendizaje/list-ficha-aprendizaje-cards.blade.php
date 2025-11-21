<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">📑 Mis Fichas de Aprendizaje</h1>
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
                            {{ $ficha->titulo }}
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
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <x-heroicon-o-book-open class="w-4 h-4 mr-2 text-indigo-500" />
                                <span class="font-medium">{{ $ficha->curso?->curso ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('filament.docente.resources.ficha-aprendizajes.edit', ['record' => $ficha->id]) }}"
                                class="flex-1 px-4 py-2 bg-primary-500 text-white rounded-md hover:bg-primary-600 transition">
                                <x-heroicon-o-pencil-square class="w-5 h-5 inline-block mr-2" />
                                Editar
                            </a>
                            <x-filament::button color="info" size="sm" icon="heroicon-o-eye"
                                onclick="abrirModalPreviaFicha({{ $ficha->id }})">
                                Ver
                            </x-filament::button>
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

    {{-- Modal personalizado para vista previa de ficha --}}
    <div id="modalPreviaFicha"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 10px; padding: 30px; max-width: 500px; text-align: center;">
            <h3 style="margin-bottom: 20px; color: #0066cc;">📄 Vista Previa de la Ficha</h3>
            <p style="margin-bottom: 30px; color: #666;">Seleccione el formato para previsualizar:</p>
            <div style="display: flex; gap: 15px; justify-content: center; margin-bottom: 20px;">
                <button onclick="abrirVistaPreviaFicha('vertical')"
                    style="background: #0066cc; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    📄 Vista Previa Vertical
                </button>
                <button onclick="abrirVistaPreviaFicha('horizontal')"
                    style="background: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    📄 Vista Previa Horizontal
                </button>
            </div>
            <button onclick="cerrarModalPreviaFicha()"
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
            let fichaIdActual = null;

            function abrirModalPreviaFicha(fichaId) {
                fichaIdActual = fichaId;
                document.getElementById('modalPreviaFicha').style.display = 'flex';
            }

            function cerrarModalPreviaFicha() {
                fichaIdActual = null;
                document.getElementById('modalPreviaFicha').style.display = 'none';
            }

            function abrirVistaPreviaFicha(orientacion) {
                if (fichaIdActual) {
                    const url = `/fichas-aprendizaje/${fichaIdActual}/vista-previa?orientacion=${orientacion}`;
                    window.open(url, 'vistaPreviaFicha', 'width=1200,height=800,scrollbars=yes,resizable=yes');
                    cerrarModalPreviaFicha();
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