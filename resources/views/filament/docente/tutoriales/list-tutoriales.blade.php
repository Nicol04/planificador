<x-filament-panels::page>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <div class="space-y-6">
        {{-- Filtros de búsqueda --}}
        <div class="bg-white rounded-xl shadow-sm p-6 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Búsqueda --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        🔍 Buscar tutoriales
                    </label>
                    <div class="relative">
                        <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        <input type="search"
                            class="pl-10 block w-full rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2.5"
                            placeholder="Título, descripción..."
                            wire:model.live.debounce.300ms="search" />
                    </div>
                </div>

                {{-- Filtro por categoría --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        📂 Categoría
                    </label>
                    <select
                        class="block w-full rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2.5"
                        wire:model.live="filterCategoria">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria }}">{{ $categoria }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Tags de categorías para filtro rápido --}}
            @if($categorias->count() > 0)
                <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="$set('filterCategoria', '')"
                        class="px-3 py-1.5 text-xs font-medium rounded-full transition-all duration-200
                            {{ $filterCategoria === '' 
                                ? 'bg-primary-500 text-white shadow-md' 
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        Todos
                    </button>
                    @foreach ($categorias as $cat)
                        <button wire:click="$set('filterCategoria', '{{ $cat }}')"
                            class="px-3 py-1.5 text-xs font-medium rounded-full transition-all duration-200
                                {{ $filterCategoria === $cat 
                                    ? 'bg-primary-500 text-white shadow-md' 
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Contador de resultados --}}
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $tutorials->count() }}</span> 
                tutorial{{ $tutorials->count() !== 1 ? 'es' : '' }} encontrado{{ $tutorials->count() !== 1 ? 's' : '' }}
                @if($filterCategoria)
                    en <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $filterCategoria }}</span>
                @endif
            </p>
        </div>

        {{-- Grid de tutoriales --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" wire:loading.class="opacity-50">
            @forelse($tutorials as $tutorial)
                <div class="flex flex-col bg-white border border-gray-200 rounded-2xl shadow-sm dark:bg-gray-800 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                    
                    {{-- Área del Video con overlay --}}
                    <div class="aspect-video w-full bg-gray-100 dark:bg-gray-900 relative overflow-hidden">
                        @php
                            $embedUrl = $tutorial->video_url;
                            $videoId = null;
                            $thumbnailUrl = null;
                            
                            if (str_contains($tutorial->video_url ?? '', 'youtube.com/watch?v=')) {
                                $videoId = explode('v=', $tutorial->video_url)[1];
                                $videoId = explode('&', $videoId)[0];
                                $embedUrl = "https://www.youtube.com/embed/" . $videoId;
                                $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
                            } elseif (str_contains($tutorial->video_url ?? '', 'youtu.be/')) {
                                $videoId = explode('youtu.be/', $tutorial->video_url)[1];
                                $videoId = explode('?', $videoId)[0];
                                $embedUrl = "https://www.youtube.com/embed/" . $videoId;
                                $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
                            }
                        @endphp

                        @if($thumbnailUrl)
                            {{-- Thumbnail con botón de play --}}
                            <div class="relative w-full h-full cursor-pointer tutorial-card" 
                                data-id="{{ $tutorial->id }}"
                                data-titulo="{{ e($tutorial->titulo) }}"
                                data-descripcion="{{ e($tutorial->descripcion) }}"
                                data-categoria="{{ e($tutorial->categoria ?? 'General') }}"
                                data-video="{{ $embedUrl }}"
                                data-youtube="{{ $tutorial->video_url }}"
                                data-fecha="{{ $tutorial->created_at->format('d M, Y') }}"
                                onclick="abrirModalTutorial(this)">
                                <img src="{{ $thumbnailUrl }}" 
                                    alt="{{ $tutorial->titulo }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    onerror="this.src='https://via.placeholder.com/640x360?text=Video'" />
                                
                                {{-- Overlay con botón de play --}}
                                <div class="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-colors duration-300 flex items-center justify-center">
                                    <div class="w-16 h-16 bg-red-600 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <x-heroicon-s-play class="w-8 h-8 text-white ml-1" />
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Placeholder si no hay video válido --}}
                            <div class="flex items-center justify-center h-full text-gray-400 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900">
                                <div class="text-center">
                                    <x-heroicon-o-video-camera class="w-12 h-12 mx-auto mb-2" />
                                    <span class="text-sm">Sin video</span>
                                </div>
                            </div>
                        @endif

                        {{-- Badge de categoría sobre el video --}}
                        <div class="absolute top-3 left-3">
                            <span class="bg-primary-500/90 backdrop-blur-sm text-white text-xs font-semibold px-3 py-1 rounded-full shadow-lg">
                                {{ $tutorial->categoria ?? 'General' }}
                            </span>
                        </div>
                    </div>

                    {{-- Contenido de la Tarjeta --}}
                    <div class="p-5 flex flex-col flex-grow">
                        {{-- Fecha --}}
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <x-heroicon-o-calendar class="w-4 h-4" />
                            <span>{{ $tutorial->created_at->format('d M, Y') }}</span>
                        </div>

                        {{-- Título --}}
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2 line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                            {{ $tutorial->titulo }}
                        </h3>

                        {{-- Descripción --}}
                        <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-3 mb-4 flex-grow">
                            {{ $tutorial->descripcion }}
                        </p>

                        {{-- Botón Ver --}}
                        <button class="tutorial-card w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                            data-id="{{ $tutorial->id }}"
                            data-titulo="{{ e($tutorial->titulo) }}"
                            data-descripcion="{{ e($tutorial->descripcion) }}"
                            data-categoria="{{ e($tutorial->categoria ?? 'General') }}"
                            data-video="{{ $embedUrl }}"
                            data-youtube="{{ $tutorial->video_url }}"
                            data-fecha="{{ $tutorial->created_at->format('d M, Y') }}"
                            onclick="abrirModalTutorial(this)">
                            <x-heroicon-s-play class="w-5 h-5" />
                            Ver Tutorial
                        </button>
                    </div>
                </div>
            @empty
                {{-- Estado vacío --}}
                <div class="col-span-full">
                    <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <div class="mx-auto w-24 h-24 mb-6 text-gray-400">
                            <x-heroicon-o-video-camera class="w-full h-full" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            No hay tutoriales disponibles
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                            @if($search || $filterCategoria)
                                No se encontraron tutoriales con los filtros seleccionados. Intenta con otros criterios de búsqueda.
                            @else
                                No hay tutoriales publicados en este momento. Vuelve pronto para ver nuevo contenido.
                            @endif
                        </p>
                        @if($search || $filterCategoria)
                            <button wire:click="$set('search', ''); $set('filterCategoria', '')"
                                class="mt-4 px-4 py-2 text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                <x-heroicon-o-arrow-path class="w-4 h-4 inline-block mr-1" />
                                Limpiar filtros
                            </button>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal para ver tutorial completo --}}
    <div id="modalTutorial"
        style="display: none;" 
        class="fixed inset-0 bg-black/80 z-[9999] flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        
        {{-- Contenedor Principal --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-7xl h-[90vh] flex flex-col overflow-hidden">
            
            {{-- Header del modal --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 z-10">
                <h2 id="modalTitulo" class="text-xl font-bold text-gray-900 dark:text-white truncate pr-4"></h2>
                <button onclick="cerrarModalTutorial()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors flex-shrink-0">
                    <x-heroicon-o-x-mark class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                </button>
            </div>

            {{-- Cuerpo: Grid Responsivo (Video Izq / Info Der) --}}
            <div class="flex-grow overflow-hidden">
                <div class="h-full grid grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-gray-200 dark:divide-gray-700">
                    
                    {{-- Columna 1 y 2: Video (Ocupa 2/3 en escritorio) --}}
                    <div class="lg:col-span-2 bg-black flex flex-col justify-center h-full relative group">
                        <div class="w-full h-full lg:h-auto aspect-video">
                            <iframe id="modalVideo" 
                                src="" 
                                class="w-full h-full" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>

                    {{-- Columna 3: Información y Scroll (Ocupa 1/3 en escritorio) --}}
                    <div class="lg:col-span-1 bg-white dark:bg-gray-800 flex flex-col h-full overflow-hidden">
                        <div class="p-6 overflow-y-auto h-full scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
                            
                            {{-- Metadata --}}
                            <div class="flex flex-wrap items-center gap-3 mb-6">
                                <span id="modalCategoria" class="bg-primary-500 text-white text-xs font-semibold px-3 py-1 rounded-full shadow-sm"></span>
                                <span class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-calendar class="w-4 h-4" />
                                    <span id="modalFecha"></span>
                                </span>
                            </div>

                            {{-- Descripción --}}
                            <div class="prose dark:prose-invert max-w-none">
                                <h3 class="text-sm uppercase tracking-wider text-gray-500 dark:text-gray-400 font-bold mb-2">
                                    Descripción del Tutorial
                                </h3>
                                <p id="modalDescripcion" class="text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line text-base"></p>
                            </div>

                            {{-- Botón para abrir en YouTube --}}
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <a id="modalYoutubeLink" href="#" target="_blank" rel="noopener noreferrer"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                    </svg>
                                    Ver en YouTube
                                </a>
                            </div>
                        </div>

                        {{-- Footer interno (Botón cerrar adicional, opcional) --}}
                        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 mt-auto">
                             <button onclick="cerrarModalTutorial()" 
                                class="w-full px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-lg transition-colors shadow-sm">
                                Cerrar Ventana
                            </button>
                        </div>
                    </div>
                </div>
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
        </style>
    @endpush

    @push('scripts')
        <script>
            function abrirModalTutorial(element) {
                const titulo = element.dataset.titulo;
                const descripcion = element.dataset.descripcion;
                const categoria = element.dataset.categoria;
                const videoUrl = element.dataset.video;
                const youtubeUrl = element.dataset.youtube;
                const fecha = element.dataset.fecha;

                document.getElementById('modalTitulo').textContent = titulo;
                document.getElementById('modalDescripcion').textContent = descripcion;
                document.getElementById('modalCategoria').textContent = categoria || 'General';
                document.getElementById('modalFecha').textContent = fecha;
                document.getElementById('modalVideo').src = videoUrl + '?autoplay=1';
                document.getElementById('modalYoutubeLink').href = youtubeUrl || '#';
                document.getElementById('modalTutorial').style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            function cerrarModalTutorial() {
                document.getElementById('modalTutorial').style.display = 'none';
                document.getElementById('modalVideo').src = '';
                document.body.style.overflow = 'auto';
            }

            // Cerrar modal con ESC
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    cerrarModalTutorial();
                }
            });

            // Cerrar modal al hacer clic fuera
            document.getElementById('modalTutorial').addEventListener('click', function(event) {
                if (event.target === this) {
                    cerrarModalTutorial();
                }
            });
        </script>
    @endpush
</x-filament-panels::page>