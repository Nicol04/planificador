@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/main.js'])

@if (empty(auth()->user()->gemini_api_key))
    <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 rounded-lg mb-4">
        ⚠️ Aún no has configurado tu clave Gemini. Ve a tu perfil para agregarla.
    </div>
@endif

<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen py-8 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <!-- Panel Izquierdo: Formulario de Entrada -->
            <div class="lg:col-span-2">
                <!-- Botón Crear ficha de aprendizaje -->
                @php
                    // 1. Detectar si estamos en el paso correcto
                    // Usamos str_contains para ser más flexibles con la URL
                    $isEditMomentos = request()->is('*/edit') && request('step') === 'momentos-de-la-sesion';

                    // 2. Obtener ID de la sesión
                    // IMPORTANTE: Filament suele llamar al parámetro de ruta 'record', probamos ambos por si acaso.
                    $sesionId = request()->route('record') ?? request()->route('sesion');

                    // 3. Construir la URL manualmente para asegurar que el ?sesion_id se pegue sí o sí
                    $baseUrl = \App\Filament\Docente\Resources\FichaAprendizajeResource::getUrl('create');

                    // Si tenemos ID, lo concatenamos, si no, dejamos la base
                    $urlFinal = $sesionId ? "{$baseUrl}?sesion_id={$sesionId}" : '#';

                    // Verificar si existe una ficha asociada a esta sesión
                    $existeFichaSesion = false;
                    if ($sesionId) {
                        $existeFichaSesion = \App\Models\FichaSesion::where('sesion_id', $sesionId)->exists();
                    }
                @endphp

                @if ($existeFichaSesion)
                    @php
                        // Obtener el id de la ficha asociada
                        $fichaSesion = \App\Models\FichaSesion::where('sesion_id', $sesionId)->first();
                        $fichaId = $fichaSesion?->ficha_aprendizaje_id;
                        $editUrl = $fichaId
                            ? \App\Filament\Docente\Resources\FichaAprendizajeResource::getUrl('edit', [
                                'record' => $fichaId,
                            ])
                            : '#';
                        $previewUrl = $fichaId ? url("/fichas/{$fichaId}/preview") : '#';
                    @endphp
                    <div class="mb-4 p-3 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                        Ya existe una ficha de aprendizaje asociada a esta sesión.
                    </div>
                    @if ($fichaId)
                        <div class="flex flex-col sm:flex-row gap-2 mb-6">
                            <button type="button"
                                class="flex-1 bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 text-white font-bold py-2 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 text-base"
                                style="border: 2px solid #fbbf24;" onclick="window.location.href='{{ $editUrl }}'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536M9 11l6-6 3 3-6 6H9v-3z" />
                                </svg>
                                Editar ficha
                            </button>
                            <button type="button"
                                class="flex-1 bg-gradient-to-r from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white font-bold py-2 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 text-base"
                                onclick="window.open('{{ $previewUrl }}', 'vistaPreviaFicha', 'width=1200,height=800,scrollbars=yes,resizable=yes');">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Previsualizar
                            </button>
                        </div>
                    @endif
                @else
                    <button type="button"
                        class="w-full mb-6 bg-gradient-to-r from-emerald-500 to-blue-500 hover:from-emerald-600 hover:to-blue-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 text-lg
                        @unless ($isEditMomentos && $sesionId) opacity-50 cursor-not-allowed @endunless"
                        {{-- Usamos la URL concatenada manualmente --}}
                        onclick="@if ($isEditMomentos && $sesionId) window.location.href='{{ $urlFinal }}' @else return false; @endif"
                        @unless ($isEditMomentos && $sesionId) disabled @endunless>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Crear ficha de aprendizaje
                    </button>
                @endif

                @unless ($isEditMomentos)
                    <div class="mb-4 p-3 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 rounded">
                        Solo puedes crear una ficha de aprendizaje cuando estés editando una sesión.
                    </div>
                @endunless

                <h2 class="titulo-documento text-2xl font-bold text-slate-800 dark:text-white mb-1">
                    Datos de la Sesión
                </h2>
                <p class="text-sm text-slate-500 dark:text-white mb-6">Complete los campos para generar la ficha
                </p>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-3">
                        <!-- Sección: Sesión de Aprendizaje -->
                        <div class="group">
                            <button type="button"
                                class="w-full bg-gradient-to-r from-gray-100 to-gray-50 dark:from-slate-800 dark:to-slate-700 hover:from-gray-200 hover:to-gray-100 dark:hover:from-slate-700 dark:hover:to-slate-600 border border-gray-300 dark:border-slate-600/50 hover:border-gray-400 dark:hover:border-slate-500 rounded-xl p-4 transition-all duration-300 text-left group-hover:shadow-lg group-hover:shadow-blue-500/10">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">📚</span>
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-white">Sesión de
                                                Aprendizaje</h3>
                                            <p class="text-xs text-gray-500 dark:text-white">2 campos</p>
                                        </div>
                                    </div>
                                    <div class="transition-transform duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-5 h-5 text-emerald-500 dark:text-emerald-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                </div>
                            </button>

                            <div
                                class="mt-2 bg-gray-50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700/50 rounded-xl p-5 space-y-4 backdrop-blur-sm">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-white mb-2 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-3 h-3 text-emerald-500 dark:text-emerald-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Tema de la Sesión
                                    </label>
                                    <input type="text" id="tema" name="tema"
                                        placeholder="Ej: Interpretación Histórica del Perú Colonial"
                                        class="w-full px-4 py-2.5 bg-white dark:bg-slate-900/50 border border-gray-300 dark:border-slate-600/30 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Botón inferior -->
                    <div
                        class="mt-8 bg-gradient-to-br from-blue-100/30 to-emerald-100/30 dark:from-blue-600/20 dark:to-emerald-600/20 border border-emerald-300 dark:border-emerald-500/30 rounded-xl p-6 backdrop-blur-sm">
                        @php
                            $sinClave = empty(auth()->user()->gemini_api_key);
                        @endphp

                        <button id="generar-btn" onclick="generarFicha()" type="button"
                            @if ($sinClave) disabled @endif
                            class="w-full bg-gradient-to-r from-blue-500 to-emerald-500
               hover:from-blue-600 hover:to-emerald-600 text-white font-bold
               py-4 px-6 rounded-lg shadow-lg hover:shadow-xl hover:shadow-emerald-500/20
               transition-all duration-300 flex items-center justify-center gap-3 group
               relative overflow-hidden
               disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none disabled:hover:from-blue-500 disabled:hover:to-emerald-500">

                            <div
                                class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent
                translate-x-full group-hover:translate-x-0 transition-transform duration-500
                pointer-events-none">
                            </div>

                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 group-hover:rotate-12 transition-transform relative z-10" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>

                            <span class="relative z-10">Generar Ficha Completa con IA</span>
                        </button>

                        @if ($sinClave)
                            <p class="mt-2 text-sm text-red-600 font-semibold">
                                ⚠️ Necesitas configurar tu clave Gemini para usar esta función.
                            </p>
                        @endif

                        <p class="text-xs text-gray-500 dark:text-white text-center mt-3">Tu ficha se generará en
                            segundos</p>
                    </div>
                </div>

            </div>

            <!-- Panel Derecho: Documento Editable -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-xl p-8 md:p-12" style="min-height: 800px;">

                    <!-- Encabezado del Documento -->
                    <div class="border-b-2 border-slate-300 pb-4 mb-8">
                        <h2
                            class="titulo-documento text-3xl font-bold text-slate-800 dark:text-white text-center mb-2">
                            Momentos de la sesión de aprendizaje
                        </h2>
                    </div>

                    <!-- Sección: Inicio -->
                    <section class="documento-seccion mb-8">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xl font-bold text-blue-800 uppercase">I. Inicio</h3>
                            <button onclick="regenerar('inicio')"
                                class="no-imprimir text-xs bg-blue-100 text-blue-700 px-3 py-1.5 rounded-md">↻
                                Regenerar</button>
                        </div>
                        <div class="pl-6 border-l-4 border-blue-200">
                            <div wire:ignore wire:key="inicio-editor" class="pl-0">
                                <div id="inicio-editor" data-quill="true" contenteditable="true"
                                    class="campo-editable w-full text-slate-700 text-sm leading-relaxed"
                                    style="min-height:120px;">{!! $datosSesion['inicio'] ?? '' !!}</div>
                            </div>
                        </div>
                    </section>

                    <div class="linea-separadora"></div>

                    <!-- Sección: Desarrollo -->
                    <section class="documento-seccion mb-8">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xl font-bold text-blue-800 uppercase">II. Desarrollo</h3>
                            <button onclick="regenerar('desarrollo')"
                                class="no-imprimir text-xs bg-blue-100 text-blue-700 px-3 py-1.5 rounded-md">↻
                                Regenerar</button>
                        </div>
                        <div class="pl-6 border-l-4 border-blue-200">
                            <div wire:ignore wire:key="desarrollo-editor" class="pl-0">
                                <div id="desarrollo-editor" data-quill="true" contenteditable="true"
                                    class="campo-editable w-full text-slate-700 text-sm leading-relaxed"
                                    style="min-height:220px;">{!! $datosSesion['desarrollo'] ?? '' !!}</div>
                            </div>
                        </div>
                    </section>

                    <div class="linea-separadora my-6 border-t"></div>

                    <!-- Sección: Conclusión -->
                    <section class="documento-seccion mb-8">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xl font-bold text-blue-800 uppercase">III. Cierre</h3>
                            <button onclick="regenerar('conclusion')"
                                class="no-imprimir text-xs bg-blue-100 text-blue-700 px-3 py-1.5 rounded-md">↻
                                Regenerar</button>
                        </div>
                        <div class="pl-6 border-l-4 border-blue-200">
                            <div wire:ignore wire:key="conclusion-editor" class="pl-0">
                                <div id="conclusion-editor" data-quill="true" contenteditable="true"
                                    class="campo-editable w-full text-slate-700 text-sm leading-relaxed"
                                    style="min-height:120px;">{!! $datosSesion['cierre'] ?? '' !!}</div>
                            </div>
                        </div>
                    </section>

                </div>

                <!-- Botón de impresión/exportación -->
                <div class="mt-4 flex justify-end gap-3 no-imprimir">
                    <button onclick="exportarWord()"
                        class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition text-sm font-medium">
                        📄 Exportar Word
                    </button>
                    <button onclick="window.print()"
                        class="bg-slate-600 text-white px-5 py-2 rounded-lg hover:bg-slate-700 transition text-sm font-medium">
                        🖨️ Imprimir Ficha
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@php
    use App\Models\Competencia;
    use App\Models\Capacidad;
    use App\Models\Estandar;

    // Tomamos los datos de sesión (o vacíos si no existen)
    $datosSesion = $datosSesion ?? [
        'titulo' => session('titulo', ''),
        'proposito_sesion' => session('proposito_sesion', ''),
        'evidencias' => session('evidencias', ''),
        'competencias' => session('competencias', []),
        'criterios' => session('criterios', []),
        'instrumentos_predefinidos' => session('instrumentos_predefinidos', []),
        'instrumentos_personalizados' => session('instrumentos_personalizados', []),
    ];
    // Agregamos genero desde User->Persona
    $genero = null;
    if (Auth::check()) {
        $user = Auth::user();

        // Género
        if ($user->persona) {
            $genero = $user->persona->genero;
        }

        // Grado del aula
        if ($user->usuario_aulas && $user->usuario_aulas->count() > 0) {
            // Tomamos la primera aula asociada
            $aula = $user->usuario_aulas->first()->aula ?? null;
            if ($aula) {
                $gradoAula = $aula->nombre; // o $aula->grado si quieres solo el grado
            }
        }
    }
    $datosSesion['genero'] = $genero;
    $datosSesion['grado_aula'] = $gradoAula;

    // Descomponer competencias: obtener nombre de la competencia, capacidades y estándares
    $competenciasCompletas = [];

    foreach ($datosSesion['competencias'] as $c) {
        $comp = Competencia::find($c['competencia_id']); // buscar competencia real
        if (!$comp) {
            continue;
        }

        // Capacidades con nombre
        $capacidadesNombres = [];
        if (!empty($c['capacidades'])) {
            $capacidadesNombres = Capacidad::whereIn('id', $c['capacidades'])->pluck('nombre')->toArray();
        }

        // Estándares con descripción
        $estandaresDesc = [];
        if (!empty($c['estandares'])) {
            $estandaresDesc = Estandar::whereIn('id', $c['estandares'])->pluck('descripcion')->toArray();
        }

        $competenciasCompletas[] = [
            'competencia_id' => $c['competencia_id'],
            'competencia_nombre' => $comp->nombre,
            'capacidades' => $capacidadesNombres,
            'estandares' => $estandaresDesc,
            'criterios' => $c['criterios'] ?? [],
            'instrumentos_predefinidos' => $c['instrumentos_predefinidos'] ?? null,
            'instrumentos_personalizados' => $c['instrumentos_personalizados'] ?? [],
        ];
    }

    // Reemplazamos las competencias originales por las completas
    $datosSesion['competencias'] = $competenciasCompletas;

    $momentos_defecto = [
        ['nombre_momento' => 'Inicio', 'descripcion' => $datosSesion['inicio'] ?? '', 'actividades' => ''],
        ['nombre_momento' => 'Desarrollo', 'descripcion' => $datosSesion['desarrollo'] ?? '', 'actividades' => ''],
        ['nombre_momento' => 'Cierre', 'descripcion' => $datosSesion['conclusion'] ?? '', 'actividades' => ''],
    ];
    $user = auth()->user();
@endphp

<h2 id="tituloLabel">Titulo: {{ $datosSesion['titulo'] ?? '' }}</h2>
<p id="propositoLabel">Propósito: {{ $datosSesion['proposito_sesion'] ?? '' }}</p>
<p id="generoLabel">Género: {{ $datosSesion['genero'] ?? 'N/A' }}</p>
<p id="gradoAulaLabel">Grado del Aula: {{ $datosSesion['grado_aula'] ?? 'N/A' }}</p>

<input type="hidden" id="momentos_data_input" name="data[momentos_data]" value='@json($momentos_defecto)'>

<!-- Hidden fields ahora forman parte del state 'data' para que Filament/Livewire los capture -->
<textarea id="inicioInput" name="data[inicio]" style="display:none;">{{ $datosSesion['inicio'] ?? '' }}</textarea>
<textarea id="desarrolloInput" name="data[desarrollo]" style="display:none;">{{ $datosSesion['desarrollo'] ?? '' }}</textarea>
<textarea id="conclusionInput" name="data[cierre]" style="display:none;">{{ $datosSesion['conclusion'] ?? '' }}</textarea>

<script>
    window.userGeminiKey = @json($user?->gemini_api_key);

    document.addEventListener('DOMContentLoaded', function() {
        function syncMomentos() {
            // Obtener HTML directamente de los editores contenteditable
            const inicioHtml = document.getElementById('inicio-editor')?.innerHTML ?? '';
            const desarrolloHtml = document.getElementById('desarrollo-editor')?.innerHTML ?? '';
            const conclusionHtml = document.getElementById('conclusion-editor')?.innerHTML ?? '';

            // sincronizar los hidden que Filament persiste (data[...] names) y disparar input
            const hi = document.getElementById('inicioInput');
            const hd = document.getElementById('desarrolloInput');
            const hc = document.getElementById('conclusionInput');
            if (hi) {
                hi.value = inicioHtml;
                hi.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            }
            if (hd) {
                hd.value = desarrolloHtml;
                hd.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            }
            if (hc) {
                hc.value = conclusionHtml;
                hc.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            }

            const arr = [{
                    nombre_momento: 'Inicio',
                    descripcion: inicioHtml,
                    actividades: ''
                },
                {
                    nombre_momento: 'Desarrollo',
                    descripcion: desarrolloHtml,
                    actividades: ''
                },
                {
                    nombre_momento: 'Cierre',
                    descripcion: conclusionHtml,
                    actividades: ''
                },
            ];

            const hidden = document.querySelector('input[name="data[momentos_data]"]') || document
                .getElementById('momentos_data_input');
            if (hidden) {
                hidden.value = JSON.stringify(arr);
                hidden.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            }
        }

        ['inicio-editor', 'desarrollo-editor', 'conclusion-editor'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            // input no siempre dispara en div contenteditable dependiendo del editor, usar varios eventos
            el.addEventListener('input', syncMomentos);
            el.addEventListener('keyup', syncMomentos);
            el.addEventListener('blur', syncMomentos);
            // observer para cambios programáticos (p. ej. Quill)
            const mo = new MutationObserver(syncMomentos);
            mo.observe(el, {
                childList: true,
                subtree: true,
                characterData: true
            });
        });

        // sincronizar justo antes de enviar cualquier formulario
        document.addEventListener('submit', syncMomentos, true);
        syncMomentos();
    });
    
</script>

@forelse($datosSesion['competencias'] ?? [] as $comp)
    <div class="competencia-item">
        <p><strong>Competencia:</strong> {{ $comp['competencia_nombre'] ?? 'N/A' }}</p>
        <p><strong>Capacidades:</strong>
            @if (!empty($comp['capacidades']))
                {{ implode(', ', $comp['capacidades']) }}
            @else
                N/A
            @endif
        </p>
        <p><strong>Estándares:</strong>
            @if (!empty($comp['estandares']))
                {{ implode(', ', $comp['estandares']) }}
            @else
                N/A
            @endif
        </p>
        <p><strong>Criterios:</strong>
            @if (!empty($comp['criterios']))
                {{ implode(', ', $comp['criterios']) }}
            @else
                N/A
            @endif
        </p>
        <p><strong>Instrumentos:</strong>
            @if (!empty($comp['instrumentos_predefinidos']))
                {{ $comp['instrumentos_predefinidos'] }}
            @endif
            @if (!empty($comp['instrumentos_personalizados']))
                {{ implode(', ', $comp['instrumentos_personalizados']) }}
            @endif
        </p>
        <hr>
    </div>
@empty
    <p>No hay competencias registradas.</p>
@endforelse
<p id="evidenciasLabel">Evidencias: {{ $datosSesion['evidencias'] ?? '' }}</p>
