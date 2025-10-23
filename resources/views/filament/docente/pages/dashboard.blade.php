<x-filament::page>
    <div
        class="min-h-[80vh] flex flex-col items-center justify-start bg-gradient-to-br from-amber-50 via-white to-amber-100 p-10">

        {{-- CABECERA DE BIENVENIDA --}}
        <div class="text-center mb-12">
            <div class="relative inline-block">
                <div
                    class="absolute -inset-1 bg-gradient-to-r from-amber-400 to-yellow-300 rounded-full blur opacity-30">
                </div>
                <div class="relative bg-white rounded-full p-4 shadow-md border border-amber-200">
                    <x-heroicon-o-academic-cap class="w-10 h-10 text-amber-600" />
                </div>
            </div>

            <h1 class="mt-6 text-4xl font-extrabold text-amber-800">
                ¡Hola, {{ auth()->user()->persona?->nombre ?? 'Docente' }}
                {{ auth()->user()->persona?->apellido ?? '' }}! 👩‍🏫
            </h1>

            <p class="text-gray-600 text-lg mt-2 italic">
                Bienvenido a tu panel docente. Gestiona tus clases, planifica tus unidades y potencia el aprendizaje de
                tus estudiantes. 🌱
            </p>
            <br />

        </div>

        {{-- SECCIÓN DE AULAS --}}
        <div class="w-full max-w-6xl">
            @if ($aulas->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($aulas as $aula)
                        <div
                            class="group relative bg-white rounded-2xl border border-amber-100 shadow-lg p-6 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 ease-in-out">
                            <div
                                class="absolute top-0 right-0 bg-amber-400/10 text-amber-700 text-xs px-3 py-1 rounded-bl-2xl font-medium">
                                {{ $aula->grado }}° {{ $aula->seccion }}
                            </div>
                            <div class="mb-4">
                                <h3 class="text-xl font-bold text-amber-700 group-hover:text-amber-800">
                                    {{ $aula->nombre }}
                                </h3>
                                <p class="text-gray-500 text-sm">Última actualización: {{ now()->format('d/m/Y') }}</p>
                            </div>
                            <div class="flex justify-between items-center">
                                <x-filament::button tag="a" href="#" size="sm" color="amber"
                                    icon="heroicon-o-eye">
                                    Ver aula
                                </x-filament::button>
                                <x-heroicon-o-chevron-right
                                    class="w-5 h-5 text-amber-500 group-hover:translate-x-1 transition" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20 text-gray-500">
                    <x-heroicon-o-exclamation-circle class="w-10 h-10 mx-auto mb-4 text-amber-400" />
                    <p class="text-lg font-medium">Aún no tienes aulas asignadas</p>
                    <p class="text-sm text-gray-400">Contacta al administrador para que te asigne una.</p>
                </div>
            @endif
        </div>
    </div>
</x-filament::page>
