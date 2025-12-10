@php
    // Inicializa la variable gradoDocente
    $gradoDocente = '';
    if (auth()->check()) {
        $user = auth()->user();
        // Busca el usuario_aula más reciente que tenga grado
        $usuarioAula = $user->usuario_aulas()->with('aula')->first();
        $gradoDocente =
            $usuarioAula && $usuarioAula->aula && !empty($usuarioAula->aula->grado) ? $usuarioAula->aula->grado : '';
    }
@endphp

<div>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/generador_ia/js/main.js'])
    @if (empty(auth()->user()->gemini_api_key))
        <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 rounded-lg mb-4">
            ⚠️ Aún no has configurado tu clave Gemini. Ve a tu perfil para agregarla.
        </div>
    @endif
    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen px-4">

        <!-- Modal de búsqueda de imágenes -->
        <div id="imageModal" class="hidden">
            <div id="modalContent">
                <h3 class="text-2xl font-bold mb-6">Seleccionar Imagen</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Columna Izquierda: Vista Previa -->
                    <div>
                        <h4 class="font-semibold text-lg mb-3 text-gray-700">Vista Previa</h4>
                        <div id="previewContainer" class="preview-container">
                            <p class="text-gray-400 text-sm">No hay imagen seleccionada</p>
                        </div>
                        <button id="btnConfirm" type="button" disabled
                            class="w-full mt-4 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed font-semibold transition">
                            ✓ Confirmar Selección
                        </button>
                    </div>

                    <!-- Columna Derecha: Opciones de Carga -->
                    <div>
                        <h4 class="font-semibold text-lg mb-3 text-gray-700">Fuente de Imagen</h4>

                        <!-- Tabs -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            <button id="tabUrl" type="button"
                                class="tab-btn px-3 py-2 text-sm rounded-lg bg-blue-600 text-white">🔗
                                URL</button>
                            <button id="tabFile" type="button"
                                class="tab-btn px-3 py-2 text-sm rounded-lg bg-gray-200">📁
                                Archivo</button>
                            <button id="tabClipboard" type="button"
                                class="tab-btn px-3 py-2 text-sm rounded-lg bg-gray-200">📋
                                Portapapeles</button>
                            <button id="tabSearch" type="button"
                                class="tab-btn px-3 py-2 text-sm rounded-lg bg-gray-200">🔍
                                Buscar</button>
                        </div>

                        <!-- Panel URL -->
                        <div id="panelUrl" class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">URL de la imagen:</label>
                            <input type="text" id="inputUrl"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="https://ejemplo.com/imagen.jpg">
                            <button id="btnUrl" type="button"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Cargar
                                URL</button>
                        </div>

                        <!-- Panel Archivo -->
                        <div id="panelFile" class="space-y-3 hidden">
                            <label class="block text-sm font-medium text-gray-700">Selecciona un archivo:</label>
                            <input type="file" id="inputFile" accept="image/*"
                                class="w-full px-3 py-2 border rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <button id="btnFile" type="button"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Cargar
                                Archivo</button>
                        </div>

                        <!-- Panel Portapapeles -->
                        <div id="panelClipboard" class="space-y-3 hidden">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
                                <p class="font-semibold mb-2">📋 Instrucciones:</p>
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>Copia una imagen (Ctrl+C o clic derecho → Copiar)</li>
                                    <li>Haz clic en este recuadro</li>
                                    <li>Pega la imagen (Ctrl+V)</li>
                                </ol>
                            </div>
                            <div id="clipboardDropzone"
                                class="preview-container cursor-pointer hover:border-blue-400 transition"
                                style="min-height: 200px;">
                                <p class="text-gray-400 text-center px-4">Haz clic aquí y presiona Ctrl+V para pegar</p>
                            </div>
                        </div>

                        <!-- Panel Búsqueda -->
                        <div id="panelSearch" class="space-y-3 hidden">
                            <label class="block text-sm font-medium text-gray-700">Buscar en Google:</label>
                            <div class="flex gap-2">
                                <input type="text" id="modalSearchQuery"
                                    class="flex-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Ej: perro, gato, manzana...">
                                <button id="modalSearchBtn" type="button"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">🔍</button>
                            </div>
                            <div id="modalResults" class="border rounded-lg p-2"></div>
                        </div>

                    </div>
                </div>

                <div class="mt-6 pt-4 border-t flex justify-end">
                    <button id="modalClose" type="button"
                        class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">Cerrar</button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            <!-- Panel Izquierdo: Formulario de Entrada -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 space-y-6">

                    <div>
                        <h2 class="text-2xl font-bold text-slate-800 mb-1 flex items-center gap-2">
                            <span class="text-blue-600">✨</span>
                            Crear Ficha
                        </h2>
                        <p class="text-sm text-slate-500">Configura tu ficha de aprendizaje</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="TipoFicha" class="block text-sm font-semibold text-slate-700 mb-2">
                                Tipo de Ejercicio
                            </label>
                            <select id="TipoFicha" required
                                class="w-full px-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:outline-none transition-colors duration-200">
                                <option value="" selected="">Selecciona un tipo</option>
                                <option value="Todos">Todos los tipos</option>
                                <option value="ClozeExercise">Completar palabras</option>
                                <option value="ClassificationExercise">Unir elementos</option>
                                <option value="SelectionExercise">Seleccionar opciones</option>
                                <option value="ReflectionExercise">Reflexión y lectura</option>
                            </select>
                        </div>

                        <div>
                            <label for="grado" class="block text-sm font-semibold text-slate-700 mb-2">
                                Grado Escolar
                            </label>
                            <div
                                class="w-full px-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-800 font-semibold">
                                @switch($gradoDocente)
                                    @case('1')
                                        1° Primaria
                                    @break

                                    @case('2')
                                        2° Primaria
                                    @break

                                    @case('3')
                                        3° Primaria
                                    @break

                                    @case('4')
                                        4° Primaria
                                    @break

                                    @case('5')
                                        5° Primaria
                                    @break

                                    @case('6')
                                        6° Primaria
                                    @break

                                    @default
                                        No asignado
                                @endswitch
                            </div>
                            <input type="hidden" name="grado" id="grado" value="{{ $gradoDocente }}">
                        </div>

                        <div>
                            <label for="Contenido" class="block text-sm font-semibold text-slate-700 mb-2">
                                Contenido del Tema
                            </label>
                            <textarea id="Contenido" rows="6" required
                                class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:outline-none resize-none transition-colors duration-200"
                                placeholder="Describe el tema que deseas trabajar..."></textarea>
                        </div>

                        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input id="AutoAsignarImagenes" type="checkbox" value=""
                                    class="w-5 h-5 mt-0.5 rounded border-blue-300 text-blue-600 focus:ring-2 focus:ring-blue-500">
                                <div>
                                    <span class="text-sm font-semibold text-slate-800">Asignar imágenes
                                        automáticamente</span>
                                    <p class="text-xs text-slate-600 mt-1">
                                        Se buscará y asignará la primera imagen relevante para cada elemento (límite:
                                        100 búsquedas diarias)
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <button type="button" id="toggleAdvanced"
                            class="flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors">
                            <span class="text-base">⚙️</span>
                            <span id="advancedToggleText">Mostrar configuración avanzada</span>
                        </button>

                        <div id="advancedConfig"
                            class="hidden mt-4 space-y-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Creatividad: <span id="temperatureValue"
                                        class="font-bold text-blue-600">1.0</span>
                                </label>
                                <!-- Flowbite slider -->
                                <input type="range" name="Temperature" id="Temperature" min="0"
                                    max="2" step="0.1" value="1.0" data-slider
                                    class="w-full accent-blue-600">
                                <p class="text-xs text-slate-600 mt-1">
                                    Controla la creatividad de las respuestas (0.0 = preciso, 2.0 = creativo)
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Libertad: <span id="topPValue" class="font-bold text-blue-600">1.0</span>
                                </label>
                                <!-- Flowbite slider -->
                                <input type="range" name="TopP" id="TopP" min="0" max="1"
                                    step="0.01" value="1.0" data-slider class="w-full accent-blue-600">
                                <p class="text-xs text-slate-600 mt-1">
                                    Controla la diversidad de palabras utilizadas (0.0 = restrictivo, 1.0 = libre)
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Precisión: <span id="topKValue" class="font-bold text-blue-600">40</span>
                                </label>
                                <!-- Flowbite slider -->
                                <input type="range" name="topK" id="topK" min="1" max="100"
                                    step="1" value="40" data-slider class="w-full accent-blue-600">
                                <p class="text-xs text-slate-600 mt-1">
                                    Limita las opciones de palabras disponibles (1 = muy preciso, 100 = variado)
                                </p>
                            </div>
                        </div>
                    </div>
                    @php
                        $sinClave = empty(auth()->user()->gemini_api_key);
                    @endphp

                    <button id="generar-btn" @if ($sinClave) disabled @endif
                        class="w-full flex items-center justify-center gap-3 px-6 py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">

                        <span class="text-xl">✨</span>
                        <span id="btn-text">Generar Ficha</span>
                    </button>

                    @if ($sinClave)
                        <p class="mt-2 text-sm text-red-600 font-medium">
                            ⚠️ Necesitas configurar tu clave Gemini para generar fichas.
                        </p>
                    @endif



                    {{-- Botones comentados  
                    <div class="mt-4 space-y-3">  
                    <button id="exportar-pdf-btn"
                            class="no-imprimir w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="text-lg">📄</span>
                            <span>Exportar PDF</span>
                        </button>
                        
                        <button id="limpiar-sesion-btn"
                            class="no-imprimir w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
                            <span class="text-lg">🗑️</span>
                            <span>Limpiar Sesión</span>
                        </button>
                        <button id="ver-sesion-btn"
                            class="no-imprimir w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-500 hover:bg-purple-600 text-white font-medium rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
                            <span class="text-lg">📊</span>
                            <span>Ver Ejercicios</span>
                        </button>
                         
                    </div>
                    --}}
                </div>
            </div>

            <!-- Panel Derecho: Documento Editable -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-xl p-8 md:p-12" style="min-height: 800px;">

                    <!-- Encabezado del Documento -->
                    <div class="border-b-2 border-slate-300 pb-4 mb-8">

                        <input id="titulo" name="titulo" type="text" placeholder="Título de la ficha"
                            class="titulo-documento w-full text-3xl font-bold text-slate-800 text-center mb-2 bg-transparent border-0 focus:outline-none focus:ring-0" />

                        <div class="flex justify-between text-xs text-slate-500 mt-4">
                            <span>Institución Educativa: Ann Goulden</span>
                        </div>
                    </div>


                    <!-- Contenido Dinámico de la Ficha -->
                    <div id="ficha-contenido" class="prose max-w-none text-slate-800">
                        <!-- Aquí se insertará el contenido generado dinámicamente -->
                    </div>

                </div>

                <!-- Botón de impresión/exportación eliminado -->
            </div>

        </div>

    </div>
</div>
<script>
    window.userGeminiKey = @json($user?->gemini_api_key);
    window.userSearchApiKey = @json(auth()->user()->search_api_key);
    window.userIdSearch = @json(auth()->user()->id_search);
</script>