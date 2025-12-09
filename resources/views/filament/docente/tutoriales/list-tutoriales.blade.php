<x-filament::page>
    {{-- Contenedor principal en Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        @forelse($tutorials as $tutorial)
            <div class="flex flex-col bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                
                {{-- Área del Video --}}
                <div class="aspect-video w-full bg-gray-100 dark:bg-gray-900 relative">
                    @php
                        // Pequeña lógica para convertir link de youtube normal a embed
                        $embedUrl = $tutorial->video_url;
                        if (str_contains($tutorial->video_url, 'youtube.com/watch?v=')) {
                            $videoId = explode('v=', $tutorial->video_url)[1];
                            $videoId = explode('&', $videoId)[0]; // prevenir parámetros extra
                            $embedUrl = "https://www.youtube.com/embed/" . $videoId;
                        } elseif (str_contains($tutorial->video_url, 'youtu.be/')) {
                             $videoId = explode('youtu.be/', $tutorial->video_url)[1];
                             $embedUrl = "https://www.youtube.com/embed/" . $videoId;
                        }
                    @endphp

                    @if($embedUrl)
                        <iframe 
                            src="{{ $embedUrl }}" 
                            class="w-full h-full" 
                            title="{{ $tutorial->titulo }}"
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    @else
                        {{-- Placeholder si no hay video válido --}}
                        <div class="flex items-center justify-center h-full text-gray-400">
                            <x-heroicon-o-video-camera class="w-12 h-12" />
                        </div>
                    @endif
                </div>

                {{-- Contenido de la Tarjeta --}}
                <div class="p-5 flex flex-col flex-grow">
                    <div class="flex items-center justify-between mb-2">
                        <span class="bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-primary-900 dark:text-primary-300">
                            {{ $tutorial->categoria ?? 'General' }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $tutorial->created_at->format('d M, Y') }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2 line-clamp-2">
                        {{ $tutorial->titulo }}
                    </h3>

                    <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-3 mb-4 flex-grow">
                        {{ $tutorial->descripcion }}
                    </p>

                    {{-- Botones de Acción (Editar si es necesario o Ver más) --}}
                    <div class="mt-auto flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                         {{-- Usamos el modal de edición de Filament si quieres que puedan editar desde la tarjeta --}}
                         <x-filament::button 
                            size="sm" 
                            color="gray"
                            tag="a"
                            href="{{ \App\Filament\Docente\Resources\TutorialResource::getUrl('edit', ['record' => $tutorial->id]) }}"
                         >
                            Editar / Detalles
                         </x-filament::button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center py-12 text-gray-500">
                <x-heroicon-o-face-frown class="w-12 h-12 mb-4" />
                <p class="text-lg">No hay tutoriales publicados disponibles en este momento.</p>
            </div>
        @endforelse

    </div>
</x-filament::page>