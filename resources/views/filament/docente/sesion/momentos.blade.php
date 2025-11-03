<x-filament-panels::page>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/main.js'])
    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen py-8 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <!-- Panel Izquierdo: Formulario de Entrada -->
                <div class="lg:col-span-2">
                    <h2 class="titulo-documento text-2xl font-bold text-slate-800 dark:text-white mb-1">
                        Datos de la Sesión
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-white mb-6">Complete los campos para generar la ficha</p>

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
                                                <h3 class="font-semibold text-gray-900 dark:text-white">Sesión de Aprendizaje</h3>
                                                <p class="text-xs text-gray-500 dark:text-white">2 campos</p>
                                            </div>
                                        </div>
                                        <div class="transition-transform duration-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                </button>

                                <div class="mt-2 bg-gray-50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700/50 rounded-xl p-5 space-y-4 backdrop-blur-sm">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Nombre de la Sesión
                                        </label>
                                        <input type="text" placeholder="Ej: Interpretación Histórica del Perú Colonial"
                                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-900/50 border border-gray-300 dark:border-slate-600/30 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Propósito de la Sesión
                                        </label>
                                        <textarea rows="3" placeholder="Describa el propósito general..."
                                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-900/50 border border-gray-300 dark:border-slate-600/30 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none resize-none"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección: Competencias -->
                            <div class="group">
                                <button type="button"
                                    class="w-full bg-gradient-to-r from-gray-100 to-gray-50 dark:from-slate-800 dark:to-slate-700 hover:from-gray-200 hover:to-gray-100 dark:hover:from-slate-700 dark:hover:to-slate-600 border border-gray-300 dark:border-slate-600/50 hover:border-gray-400 dark:hover:border-slate-500 rounded-xl p-4 transition-all duration-300 text-left group-hover:shadow-lg group-hover:shadow-blue-500/10">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl">🎯</span>
                                            <div>
                                                <h3 class="font-semibold text-gray-900 dark:text-white">Competencias</h3>
                                                <p class="text-xs text-gray-500 dark:text-white">3 campos</p>
                                            </div>
                                        </div>
                                        <div class="transition-transform duration-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                </button>

                                <div class="mt-2 bg-gray-50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700/50 rounded-xl p-5 space-y-4 backdrop-blur-sm">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Competencia
                                        </label>
                                        <input type="text" placeholder="Ej: Construye interpretaciones históricas"
                                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-900/50 border border-gray-300 dark:border-slate-600/30 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Capacidades
                                        </label>
                                        <input type="text" placeholder="Capacidades..."
                                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-900/50 border border-gray-300 dark:border-slate-600/30 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Desempeños
                                        </label>
                                        <input type="text" placeholder="Desempeños..."
                                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-900/50 border border-gray-300 dark:border-slate-600/30 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                                    </div>
                                </div>
                            </div>

                            <!-- Sección: Evaluación -->
                            <div class="group">
                                <button type="button"
                                    class="w-full bg-gradient-to-r from-gray-100 to-gray-50 dark:from-slate-800 dark:to-slate-700 hover:from-gray-200 hover:to-gray-100 dark:hover:from-slate-700 dark:hover:to-slate-600 border border-gray-300 dark:border-slate-600/50 hover:border-gray-400 dark:hover:border-slate-500 rounded-xl p-4 transition-all duration-300 text-left group-hover:shadow-lg group-hover:shadow-blue-500/10">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl">✅</span>
                                            <div>
                                                <h3 class="font-semibold text-gray-900 dark:text-white">Evaluación</h3>
                                                <p class="text-xs text-gray-500 dark:text-white">3 campos</p>
                                            </div>
                                        </div>
                                        <div class="transition-transform duration-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                </button>

                                <div class="mt-2 bg-gray-50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700/50 rounded-xl p-5 space-y-4 backdrop-blur-sm">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Criterios de Evaluación
                                        </label>
                                        <textarea rows="3" placeholder="Describa los criterios..."
                                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-900/50 border border-gray-300 dark:border-slate-600/30 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none resize-none"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Evidencias de Aprendizaje
                                        </label>
                                        <textarea rows="3" placeholder="Describa las evidencias..."
                                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-900/50 border border-gray-300 dark:border-slate-600/30 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none resize-none"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Instrumentos de Evaluación
                                        </label>
                                        <input type="text" placeholder="Ej: Rúbrica, Lista de cotejo..."
                                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-900/50 border border-gray-300 dark:border-slate-600/30 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón inferior -->
                        <div class="mt-8 bg-gradient-to-br from-blue-100/30 to-emerald-100/30 dark:from-blue-600/20 dark:to-emerald-600/20 border border-emerald-300 dark:border-emerald-500/30 rounded-xl p-6 backdrop-blur-sm">
                            <button id="generar-btn" onclick="generarFicha()" type="button"
                                class="w-full bg-gradient-to-r from-blue-500 to-emerald-500 hover:from-blue-600 hover:to-emerald-600 text-white font-bold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl hover:shadow-emerald-500/20 transition-all duration-300 flex items-center justify-center gap-3 group relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-full group-hover:translate-x-0 transition-transform duration-500"></div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:rotate-12 transition-transform relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="relative z-10">Generar Ficha Completa con IA</span>
                            </button>
                            <p class="text-xs text-gray-500 dark:text-white text-center mt-3">Tu ficha se generará en segundos</p>
                        </div>
                    </div>

                </div>

                <!-- Panel Derecho: Documento Editable -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-lg shadow-xl p-8 md:p-12" style="min-height: 800px;">

                        <!-- Encabezado del Documento -->
                        <div class="border-b-2 border-slate-300 pb-4 mb-8">
                            <h2 class="titulo-documento text-3xl font-bold text-slate-800 dark:text-white text-center mb-2">
                                Ficha de Sesión de Aprendizaje
                            </h2>
                            <div class="flex justify-between text-xs text-slate-500 mt-4">
                                <span>Institución Educativa: _____________________</span>
                                <span>Fecha: _______________</span>
                            </div>
                        </div>

                        <!-- Sección: Inicio -->
                        <section class="documento-seccion mb-10">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="titulo-documento text-xl font-bold text-blue-800 dark:text-white uppercase tracking-wide">
                                    I. Inicio
                                </h3>
                                <button onclick="regenerar('inicio')"
                                    class="no-imprimir text-xs bg-blue-100 text-blue-700 px-3 py-1.5 rounded-md hover:bg-blue-200 transition font-medium">
                                    ↻ Regenerar
                                </button>
                            </div>

                            <div class="pl-6 border-l-4 border-blue-200">
                                <div
                                    id="inicio-editor"
                                    class="campo-editable w-full text-slate-700 dark:text-white text-sm leading-relaxed"
                                    style="min-height: 100px;">Pendiente de generación...</div>
                            </div>
                        </section>

                        <div class="linea-separadora"></div>

                        <!-- Sección: Desarrollo -->
                        <section class="documento-seccion mb-10">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="titulo-documento text-xl font-bold text-blue-800 dark:text-white uppercase tracking-wide">
                                    II. Desarrollo
                                </h3>
                                <button onclick="regenerar('desarrollo')"
                                    class="no-imprimir text-xs bg-blue-100 text-blue-700 px-3 py-1.5 rounded-md hover:bg-blue-200 transition font-medium">
                                    ↻ Regenerar
                                </button>
                            </div>

                            <div class="pl-6 border-l-4 border-blue-200">
                                <div
                                    id="desarrollo-editor"
                                    class="campo-editable w-full text-slate-700 dark:text-white text-sm leading-relaxed"
                                    style="min-height: 100px;">Pendiente de generación...</div>
                            </div>
                        </section>

                        <div class="linea-separadora"></div>

                        <!-- Sección: Conclusión -->
                        <section class="documento-seccion mb-10">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="titulo-documento text-xl font-bold text-blue-800 dark:text-white uppercase tracking-wide">
                                    III. Cierre
                                </h3>
                                <button onclick="regenerar('conclusion')"
                                    class="no-imprimir text-xs bg-blue-100 text-blue-700 px-3 py-1.5 rounded-md hover:bg-blue-200 transition font-medium">
                                    ↻ Regenerar
                                </button>
                            </div>

                            <div class="pl-6 border-l-4 border-blue-200">
                                <div
                                    id="conclusion-editor"
                                    class="campo-editable w-full text-slate-700 dark:text-white text-sm leading-relaxed"
                                    style="min-height: 100px;">Pendiente de generación...</div>
                            </div>
                        </section>

                        <!-- Pie del Documento -->
                        <div class="mt-12 pt-6 border-t border-slate-300">
                            <div class="grid grid-cols-2 gap-8 text-xs text-slate-600">
                                <div>
                                    <p class="mb-1">______________________________</p>
                                    <p class="font-semibold">Firma del Docente</p>
                                </div>
                                <div>
                                    <p class="mb-1">______________________________</p>
                                    <p class="font-semibold">V°B° Director/Coordinador</p>
                                </div>
                            </div>
                        </div>

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
</x-filament-panels::page>