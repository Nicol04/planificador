<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ficha Educativa (POO JS)</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.bubble.css" rel="stylesheet" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Crimson+Text:wght@400;600;700&display=swap');
    
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 14px;
    }
    
    .documento-seccion {
      page-break-inside: avoid;
    }
    
    .titulo-documento {
      font-family: 'Crimson Text', serif;
      letter-spacing: 0.5px;
    }
    
    .campo-editable {
      border: none;
      border-bottom: 1px solid #e5e7eb;
      background: transparent;
      transition: all 0.2s;
      font-family: Arial, Helvetica, sans-serif;
      line-height: 1.8;
    }
    
    .campo-editable:focus {
      outline: none;
      border-bottom: 2px solid #3b82f6;
      background: #f9fafb;
    }
    
    .campo-editable:hover {
      background: #f9fafb;
    }
    
    textarea.campo-editable {
      resize: vertical;
      min-height: 80px;
    }
    
    .linea-separadora {
      height: 2px;
      background: linear-gradient(to right, #1e40af, #93c5fd, #1e40af);
      margin: 1.5rem 0;
    }
    
    @media print {
      .no-imprimir {
        display: none;
      }
      body {
        background: white;
      }
    }
  </style>
</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen py-8 px-4">

  <div class="max-w-7xl mx-auto">
    
    <!-- Encabezado Principal -->
    <div class="text-center mb-8">
      <h1 class="titulo-documento text-4xl font-bold text-slate-800 mb-2">
        Sistema de Planificación Educativa
      </h1>
      <p class="text-slate-600 text-sm">Gestión de Fichas Pedagógicas</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

      <!-- Panel Izquierdo: Formulario de Entrada -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-lg p-6 border-t-4 border-blue-600">
          
          <h2 class="titulo-documento text-2xl font-bold text-slate-800 mb-1">
            Datos de la Sesión
          </h2>
          <p class="text-sm text-slate-500 mb-6">Complete los campos para generar la ficha</p>

          <div class="space-y-5">
            
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Nombre de la Sesión
              </label>
              <input type="text" 
                class="w-full border border-slate-300 rounded-md px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                placeholder="Ej: Interpretación Histórica del Perú Colonial" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Propósito de la Sesión
              </label>
              <textarea 
                class="w-full border border-slate-300 rounded-md px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none" 
                rows="3"
                placeholder="Describa el propósito general de la sesión educativa..."></textarea>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Competencia
              </label>
              <input type="text" 
                class="w-full border border-slate-300 rounded-md px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                placeholder="Ej: Construye interpretaciones históricas" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                  Capacidades
                </label>
                <input type="text" 
                  class="w-full border border-slate-300 rounded-md px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                  placeholder="Capacidades..." />
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                  Desempeños
                </label>
                <input type="text" 
                  class="w-full border border-slate-300 rounded-md px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                  placeholder="Desempeños..." />
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Criterios de Evaluación
              </label>
              <textarea 
                class="w-full border border-slate-300 rounded-md px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none" 
                rows="3"
                placeholder="Describa los criterios de evaluación que se aplicarán..."></textarea>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Evidencias de Aprendizaje
              </label>
              <textarea 
                class="w-full border border-slate-300 rounded-md px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none" 
                rows="3"
                placeholder="Describa las evidencias que demostrarán el aprendizaje..."></textarea>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Instrumentos de Evaluación
              </label>
              <input type="text" 
                class="w-full border border-slate-300 rounded-md px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                placeholder="Ej: Rúbrica, Lista de cotejo, Prueba escrita..." />
            </div>

          </div>

          <div class="mt-8 pt-6 border-t border-slate-200">
            <button id="generar-btn" onclick="generarFicha()"
              class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-md hover:shadow-lg text-base font-semibold flex items-center justify-center gap-2">
              <span>🚀</span>
              <span>Generar Ficha Completa</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Panel Derecho: Documento Editable -->
      <div class="lg:col-span-3">
        <div class="bg-white rounded-lg shadow-xl p-8 md:p-12" style="min-height: 800px;">
          
          <!-- Encabezado del Documento -->
          <div class="border-b-2 border-slate-300 pb-4 mb-8">
            <h2 class="titulo-documento text-3xl font-bold text-slate-800 text-center mb-2">
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
              <h3 class="titulo-documento text-xl font-bold text-blue-800 uppercase tracking-wide">
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
                class="campo-editable w-full text-slate-700 text-sm leading-relaxed"
                style="min-height: 100px;"
              >Pendiente de generación...</div>
            </div>
          </section>

          <div class="linea-separadora"></div>

          <!-- Sección: Desarrollo -->
          <section class="documento-seccion mb-10">
            <div class="flex items-center justify-between mb-4">
              <h3 class="titulo-documento text-xl font-bold text-blue-800 uppercase tracking-wide">
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
                class="campo-editable w-full text-slate-700 text-sm leading-relaxed"
                style="min-height: 100px;"
              >Pendiente de generación...</div>
            </div>
          </section>

          <div class="linea-separadora"></div>

          <!-- Sección: Conclusión -->
          <section class="documento-seccion mb-10">
            <div class="flex items-center justify-between mb-4">
              <h3 class="titulo-documento text-xl font-bold text-blue-800 uppercase tracking-wide">
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
                class="campo-editable w-full text-slate-700 text-sm leading-relaxed"
                style="min-height: 100px;"
              >Pendiente de generación...</div>
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

  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/html-docx-js@0.3.1/dist/html-docx.js"></script>
  <script type="module" src="js/main.js"></script>
</body>

</html>