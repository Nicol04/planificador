import { FichaController } from "./controllers/FichaController.js";
import { AprendizajeController } from "./controllers/AprendizajeController.js";
import { QuillEditorManager } from "./services/QuillEditorManager.js";
import { WordExportService } from "./services/WordExportService.js";

const API_KEY = "AIzaSyAvNoL4EJw-sGpzortVelmpdMRLlznIzZA"; // ⚠️ No seguro para producción
const fichaController = new FichaController(API_KEY);
const aprendizajeController = new AprendizajeController();
const quillManager = new QuillEditorManager();
const wordExportService = new WordExportService(quillManager);

// ✅ ACTUALIZADO: Selecciona inputs del panel izquierdo usando un selector más específico
function leerSesionCompletaDeFormulario({ temaModal = "" } = {}) {
  // DATOS DE LA SESIÓN
  const datos = {
    titulo: document.querySelector('input[name="titulo"]')?.value || "",
    proposito_sesion: document.querySelector('textarea[name="proposito_sesion"]')?.value || "",
    aula_curso_id: document.querySelector('input[name="aula_curso_id"]')?.value || "",
    curso: document.querySelector('input[name="curso"]')?.value || "",
    aula: document.querySelector('input[name="aula"]')?.value || "",
    nivel: document.querySelector('input[name="nivel"]')?.value || "",
    tema: temaModal || document.querySelector('input[name="tema"]')?.value || "",
    // Otros campos si necesitas...
  };

  // PROPÓSITOS DE APRENDIZAJE
  const propositos = {
    competencia: document.querySelector('input[name="competencia"]')?.value || "",
    capacidades: document.querySelector('input[name="capacidades"]')?.value || "",
    desempenos: document.querySelector('input[name="desempenos"]')?.value || "",
    criterios: document.querySelector('textarea[name="criterios"]')?.value || "",
    evidencias: document.querySelector('textarea[name="evidencias"]')?.value || "",
    instrumentos: document.querySelector('input[name="instrumentos"]')?.value || "",
  };

  // ENFOQUES TRANSVERSALES
  const enfoques = {
    enfoques: document.querySelector('select[name="enfoque_transversal_ids"]')?.value || "",
    competencias: document.querySelector('select[name="competencias_transversales_ids"]')?.value || "",
    capacidades: document.querySelector('select[name="capacidades_transversales_ids"]')?.value || "",
    desempenos: document.querySelector('select[name="desempeno_transversal_ids"]')?.value || "",
    criterios: document.querySelector('textarea[name="criterios_transversales"]')?.value || "",
    instrumentos: document.querySelector('select[name="instrumentos_transversales_ids"]')?.value || "",
    instrumentos_personalizados: document.querySelector('textarea[name="instrumentos_transversales_personalizados"]')?.value || "",
  };

  // MOMENTOS DE LA SESIÓN
  const momentos = {
    inicio: document.getElementById('inicio')?.value || "",
    desarrollo: document.getElementById('desarrollo')?.value || "",
    conclusion: document.getElementById('cierre')?.value || "",
  };

  return {
    datos,
    propositos,
    enfoques,
    momentos
  };
}


// Guardar aprendizaje y actualizar contexto
function guardarAprendizaje() {
  const data = leerSesionCompletaDeFormulario();
  console.log("Datos recogidos del formulario:", data); // 👈 Prueba aquí
  aprendizajeController.aprendizajes = [];
  aprendizajeController.agregarAprendizaje(data);
  fichaController.setAprendizajes(aprendizajeController.obtenerAprendizajes());
}

// Cargar ficha al iniciar
document.addEventListener('DOMContentLoaded', function() {
  // Abrir modal al hacer clic en el botón
  const btnGenerarMomentos = document.getElementById('btn-generar-momentos');
  if (btnGenerarMomentos) {
    btnGenerarMomentos.addEventListener('click', function() {
      document.getElementById('modal-generar-momentos').classList.remove('hidden');
    });
  }

  // Exponer funciones globales para el modal
  window.cerrarModalGenerarMomentos = function() {
    document.getElementById('modal-generar-momentos').classList.add('hidden');
    document.getElementById('momentos-error').classList.add('hidden');
  };

  window.generarMomentos = async function() {
    const tema = document.getElementById('tema-momentos').value.trim();
    if (!tema) {
      document.getElementById('momentos-error').textContent = 'Por favor ingrese el tema.';
      document.getElementById('momentos-error').classList.remove('hidden');
      return;
    }

    // Obtén todos los datos de la sesión, incluyendo el tema del modal
    const sesionCompleta = leerSesionCompletaDeFormulario({ temaModal: tema });

    // Prueba: muestra los datos en consola
    console.log("Datos para IA:", sesionCompleta);

    // Si tienes la función en fichaController:
    // const momentosGenerados = await fichaController.generarMomentos(sesionCompleta);

    // Simulación para pruebas (elimina esto cuando uses la IA real)
    const momentosGenerados = {
      inicio: "Texto generado para el inicio...",
      desarrollo: "Texto generado para el desarrollo...",
      conclusion: "Texto generado para el cierre..."
    };

    // Llena los campos del formulario con los textos generados
    document.getElementById('inicio').value = momentosGenerados.inicio || "";
    document.getElementById('desarrollo').value = momentosGenerados.desarrollo || "";
    document.getElementById('cierre').value = momentosGenerados.conclusion || "";

    cerrarModalGenerarMomentos();
  };
});

// ✅ ACTUALIZADO: Usar QuillEditorManager para renderizar Markdown
function renderFicha() {
  quillManager.setMarkdown('#inicio-editor', fichaController.inicio.texto || "Pendiente de generación...");
  quillManager.setMarkdown('#desarrollo-editor', fichaController.desarrollo.texto || "Pendiente de generación...");
  quillManager.setMarkdown('#conclusion-editor', fichaController.conclusion.texto || "Pendiente de generación...");
}

window.renderFicha = renderFicha;

window.generarFicha = async () => {
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
  await fichaController.generarTodo();

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