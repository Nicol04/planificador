<x-filament-panels::page>
    <div class="container mx-auto p-6 bg-gray-50">
        <h1 class="text-4xl font-extrabold mb-8 text-gray-800 text-center">📚 Tutoriales Administrativos</h1>
        
        <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-6 text-primary-700">Filtros</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Campo de búsqueda por Título/Descripción --}}
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="search"
                        wire:model.live="search"
                        placeholder="🔍 Buscar por título o descripción..."
                        class="w-full border-gray-300 focus:ring-primary-500 focus:border-primary-500"
                    />
                </x-filament::input.wrapper>

                {{-- Selector de Categoría (Usando el nuevo método) --}}
                <x-filament::input.wrapper>
                    <x-filament::input.select 
                        wire:model.live="selectedCategory"
                        class="w-full border-gray-300 focus:ring-primary-500 focus:border-primary-500"
                    >
                        <option value="">-- Todas las Categorías --</option>
                        {{-- CAMBIO AQUÍ: Usando el método de la clase Livewire --}}
                        @foreach ($this->getAllCategories() as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>

        <div id="tutorials" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- Usa el método getRecords() como ya lo habíamos definido --}}
            @forelse ($this->getRecords() as $tutorial) 
                {{-- Tarjeta del tutorial --}}
                <div class="bg-white shadow-md hover:shadow-xl transition-shadow duration-300 rounded-lg overflow-hidden flex flex-col category-{{ Str::slug($tutorial->categoria) }}">
                    
                    @php
                        $isYouTube = Str::contains($tutorial->video_url, 'youtube.com') || Str::contains($tutorial->video_url, 'youtu.be');
                        $embedUrl = $isYouTube ? str_replace(['watch?v=', 'youtu.be/'], ['embed/', 'youtube.com/embed/'], $tutorial->video_url) : $tutorial->video_url;
                    @endphp

                    <div class="w-full" style="height: 180px;">
                        @if ($isYouTube)
                            <iframe 
                                class="w-full h-full" 
                                src="{{ $embedUrl }}" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen
                                loading="lazy"
                            ></iframe>
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center text-center p-4">
                                <a href="{{ $tutorial->video_url }}" class="text-blue-600 hover:text-blue-800 font-medium text-lg underline" target="_blank">
                                    ▶️ Ver Video (URL Externa)
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="p-5 flex flex-col flex-grow">
                        <span class="text-xs font-medium text-white px-3 py-1 bg-primary-600 rounded-full inline-block mb-3 self-start">
                            {{ $tutorial->categoria }}
                        </span>
                        <h2 class="text-lg font-bold mb-2 text-gray-900">{{ $tutorial->titulo }}</h2>
                        <p class="text-sm text-gray-600 flex-grow">{{ $tutorial->descripcion }}</p>
                    </div>
                </div>
            @empty
                <div class="lg:col-span-3 text-center p-10 bg-gray-100 rounded-lg">
                    <p class="text-xl text-gray-500">😔 No se encontraron tutoriales administrativos.</p>
                </div>
            @endforelse
        </div>
        
        <div class="mt-8">
            {{ $this->getRecords()->links() }} 
        </div>
    </div>
</x-filament-panels::page>