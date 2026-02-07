<x-filament-panels::page>
    @vite(['resources/css/tutoriales.css', 'resources/js/tutoriales.js'])

    <div class="tutorials-wrapper">
        {{-- Header Premium --}}
        <div class="tutorials-header-premium">
            <h1>📚 Tutoriales Administrativos</h1>
            <p>Aprende a usar todas las funcionalidades del sistema con nuestros tutoriales en video</p>
        </div>

        {{-- Filtros Mejorados --}}
        <div class="tutorials-filters">
            <h2>🔍 Buscar y Filtrar</h2>
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="search">Buscar por título o descripción</label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="search"
                            id="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="🔍 Escribe para buscar..."
                        />
                    </x-filament::input.wrapper>
                </div>

                <div class="filter-group">
                    <label for="category">Filtrar por categoría</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select 
                            id="category"
                            wire:model.live="selectedCategory"
                        >
                            <option value="">🎯 Todas las Categorías</option>
                            @foreach ($this->getAllCategories() as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>

            {{-- Contador de resultados --}}
            <div class="results-counter" style="margin-top: 1rem; color: #667eea; font-weight: 600;">
                Mostrando {{ $this->getRecords()->count() }} tutoriales
            </div>

            {{-- Botón limpiar filtros --}}
            @if($search || $selectedCategory)
                <button 
                    wire:click="$set('search', ''); $set('selectedCategory', '')"
                    class="clear-filters-btn"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Limpiar Filtros
                </button>
            @endif
        </div>

        {{-- Grid de Tutoriales --}}
        <div class="tutorials-grid">
            @forelse ($this->getRecords() as $tutorial)
                <div class="tutorial-card" data-category="{{ $tutorial->categoria }}">
                    {{-- Video --}}
                    @php
                        $isYouTube = Str::contains($tutorial->video_url, 'youtube.com') || Str::contains($tutorial->video_url, 'youtu.be');
                        $videoId = null;
                        
                        if ($isYouTube) {
                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $tutorial->video_url, $matches);
                            $videoId = $matches[1] ?? null;
                        }
                        
                        $embedUrl = $videoId ? "https://www.youtube.com/embed/{$videoId}" : $tutorial->video_url;
                    @endphp

                    <div class="tutorial-video-container">
                        @if ($isYouTube && $videoId)
                            <iframe 
                                src="{{ $embedUrl }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen
                                loading="lazy"
                                title="{{ $tutorial->titulo }}"
                            ></iframe>
                        @else
                            <div class="video-placeholder">
                                <a href="{{ $tutorial->video_url }}" target="_blank" rel="noopener noreferrer">
                                    <span class="play-icon">▶️</span>
                                    <span>Ver Video</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Contenido --}}
                    <div class="tutorial-body">
                        <span class="category-badge">{{ $tutorial->categoria }}</span>
                        <h2 class="tutorial-title">{{ $tutorial->titulo }}</h2>
                        <p class="tutorial-description">{{ $tutorial->descripcion ?? 'Sin descripción' }}</p>

                        {{-- Footer --}}
                        <div class="tutorial-footer">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span>{{ $tutorial->created_at->diffForHumans() }}</span>
                                <span style="font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; {{ $tutorial->public ? 'background: #dcfce7; color: #166534;' : 'background: #fee2e2; color: #991b1b;' }}">
                                    {{ $tutorial->public ? '👥 Docentes' : '🔒 Administrativo' }}
                                </span>
                            </div>
                            <a href="{{ $tutorial->video_url }}" target="_blank" rel="noopener noreferrer">
                                Ver completo →
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">🎬</div>
                    <h3 class="empty-title">No se encontraron tutoriales</h3>
                    <p class="empty-text">
                        @if($search || $selectedCategory)
                            Ajusta los filtros de búsqueda
                        @else
                            Aún no hay tutoriales administrativos
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        <div class="pagination-wrapper">
            {{ $this->getRecords()->links() }}
        </div>
    </div>
</x-filament-panels::page>