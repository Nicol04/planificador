import { FichaController } from "./controllers/FichaController.js";
import { AprendizajeController } from "./controllers/AprendizajeController.js";
import { QuillEditorManager } from "./services/QuillEditorManager.js";
import { WordExportService } from "./services/WordExportService.js";
import { Aprendizaje } from './models/Aprendizaje.js';
import { getSesionIdFromEditUrl, SesionMomentoService } from "./services/SesionMomentoService.js";

console.log('🎯 main.js cargado correctamente');

const API_KEY = window.userGeminiKey ?? null;
const SEARCH_API_KEY = window.userGeminiKey ?? null;
const fichaController = new FichaController(API_KEY);
const aprendizajeController = new AprendizajeController();
const quillManager = new QuillEditorManager();
const wordExportService = new WordExportService(quillManager);

if (!API_KEY) {
    console.warn("⚠️ No se encontró la clave Gemini del usuario autenticado.");
}
console.log(window.userGeminiKey);

console.log('✅ Controladores inicializados:', {
  fichaController,
  aprendizajeController,
  quillManager,
  wordExportService
});

function initEditorsIfNeeded() {
  const toolbarOptions = [
    ['bold', 'italic', 'underline', 'strike'],
    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
    ['link', 'blockquote', 'code-block'],
    ['clean']
  ];

  quillManager.initializeEditor('#inicio-editor', 'snow', {
    modules: { toolbar: toolbarOptions },
    placeholder: 'Escribe el Inicio...'
  });

  quillManager.initializeEditor('#desarrollo-editor', 'snow', {
    modules: { toolbar: toolbarOptions },
    placeholder: 'Escribe el Desarrollo...'
  });

  quillManager.initializeEditor('#conclusion-editor', 'snow', {
    modules: { toolbar: toolbarOptions },
    placeholder: 'Escribe la Conclusión...'
  });
}

function actualizarDatosSesionDesdeLabels() {
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
  actualizarDatosSesionDesdeLabels();

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

document.addEventListener("DOMContentLoaded", async () => {
  console.log('🎬 DOMContentLoaded - Inicializando editores...');
  initEditorsIfNeeded();

  console.log('✅ Editores Quill inicializados (si estaban presentes)');

  renderFicha();

  const sesion = window.datosSesion || window.sesionData || {};
  if (sesion && (sesion.titulo || sesion.proposito_sesion)) {
    const apr = Aprendizaje.fromSessionData(sesion);
    aprendizajeController.aprendizajes = [];
    aprendizajeController.agregarAprendizaje(apr);
    fichaController.setAprendizajes(aprendizajeController.obtenerAprendizajes());
    console.log('⚡ Aprendizaje cargado desde session:', apr);
  }

  const formContainer = document.querySelector('.lg\\:col-span-2');
  if (formContainer) {
    const inputs = formContainer.querySelectorAll('input, textarea');

    inputs.forEach(el => {
      el.addEventListener('change', () => {
        console.log('📝 Campo modificado, guardando aprendizaje...');
        guardarAprendizaje();
        enviarMomentosASession();
      });
    });

    console.log(`✅ ${inputs.length} inputs con listeners agregados`);
  } else {
    console.error('❌ No se encontró el contenedor del formulario');
  }

  console.log('🎉 Inicialización completa');
});

if (window.Livewire && typeof window.Livewire.hook === 'function') {
  try {
    Livewire.hook('message.processed', (message, component) => {
      setTimeout(() => {
        initEditorsIfNeeded();
      }, 50);
    });
    console.log('🔁 Livewire hook: message.processed agregado para re-inicializar editores');
  } catch (e) {
    console.warn('Livewire presente pero no se pudo registrar hook:', e);
  }
}

const observer = new MutationObserver((mutations) => {
  if (document.querySelector('#inicio-editor') || document.querySelector('#desarrollo-editor') || document.querySelector('#conclusion-editor')) {
    initEditorsIfNeeded();
  }
});
observer.observe(document.body, { childList: true, subtree: true });

function renderFicha() {
  quillManager.setMarkdown('#inicio-editor', fichaController.inicio.texto || "");
  quillManager.setMarkdown('#desarrollo-editor', fichaController.desarrollo.texto || "");
  quillManager.setMarkdown('#conclusion-editor', fichaController.conclusion.texto || "");

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

  btn.innerHTML = `
    <span>⏳</span>
    <span>Generando Ficha...</span>
  `;

  quillManager.setContent('#inicio-editor', "Generando...");
  quillManager.setContent('#desarrollo-editor', "Generando...");
  quillManager.setContent('#conclusion-editor', "Generando...");

  guardarAprendizaje();

  try {
    console.log('📝 Aprendizajes guardados:', aprendizajeController.obtenerAprendizajes());
    await fichaController.generarTodo();
    console.log('✅ Ficha generada exitosamente');
    enviarMomentosASession();

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
async function enviarMomentosASession() {
  const inicio = document.getElementById('inicioInput')?.value || '';
  const desarrollo = document.getElementById('desarrolloInput')?.value || '';
  const cierre = document.getElementById('conclusionInput')?.value || '';
  try {
    const data = await SesionMomentoService.saveMomentos(inicio, desarrollo, cierre);
    console.log('✅ Momentos guardados en sesión:', data);
  } catch (error) {
  }
}

window.regenerar = async (seccion, e) => {
  const btn = e && e.target
    ? e.target
    : document.querySelector(`button[onclick^="regenerar('${seccion}")`) // intento de fallback
    || document.querySelector(`button[onclick*="regenerar('${seccion}')"]`)
    || null;

  if (btn) {
    btn.disabled = true;
    btn.textContent = "⏳ Generando...";
  }

  guardarAprendizaje();

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

function getSesionIdFromUrl() {
  const match = window.location.pathname.match(/sesions\/(\d+)/);
  return match ? match[1] : null;
}

async function cargarMomentosSiEdit() {
  const sesionId = getSesionIdFromEditUrl();
  if (sesionId) {
    try {
      const response = await SesionMomentoService.getMomentosById(sesionId);
      if (response && response.momentos && response.momentos.length > 0) {
        const momento = response.momentos[0];
        document.getElementById('inicioInput').value = momento.inicio || '';
        document.getElementById('desarrolloInput').value = momento.desarrollo || '';
        document.getElementById('conclusionInput').value = momento.cierre || '';
        quillManager.setMarkdown('#inicio-editor', momento.inicio || '');
        quillManager.setMarkdown('#desarrollo-editor', momento.desarrollo || '');
        quillManager.setMarkdown('#conclusion-editor', momento.cierre || '');
        console.log('✅ Momentos cargados en edición:', momento);
      }
    } catch (error) {
    }
  }
}
cargarMomentosSiEdit();
