import { FichaController } from "./controllers/FichaController.js";
import { AprendizajeController } from "./controllers/AprendizajeController.js";
import { QuillEditorManager } from "./services/QuillEditorManager.js";
import { WordExportService } from "./services/WordExportService.js";

console.log('🎯 main.js cargado correctamente');

const API_KEY = "AIzaSyAvNoL4EJw-sGpzortVelmpdMRLlznIzZA"; // ⚠️ No seguro para producción
const SEARCH_API_KEY = "AIzaSyBtow2Dzgpcuuko3cSVCh4L2A5s8j32r9Y"; // ⚠️ No seguro para producción
const fichaController = new FichaController(API_KEY);
const aprendizajeController = new AprendizajeController();
const quillManager = new QuillEditorManager();
const wordExportService = new WordExportService(quillManager);

console.log('✅ Controladores inicializados:', {
  fichaController,
  aprendizajeController,
  quillManager,
  wordExportService
});

// ✅ ACTUALIZADO: Selecciona inputs del panel izquierdo usando un selector más específico
function leerAprendizajeDeFormulario() {
  // Seleccionar el contenedor del formulario (panel izquierdo)
  const formContainer = document.querySelector('.lg\\:col-span-2');
  const inputs = formContainer.querySelectorAll('input');
  const textareas = formContainer.querySelectorAll('textarea');
  
  return {
    nombre: inputs[0].value,
    proposito: textareas[0].value,
    competencia: inputs[1].value,
    capacidades: inputs[2].value,
    desempenos: inputs[3].value,
    criterios: textareas[1].value,
    evidencias: textareas[2].value,
    instrumentos: inputs[4].value
  };
}

// Guardar aprendizaje y actualizar contexto
function guardarAprendizaje() {
  const data = leerAprendizajeDeFormulario();
  aprendizajeController.aprendizajes = []; // Solo uno por ahora
  aprendizajeController.agregarAprendizaje(data);
  fichaController.setAprendizajes(aprendizajeController.obtenerAprendizajes());
}

// Cargar ficha al iniciar
document.addEventListener("DOMContentLoaded", async () => {
  console.log('🎬 DOMContentLoaded - Inicializando editores...');
  
  // Initialize Quill editors
  quillManager.initializeEditor('#inicio-editor', 'bubble');
  quillManager.initializeEditor('#desarrollo-editor', 'bubble');
  quillManager.initializeEditor('#conclusion-editor', 'bubble');
  
  console.log('✅ Editores Quill inicializados');
  
  // No generar automáticamente, esperar al usuario
  renderFicha();
  
  // ✅ ACTUALIZADO: Agregar listeners a los inputs del formulario
  const formContainer = document.querySelector('.lg\\:col-span-2');
  if (formContainer) {
    const inputs = formContainer.querySelectorAll('input, textarea');
    
    inputs.forEach(el => {
      el.addEventListener('change', () => {
        console.log('📝 Campo modificado, guardando aprendizaje...');
        guardarAprendizaje();
      });
    });
    
    console.log(`✅ ${inputs.length} inputs con listeners agregados`);
  } else {
    console.error('❌ No se encontró el contenedor del formulario');
  }
  
  console.log('🎉 Inicialización completa');
});

// ✅ ACTUALIZADO: Usar QuillEditorManager para renderizar Markdown
function renderFicha() {
  quillManager.setMarkdown('#inicio-editor', fichaController.inicio.texto || "Pendiente de generación...");
  quillManager.setMarkdown('#desarrollo-editor', fichaController.desarrollo.texto || "Pendiente de generación...");
  quillManager.setMarkdown('#conclusion-editor', fichaController.conclusion.texto || "Pendiente de generación...");
}

window.renderFicha = renderFicha;

window.generarFicha = async () => {
  console.log('🚀 Iniciando generación de ficha...');
  
  const btn = document.getElementById('generar-btn');
  btn.disabled = true;
  
  // ✅ MEJORADO: Mantener estructura del botón con HTML
  btn.innerHTML = `
    <span>⏳</span>
    <span>Generando Ficha...</span>
  `;

  // ✅ ACTUALIZADO: Usar QuillEditorManager
  quillManager.setContent('#inicio-editor', "Generando...");
  quillManager.setContent('#desarrollo-editor', "Generando...");
  quillManager.setContent('#conclusion-editor', "Generando...");

  guardarAprendizaje();
  
  try {
    console.log('📝 Aprendizajes guardados:', aprendizajeController.obtenerAprendizajes());
    await fichaController.generarTodo();
    console.log('✅ Ficha generada exitosamente');
  } catch (error) {
    console.error('❌ Error al generar ficha:', error);
    quillManager.setContent('#inicio-editor', "Error al generar. Ver consola.");
    quillManager.setContent('#desarrollo-editor', "Error al generar. Ver consola.");
    quillManager.setContent('#conclusion-editor', "Error al generar. Ver consola.");
  }

  btn.disabled = false;
  btn.innerHTML = `
    <span>🚀</span>
    <span>Generar Ficha Completa</span>
  `;
};

window.regenerar = async (seccion) => {
  const btn = event.target;
  btn.disabled = true;
  btn.textContent = "⏳ Generando...";
  
  // Actualizar aprendizajes antes de regenerar
  guardarAprendizaje();
  
  // ✅ ACTUALIZADO: Usar QuillEditorManager
  if (seccion === "inicio") quillManager.setContent('#inicio-editor', "Generando...");
  else if (seccion === "desarrollo") quillManager.setContent('#desarrollo-editor', "Generando...");
  else if (seccion === "conclusion") quillManager.setContent('#conclusion-editor', "Generando...");

  if (seccion === "inicio") await fichaController.generarInicio();
  else if (seccion === "desarrollo") await fichaController.generarDesarrollo();
  else if (seccion === "conclusion") await fichaController.generarConclusion();

  btn.disabled = false;
  btn.textContent = `↻ Regenerar`;
};

window.exportarWord = async () => {
  await wordExportService.exportToWord(fichaController, aprendizajeController);
};

function capitalize(text) {
  return text.charAt(0).toUpperCase() + text.slice(1);
}