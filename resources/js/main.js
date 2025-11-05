import { FichaController } from "./controllers/FichaController.js";
import { AprendizajeController } from "./controllers/AprendizajeController.js";
import { QuillEditorManager } from "./services/QuillEditorManager.js";
import { WordExportService } from "./services/WordExportService.js";
import { Aprendizaje } from './models/Aprendizaje.js'; // añadir al top si usas módulos

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

function actualizarDatosSesionDesdeLabels() {
  // helper seguro para obtener texto y quitar prefijo
  const getText = (id, prefix = '') => {
    const el = document.getElementById(id);
    const text = el && el.innerText ? el.innerText : '';
    return prefix ? text.replace(prefix, '').trim() : text.trim();
  };

  const titulo = getText('tituloLabel', 'Titulo:') || '';
  const proposito = getText('propositoLabel', 'Propósito:') || '';
  const genero = getText('generoLabel', 'Género:') || '';
  const gradoAula = getText('gradoAulaLabel', 'Grado del Aula:') || '';
  const evidencias = getText('evidenciasLabel', 'Evidencias:') || '';

  // Parseo robusto de .competencia-item sin usar selectores inválidos
  const competenciaItems = document.querySelectorAll('.competencia-item');
  const competencias = Array.from(competenciaItems).map(item => {
    const paragraphs = Array.from(item.querySelectorAll('p'));
    const findValue = (label) => {
      const p = paragraphs.find(p => p.innerText.trim().startsWith(label));
      if (!p) return '';
      return p.innerText.replace(label, '').trim();
    };

    const capacidadesText = findValue('Capacidades:');
    const estandaresText = findValue('Estándares:');
    const criteriosText = findValue('Criterios:');
    const instrumentosText = findValue('Instrumentos:');
    const nombre = findValue('Competencia:');

    return {
      competencia_nombre: nombre || '',
      capacidades: capacidadesText ? capacidadesText.split(',').map(s => s.trim()).filter(Boolean) : [],
      estandares: estandaresText ? estandaresText.split(',').map(s => s.trim()).filter(Boolean) : [],
      criterios: criteriosText ? criteriosText.split(',').map(s => s.trim()).filter(Boolean) : [],
      instrumentos: instrumentosText ? instrumentosText.split(',').map(s => s.trim()).filter(Boolean) : [],
    };
  });

  window.datosSesion = {
    ...window.datosSesion,
    titulo,
    proposito_sesion: proposito,
    genero,
    grado_aula: gradoAula,
    evidencias,
    competencias
  };

  console.log('🌟 Datos de sesión actualizados desde labels:', window.datosSesion);
  window.dispatchEvent(new CustomEvent('sesionDataUpdated', { detail: window.datosSesion }));
}
function guardarAprendizaje() {
  actualizarDatosSesionDesdeLabels(); // actualiza datos desde HTML

  const temaInput = document.getElementById('tema');
  const tema = temaInput ? (temaInput.value || '') : '';
  const datosSesion = window.datosSesion || {};

  const aprendizaje = Aprendizaje.fromSessionData({
    ...datosSesion,
    tema,
  });

  aprendizajeController.aprendizajes = [];
  aprendizajeController.agregarAprendizaje(aprendizaje);
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

  const sesion = window.datosSesion || window.sesionData || {};
  if (sesion && (sesion.titulo || sesion.proposito_sesion)) {
    const apr = Aprendizaje.fromSessionData(sesion);
    aprendizajeController.aprendizajes = []; // limpiar estado si se desea
    aprendizajeController.agregarAprendizaje(apr);
    fichaController.setAprendizajes(aprendizajeController.obtenerAprendizajes());
    console.log('⚡ Aprendizaje cargado desde session:', apr);
  }

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

  // Sync generated content into hidden inputs so forms or other scripts can access them
  try {
    const setHidden = (id, value) => {
      const el = document.getElementById(id);
      if (el) el.value = value || '';
    };

    setHidden('inicioInput', fichaController.inicio && fichaController.inicio.texto ? fichaController.inicio.texto : '');
    setHidden('desarrolloInput', fichaController.desarrollo && fichaController.desarrollo.texto ? fichaController.desarrollo.texto : '');
    setHidden('conclusionInput', fichaController.conclusion && fichaController.conclusion.texto ? fichaController.conclusion.texto : '');
  } catch (e) {
    console.warn('No se pudieron sincronizar los campos ocultos:', e);
  }
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

window.regenerar = async (seccion, e) => {
  // obtener el botón de forma segura: si se pasa el evento lo usamos, si no buscamos por onclick
  const btn = e && e.target
    ? e.target
    : document.querySelector(`button[onclick^="regenerar('${seccion}")`) // intento de fallback
    || document.querySelector(`button[onclick*="regenerar('${seccion}')"]`)
    || null;

  if (btn) {
    btn.disabled = true;
    btn.textContent = "⏳ Generando...";
  }

  // Actualizar aprendizajes antes de regenerar
  guardarAprendizaje();

  // Mostrar estado en Quill
  if (seccion === "inicio") quillManager.setContent('#inicio-editor', "Generando...");
  else if (seccion === "desarrollo") quillManager.setContent('#desarrollo-editor', "Generando...");
  else if (seccion === "conclusion") quillManager.setContent('#conclusion-editor', "Generando...");

  try {
    if (seccion === "inicio") await fichaController.generarInicio();
    else if (seccion === "desarrollo") await fichaController.generarDesarrollo();
    else if (seccion === "conclusion") await fichaController.generarConclusion();
  } catch (err) {
    console.error('Error regenerando sección', seccion, err);
  }

  if (btn) {
    btn.disabled = false;
    btn.textContent = `↻ Regenerar`;
  }
};

window.exportarWord = async () => {
  await wordExportService.exportToWord(fichaController, aprendizajeController);
};

function capitalize(text) {
  return text.charAt(0).toUpperCase() + text.slice(1);
}