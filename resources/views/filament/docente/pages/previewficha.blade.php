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
                <span>Institución Educativa: _____________________</span>
                <span>Fecha: _______________</span>
            </div>
        </div>

        <!-- Contenido Dinámico de la Ficha -->
        <div id="ficha-contenido" class="prose max-w-none text-slate-800">
            @foreach($ejerciciosHtml as $ejercicioHtml)
                {!! $ejercicioHtml !!}
            @endforeach
        </div>

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
            .no-print {
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
        }
    </style>
</div>