<div>
    @vite(['resources/css/app.css'])
    
    <!-- Panel Derecho: Documento Editable -->
    <div class="bg-white rounded-lg shadow-xl p-8 md:p-12 mx-auto" style="min-height: 800px; max-width: 794px; width: 100%;">

        <!-- Encabezado del Documento -->
        <div class="border-b-2 border-slate-300 pb-4 mb-8">

            <h1 class="titulo-documento w-full text-3xl font-bold text-slate-800 text-center mb-2">
                {{ $ficha->nombre ?? 'Ficha de Aprendizaje' }}
            </h1>

            <div class="flex justify-between text-xs text-slate-500 mt-4">
                <span>Institución Educativa: Ann Goulden</span>
                <span>Fecha: {{ $ficha->created_at ? $ficha->created_at->format('d/m/Y') : \Carbon\Carbon::now()->format('d/m/Y') }}</span>
            </div>
        </div>

        <!-- Contenido Dinámico de la Ficha -->
        <div id="ficha-contenido" class="prose max-w-none text-slate-800">
            @forelse($ejerciciosHtml as $ejercicioHtml)
                {!! $ejercicioHtml !!}
            @empty
                <p class="text-gray-500 italic">No hay ejercicios disponibles.</p>
            @endforelse
        </div>

        <!-- Sección de Firma del Docente -->
        <div class="mt-16 pt-8 border-t-2 border-slate-300">
            <div class="flex justify-end">
                <div class="text-center" style="min-width: 280px;">
                    <!-- Línea de firma -->
                    <p class="text-slate-800 mb-2">_______________________________</p>
                    
                    <!-- Título/Rol -->
                    <p class="text-xs text-slate-600 uppercase tracking-wide">
                        Docente Responsable
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botón de impresión -->
    <div class="fixed bottom-8 right-8 no-print">
        <button onclick="window.print()" 
                class="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
            <span class="text-xl">🖨️</span>
            <span>Imprimir</span>
        </button>
    </div>
    
    <!-- Estilos para impresión -->
    <style>
        @media print {
            .no-print, .no-imprimir {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
            @page {
                size: A4;
                margin: 2cm;
            }
            
            /* Forzar 3 columnas en SelectionExercise y ClozeExercise al imprimir */
            .grid.grid-cols-3,
            .grid.grid-cols-1.md\:grid-cols-2 {
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 1rem !important;
            }
        }
    </style>
</div>