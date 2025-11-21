import GeminiService from '../service/GeminiService.js';
import { getFirstImage } from '../service/SearchImage.js';
import EjercicioSessionService from './services/EjercicioSessionService.js';

import ClassificationExercise from './models/ClassificationExercise.js';
import ClozeExercise from './models/ClozeExercise.js';
import SelectionExercise from './models/SelectionExercise.js';
import ReflectionExercise from './models/ReflectionExercise.js';
import ProgressIndicator from './ProgressIndicator.js';

// Importar controladores
import AppController from './controllers/AppController.js';
import ExerciseSyncController from './controllers/ExerciseSyncController.js';

const GEMINI_API_KEY = window.userGeminiKey ?? null;
const SEARCH_API_KEY = window.userGeminiKey ?? null;

if (!GEMINI_API_KEY) {
    console.warn("⚠️ No se encontró la clave Gemini del usuario autenticado.");
} else {
    console.log("✓ Clave Gemini cargada correctamente");
    // Ocultar la clave en producción - solo mostrar los primeros 8 caracteres
    console.log(`🔑 Clave (parcial): ${GEMINI_API_KEY.substring(0, 8)}...`);
}

// Instanciar el servicio Gemini
const gemini = new GeminiService(GEMINI_API_KEY);

const ejercicioSessionService = new EjercicioSessionService();

// Instanciar controlador principal de la aplicación
const appController = new AppController();

// Instanciar controlador de sincronización
const exerciseSyncController = new ExerciseSyncController(ejercicioSessionService);

// Hacer disponible globalmente para los modelos
window.ejercicioSessionService = ejercicioSessionService;

/**
 * Guardar un ejercicio en sesión de Laravel después de generarlo o en modo edición
 * @param {Object} ejercicioInstancia - Instancia del ejercicio (SelectionExercise, etc.)
 * @param {boolean} esPrimero - Si es el primer ejercicio (para incluir descripción)
 */
async function guardarEjercicioEnSesion(ejercicioInstancia, esPrimero = false) {
	try {
		const urlPattern = /\/docente\/ficha-aprendizajes\/(\d+)\/edit/;
		const match = window.location.pathname.match(urlPattern);
		const isEditMode = !!match;

		const tipo = ejercicioInstancia.tipo;
		const contenido = ejercicioInstancia.getJSON();

		// Construimos payload base
		const payload = {
			tipo,
			contenido
		};

		// Si corresponde, agregar descripción
		if (isEditMode || esPrimero) {
			const descripcion = document.getElementById('Contenido')?.value || '';
			if (descripcion) {
				payload.descripcion = descripcion;
				console.log(`📝 Guardando descripción: "${descripcion.substring(0, 50)}..."`);
			}
		}

		// Agregar título (si existe)
		const nombre = document.getElementById('titulo')?.value || '';
		if (nombre) {
			payload.nombre = nombre;
		}

		// Capturar grado y tipo de ejercicio (estos estaban faltando en el payload)
		payload.grado = String(document.getElementById('grado')?.value || '');
		payload.tipo_ejercicio = String(document.getElementById('TipoFicha')?.value || '');

		console.log("📦 Payload final:", payload);

		const response = await ejercicioSessionService.store(
			payload.tipo,
			payload.contenido,
			payload.descripcion ?? null,
			payload.nombre ?? null,
			payload.grado,
			payload.tipo_ejercicio
		);

		ejercicioInstancia.setSessionId(response.data.id);

		console.log(`💾 Ejercicio ${tipo} guardado con ID: ${response.data.id}`);
		return response.data;

	} catch (error) {
		console.error(`❌ Error guardando ejercicio ${tipo}:`, error);
		throw error;
	}
}

/**
 * Detectar si estamos en modo edición y cargar ejercicios existentes
 */
async function cargarEjerciciosSiEsEdicion() {
	// Detectar URL del tipo: /docente/ficha-aprendizajes/1/edit
	const urlPattern = /\/docente\/ficha-aprendizajes\/(\d+)\/edit/;
	const match = window.location.pathname.match(urlPattern);

	if (!match) {
		console.log('🔍 [Main] No estamos en modo edición, omitiendo carga de ejercicios');
		return;
	}

	const fichaId = match[1];
	console.log(`📂 [Main] Detectado modo edición para FichaAprendizaje ID: ${fichaId}`);

	const fichaContenido = document.getElementById('ficha-contenido');
	if (!fichaContenido) {
		console.error('❌ [Main] Contenedor #ficha-contenido no encontrado');
		return;
	}

	fichaContenido.innerHTML = '<div class="text-center text-slate-500 py-8">Cargando ejercicios... ⏳</div>';

	try {
		// Obtener ejercicios desde el backend
		const response = await fetch(`/fichas/${fichaId}/ejercicios`, {
			method: 'GET',
			headers: {
				'Accept': 'application/json',
				'X-Requested-With': 'XMLHttpRequest'
			}
		});

		if (!response.ok) {
			throw new Error(`Error ${response.status}: ${response.statusText}`);
		}

		const data = await response.json();
		console.log(`✓ [Main] ${data.data.count} ejercicios cargados para "${data.data.nombre}"`);

		// Prefill título y descripción de la ficha en la vista de edición
		const tituloInput = document.getElementById('titulo');
		if (tituloInput && data.data.nombre) {
			tituloInput.value = data.data.nombre;
			console.log(`📝 [Main] Título de ficha prellenado: "${data.data.nombre}"`);
		}

		const contenidoTextarea = document.getElementById('Contenido');
		if (contenidoTextarea && data.data.descripcion) {
			contenidoTextarea.value = data.data.descripcion;
			console.log('📝 [Main] Contenido de ficha prellenado desde descripción');
		}

		// Prefill grado y tipo_ejercicio si están disponibles
		const gradoSelect = document.getElementById('grado');
		if (gradoSelect && data.data.grado) {
			gradoSelect.value = data.data.grado;
			console.log(`📝 [Main] Grado prellenado: "${data.data.grado}"`);
		}

		const tipoSelect = document.getElementById('TipoFicha');
		if (tipoSelect && data.data.tipo_ejercicio) {
			tipoSelect.value = data.data.tipo_ejercicio;
			console.log(`📝 [Main] Tipo de ejercicio prellenado: "${data.data.tipo_ejercicio}"`);
		}

		// 🔄 Sincronizar metadatos inmediatamente en sesión
		console.log('🔄 [Main] Sincronizando metadatos en sesión...');
		await sincronizarMetadatosFicha();

		// Limpiar contenedor
		fichaContenido.innerHTML = '';

		if (data.data.count === 0) {
			fichaContenido.innerHTML = '<div class="text-center text-slate-400 py-8">No hay ejercicios asociados a esta ficha</div>';
			return;
		}

		// Sincronizar ejercicios de BD con sesión
		console.log('🔄 [Main] Sincronizando ejercicios de BD con sesión...');
		const syncMap = await exerciseSyncController.syncFromDatabase(data.data.ejercicios);
		console.log(`✓ [Main] ${syncMap.size} ejercicios sincronizados con sesión`);

		// Renderizar cada ejercicio
		for (const ejercicioData of data.data.ejercicios) {
			await renderizarEjercicio(ejercicioData, fichaContenido);
		}

		console.log('🎉 [Main] Todos los ejercicios renderizados exitosamente');
	} catch (error) {
		console.error('❌ [Main] Error cargando ejercicios:', error);
		fichaContenido.innerHTML = `<div class="text-red-600 p-4 bg-red-50 rounded">Error al cargar ejercicios: ${error.message}</div>`;
	}
}

/**
 * Renderizar un ejercicio desde datos de BD
 * @param {Object} ejercicioData - Datos del ejercicio {id, tipo, contenido}
 * @param {HTMLElement} contenedor - Contenedor donde renderizar
 */
async function renderizarEjercicio(ejercicioData, contenedor) {
	const { id: bdId, tipo, contenido } = ejercicioData;
	console.log(`🎨 [Main] Renderizando ejercicio tipo: ${tipo} (BD ID: ${bdId})`);

	// Crear contenedor individual
	const contenedorEjercicio = document.createElement('div');
	contenedorEjercicio.className = 'mb-8';

	let ejercicioInstancia = null;

	try {
		switch (tipo) {
			case 'SelectionExercise':
				ejercicioInstancia = new SelectionExercise(contenido.title, contenido.description);
				for (const option of contenido.options || []) {
					await ejercicioInstancia.add(option.imageSrc, option.text);
				}
				break;

			case 'ClassificationExercise':
				ejercicioInstancia = new ClassificationExercise(contenido.title, contenido.description);
				for (const item of contenido.items || []) {
					await ejercicioInstancia.add(item.imageSrc, item.text);
				}
				break;

			case 'ClozeExercise':
				ejercicioInstancia = new ClozeExercise(contenido.title, contenido.description);
				for (const item of contenido.items || []) {
					await ejercicioInstancia.add(item.imageSrc, item.placeholder);
				}
				break;

			case 'ReflectionExercise':
				ejercicioInstancia = new ReflectionExercise(contenido.title, contenido.description);
				ejercicioInstancia.setText(contenido.text || '');
				if (contenido.imageSrc) {
					await ejercicioInstancia.setImage(contenido.imageSrc);
				}
				for (const question of contenido.questions || []) {
					ejercicioInstancia.addQuestion(question);
				}
				break;

			default:
				throw new Error(`Tipo de ejercicio desconocido: ${tipo}`);
		}

		// Obtener sessionId desde el controlador de sincronización
		const sessionId = exerciseSyncController.getSessionId(bdId);
		if (sessionId) {
			ejercicioInstancia.setSessionId(sessionId);
			console.log(`🔗 [Main] Ejercicio vinculado: BD ${bdId} -> Sesión ${sessionId}`);
		} else {
			console.warn(`⚠️ [Main] No se encontró sessionId para ejercicio BD ${bdId}`);
		}

		// Renderizar
		ejercicioInstancia.renderInto(contenedorEjercicio);
		contenedor.appendChild(contenedorEjercicio);
		console.log(`✓ [Main] Ejercicio ${tipo} renderizado`);

	} catch (error) {
		console.error(`❌ [Main] Error renderizando ${tipo}:`, error);
		const errorDiv = document.createElement('div');
		errorDiv.className = 'text-red-600 p-4 bg-red-50 rounded mb-4';
		errorDiv.textContent = `Error renderizando ${tipo}: ${error.message}`;
		contenedor.appendChild(errorDiv);
	}
}


export async function generarFicha() {
	const btn = document.getElementById('generar-btn');
	const btnText = document.getElementById('btn-text');
	const tipoFicha = document.getElementById('TipoFicha').value;
	const gradoPrimaria = document.getElementById('grado').value;
	const contenido = document.getElementById('Contenido').value;
	const autoAsignarImagenes = document.getElementById('AutoAsignarImagenes')?.checked || false;

	// Obtener configuración avanzada desde el controlador
	const { temperature, topP, topK } = appController.getAdvancedConfigController().getConfig();

	if (!contenido || tipoFicha === 'Selecciona una Opción' || !gradoPrimaria) {
		alert('Por favor, selecciona un tipo de ficha, un grado y escribe el contenido');
		return;
	}

	// Hacer disponible getFirstImage globalmente para los modelos
	window.getFirstImage = autoAsignarImagenes ? getFirstImage : null;
	console.log('🖼️ [Main] Asignación automática de imágenes:', autoAsignarImagenes ? 'ACTIVADA' : 'DESACTIVADA');

	const fichaContenido = document.getElementById('ficha-contenido');
	btn.disabled = true;
	btnText.textContent = 'Generando...';

	// Inicializar sistema de indicadores de progreso
	const progressIndicator = new ProgressIndicator('ficha-contenido');
	progressIndicator.init();

	const options = {
		temperature,
		topP,
		topK,
		responseMimeType: 'text/plain',
		systemInstruction: 'Responde únicamente con el esquema de ficha en formato JSON, sin explicaciones ni texto adicional.'
	};

	try {
		// Limpiar el contenedor
		fichaContenido.innerHTML = '';

		if (tipoFicha === 'Todos') {
			// Generar los 4 tipos de ejercicios
			const tipos = [
				{ clase: SelectionExercise, nombre: 'SelectionExercise', propiedad: 'options' },
				{ clase: ClassificationExercise, nombre: 'ClassificationExercise', propiedad: 'items' },
				{ clase: ClozeExercise, nombre: 'ClozeExercise', propiedad: 'items' },
				{ clase: ReflectionExercise, nombre: 'ReflectionExercise', propiedad: 'questions' }
			];

			console.log('🚀 Iniciando generación de 4 ejercicios...');

			for (let i = 0; i < tipos.length; i++) {
				const tipo = tipos[i];
				console.log(`\n📝 [${i + 1}/4] Generando ejercicio: ${tipo.nombre}`);

				try {
					// Crear contenedor individual para cada ejercicio
					const contenedorEjercicio = document.createElement('div');
					contenedorEjercicio.className = 'mb-8';
					contenedorEjercicio.id = `ejercicio-${i}`;
					fichaContenido.appendChild(contenedorEjercicio);

					// Crear indicador de progreso dentro del contenedor del ejercicio
					const exerciseProgress = new ProgressIndicator(`ejercicio-${i}`);
					exerciseProgress.init();
					exerciseProgress.createExerciseIndicator(tipo.nombre, 0);
					exerciseProgress.addStep(0, 'Cargando esquema de ejercicio...', 'loading');
					exerciseProgress.updateProgress(0, 10);

					// Crear instancia del ejercicio
					const ejercicioInstancia = new tipo.clase('', '');

					const esquemaFicha = tipo.clase.getJSONSchemaString();
					console.log(`📋 Esquema cargado para ${tipo.nombre}`);
					exerciseProgress.updateLastStepStatus(0, 'success');
					exerciseProgress.updateProgress(0, 20);

					const prompt = `Genera únicamente el esquema de ficha en formato JSON para un ejercicio de tipo "${tipo.nombre}", dirigido a niños de ${gradoPrimaria}° grado de primaria. Usa solo el siguiente texto como base: "${contenido}". El esquema debe seguir este ejemplo: ${esquemaFicha} En cada key "imageSrc" coloca un nombre clave descriptivo para buscar una imagen (si es un ejercicio de Clasiffication entonces ponemos imageSrc de forma aletoria texto), por ejemplo: "bandera Perú". Adapta el vocabulario y la complejidad para niños de ${gradoPrimaria}° grado. No incluyas explicaciones ni texto adicional, solo el JSON.`;

					exerciseProgress.addStep(0, 'Enviando petición a IA...', 'loading');
					exerciseProgress.updateProgress(0, 30);
					console.log(`🌐 Enviando petición a Gemini...`);

					const result = await gemini.generateContent(prompt, options);
					let jsonText = result.text || '';
					console.log(`✅ Respuesta recibida (${jsonText.length} caracteres)`);
					exerciseProgress.updateLastStepStatus(0, 'success');
					exerciseProgress.updateProgress(0, 50);

					exerciseProgress.addStep(0, 'Procesando respuesta...', 'loading');
					// Limpiar la respuesta de Gemini
					jsonText = jsonText.trim().replace(/```json/g, '').replace(/```/g, '').trim();
					console.log(`🧹 Respuesta limpiada`);

					const jsonData = JSON.parse(jsonText);
					console.log(`✓ JSON parseado correctamente:`, jsonData);
					exerciseProgress.updateLastStepStatus(0, 'success');
					exerciseProgress.updateProgress(0, 60);

					// Configurar título y descripción
					ejercicioInstancia.setTitle(jsonData.title || '');
					ejercicioInstancia.setDescription(jsonData.description || '');

					exerciseProgress.addStep(0, 'Creando estructura del ejercicio...', 'loading');
					exerciseProgress.updateProgress(0, 70);

					const datos = jsonData[tipo.propiedad] || [];
					console.log(`📦 Agregando ${datos.length} items al ejercicio`);
					exerciseProgress.updateLastStepStatus(0, 'success');

					exerciseProgress.addStep(0, `Agregando ${datos.length} elementos...`, 'loading');
					exerciseProgress.updateProgress(0, 80);

					// Agregar elementos
					if (tipo.nombre === 'ReflectionExercise') {
						ejercicioInstancia.setText(jsonData.text || '');
						if (jsonData.imageSrc) {
							await ejercicioInstancia.setImage(jsonData.imageSrc);
						}
						for (let j = 0; j < datos.length; j++) {
							ejercicioInstancia.addQuestion(datos[j]);
						}
					} else if (tipo.nombre === 'ClozeExercise') {
						for (let j = 0; j < datos.length; j++) {
							const item = datos[j];
							await ejercicioInstancia.add(item.imageSrc, item.placeholder);
						}
					} else {
						for (let j = 0; j < datos.length; j++) {
							const item = datos[j];
							await ejercicioInstancia.add(item.imageSrc, item.text);
						}
					}

					exerciseProgress.updateLastStepStatus(0, 'success');
					exerciseProgress.addStep(0, 'Renderizando ejercicio...', 'loading');
					exerciseProgress.updateProgress(0, 95);

					// Limpiar el contenedor y renderizar versión final editable
					contenedorEjercicio.innerHTML = '';
					ejercicioInstancia.renderInto(contenedorEjercicio);
					console.log(`✅ Ejercicio ${tipo.nombre} renderizado exitosamente`);

					// Guardar en sesión de Laravel (el primero incluye descripción)
					await guardarEjercicioEnSesion(ejercicioInstancia, i === 0);

					// Delay de 500ms entre llamados (excepto en el último)
					if (i < tipos.length - 1) {
						console.log('⏱️ Esperando 500ms antes del siguiente llamado...');
						await new Promise(resolve => setTimeout(resolve, 500));
					}
				} catch (error) {
					console.error(`❌ Error generando ${tipo.nombre}:`, error);

					// Mostrar error en el contenedor del ejercicio
					contenedorEjercicio.innerHTML = `
				<div class="text-red-600 p-4 bg-red-50 border-2 border-red-200 rounded-xl">
					<div class="flex items-center gap-3">
						<svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
						</svg>
						<div>
							<p class="font-semibold">Error en ${tipo.nombre}</p>
							<p class="text-sm">${error.message}</p>
						</div>
					</div>
				</div>
			`;
				}
			}
			console.log('🎉 Generación de ejercicios completada');
		} else {
			// Generar un solo tipo de ejercicio
			// Crear contenedor
			const contenedorEjercicio = document.createElement('div');
			contenedorEjercicio.className = 'mb-8';
			contenedorEjercicio.id = 'ejercicio-unico';
			fichaContenido.appendChild(contenedorEjercicio);

			// Crear indicador de progreso dentro del contenedor
			const exerciseProgress = new ProgressIndicator('ejercicio-unico');
			exerciseProgress.init();
			exerciseProgress.createExerciseIndicator(tipoFicha, 0);
			exerciseProgress.addStep(0, 'Cargando esquema de ejercicio...', 'loading');
			exerciseProgress.updateProgress(0, 10);

			let esquemaFicha = '';
			let ClaseEjercicio = null;
			let propiedad = '';

			switch (tipoFicha) {
				case 'ClassificationExercise':
					esquemaFicha = ClassificationExercise.getJSONSchemaString();
					ClaseEjercicio = ClassificationExercise;
					propiedad = 'items';
					break;
				case 'ClozeExercise':
					esquemaFicha = ClozeExercise.getJSONSchemaString();
					ClaseEjercicio = ClozeExercise;
					propiedad = 'items';
					break;
				case 'SelectionExercise':
					esquemaFicha = SelectionExercise.getJSONSchemaString();
					ClaseEjercicio = SelectionExercise;
					propiedad = 'options';
					break;
				case 'ReflectionExercise':
					esquemaFicha = ReflectionExercise.getJSONSchemaString();
					ClaseEjercicio = ReflectionExercise;
					propiedad = 'questions';
					break;
			}

			// Crear instancia del ejercicio
			const ejercicioInstancia = new ClaseEjercicio('', '');

			exerciseProgress.updateLastStepStatus(0, 'success');
			exerciseProgress.updateProgress(0, 20);

			const prompt = `Genera únicamente el esquema de ficha en formato JSON para un ejercicio de tipo "${tipoFicha}", dirigido a niños de ${gradoPrimaria}° grado de primaria. Usa solo el siguiente texto como base: "${contenido}". El esquema debe seguir este ejemplo: ${esquemaFicha} En cada key "imageSrc" coloca un nombre clave descriptivo para buscar una imagen, por ejemplo: "bandera Perú". Adapta el vocabulario y la complejidad para niños de ${gradoPrimaria}° grado. No incluyas explicaciones ni texto adicional, solo el JSON.`;

			exerciseProgress.addStep(0, 'Enviando petición a IA...', 'loading');
			exerciseProgress.updateProgress(0, 30);
			console.log('🌐 Enviando petición a Gemini...');

			const result = await gemini.generateContent(prompt, options);
			let jsonText = result.text || '';
			console.log(`✅ Respuesta recibida de Gemini: ${jsonText.length} caracteres`);
			exerciseProgress.updateLastStepStatus(0, 'success');
			exerciseProgress.updateProgress(0, 50);

			exerciseProgress.addStep(0, 'Procesando respuesta...', 'loading');
			// Limpiar la respuesta de Gemini
			jsonText = jsonText.trim().replace(/```json/g, '').replace(/```/g, '').trim();
			console.log('🧹 Respuesta limpiada');
			const jsonData = JSON.parse(jsonText);
			console.log('✓ JSON parseado:', jsonData);
			exerciseProgress.updateLastStepStatus(0, 'success');
			exerciseProgress.updateProgress(0, 60);

			exerciseProgress.addStep(0, 'Creando estructura del ejercicio...', 'loading');
			exerciseProgress.updateProgress(0, 70);

			// Configurar título y descripción
			ejercicioInstancia.setTitle(jsonData.title || '');
			ejercicioInstancia.setDescription(jsonData.description || '');

			const datos = jsonData[propiedad] || [];
			exerciseProgress.updateLastStepStatus(0, 'success');

			exerciseProgress.addStep(0, `Agregando ${datos.length} elementos...`, 'loading');
			exerciseProgress.updateProgress(0, 80);

			// Agregar elementos
			if (tipoFicha === 'ReflectionExercise') {
				ejercicioInstancia.setText(jsonData.text || '');
				if (jsonData.imageSrc) {
					await ejercicioInstancia.setImage(jsonData.imageSrc);
				}
				for (let j = 0; j < datos.length; j++) {
					ejercicioInstancia.addQuestion(datos[j]);
				}
			} else if (tipoFicha === 'ClozeExercise') {
				for (let j = 0; j < datos.length; j++) {
					const item = datos[j];
					await ejercicioInstancia.add(item.imageSrc, item.placeholder);
				}
			} else {
				for (let j = 0; j < datos.length; j++) {
					const item = datos[j];
					await ejercicioInstancia.add(item.imageSrc, item.text);
				}
			}

			exerciseProgress.updateLastStepStatus(0, 'success');
			exerciseProgress.addStep(0, 'Renderizando ejercicio...', 'loading');
			exerciseProgress.updateProgress(0, 95);

			// Limpiar el contenedor y renderizar versión final editable
			contenedorEjercicio.innerHTML = '';
			ejercicioInstancia.renderInto(contenedorEjercicio);
			console.log('✅ Ejercicio renderizado exitosamente');

			// Guardar en sesión de Laravel (único ejercicio, es el primero)
			await guardarEjercicioEnSesion(ejercicioInstancia, true);
		}
	} catch (error) {
		fichaContenido.innerHTML = `<div class="text-red-600 p-4 bg-red-50 rounded">Error: ${error.message}</div>`;
	} finally {
		btn.disabled = false;
		btnText.textContent = 'Generar Ficha Completa';
	}
}

/**
 * Sincronizar metadatos de la ficha en sesión cuando cambian
 * Esta función se invoca cuando se modifican campos críticos en modo edición
 */
async function sincronizarMetadatosFicha() {
	try {
		const nombre = document.getElementById('titulo')?.value || '';
		const descripcion = document.getElementById('Contenido')?.value || '';
		const grado = document.getElementById('grado')?.value || '';
		const tipo_ejercicio = document.getElementById('TipoFicha')?.value || '';

		// Enviar al backend para actualizar sesión
		const response = await fetch('/session/ejercicios/metadata', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Accept': 'application/json',
				'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
				'X-Requested-With': 'XMLHttpRequest'
			},
			body: JSON.stringify({ nombre, descripcion, grado, tipo_ejercicio })
		});

		if (response.ok) {
			console.log('✓ [Main] Metadatos de ficha sincronizados en sesión');
		}
	} catch (error) {
		console.error('❌ [Main] Error sincronizando metadatos:', error);
	}
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
	// 🛡️ Protección contra submit no deseado del formulario de Filament
	// Interceptar todos los formularios en la página
	const forms = document.querySelectorAll('form');
	forms.forEach(form => {
		form.addEventListener('submit', (e) => {
			// Verificar si el submit viene del botón oficial de Filament
			const submitButton = e.submitter;
			if (!submitButton || !submitButton.hasAttribute('data-filament-action')) {
				// Si no es un botón oficial de Filament, verificar si es nuestro botón de generar
				if (submitButton && submitButton.id === 'generar-btn') {
					console.log('🚫 [Main] Submit interceptado desde botón generar - prevenido');
					e.preventDefault();
					e.stopPropagation();
					return false;
				}
			}
		});
	});
	console.log('🛡️ [Main] Protección de formulario activada');

	// 🛡️ Prevenir submit con Enter en inputs y textareas
	const inputs = document.querySelectorAll('input, textarea');
	inputs.forEach(input => {
		// Excepto para inputs de búsqueda que sí deben permitir Enter
		if (!input.id.includes('Search') && !input.id.includes('modal')) {
			input.addEventListener('keypress', (e) => {
				if (e.key === 'Enter' && input.tagName.toLowerCase() !== 'textarea') {
					console.log('🚫 [Main] Enter interceptado en input - prevenido');
					e.preventDefault();
					return false;
				}
			});
		}
	});
	console.log('🛡️ [Main] Protección Enter en inputs activada');

	// Detectar si estamos en modo creación y limpiar variables de sesión
	if (window.location.pathname.match(/\/docente\/ficha-aprendizajes\/create$/)) {
		console.log('[LOG][Main] MODO CREACIÓN detectado, limpiando variables de sesión...');
		ejercicioSessionService.clear().then(() => {
			console.log('[LOG][Main] Variables de sesión limpiadas correctamente en modo creación');
		}).catch((err) => {
			console.error('[LOG][Main] Error al limpiar variables de sesión en modo creación:', err);
		});
	} else {
		// Verificar si estamos en modo edición y cargar ejercicios existentes
		cargarEjerciciosSiEsEdicion();
	}

	// 🔄 Agregar listeners para sincronizar metadatos en modo edición
	const urlPattern = /\/docente\/ficha-aprendizajes\/(\d+)\/edit/;
	const isEditMode = window.location.pathname.match(urlPattern);
	
	if (isEditMode) {
		console.log('📝 [Main] Modo edición: activando sincronización automática de metadatos');
		
		// Sincronizar cuando cambian los campos
		const tituloInput = document.getElementById('titulo');
		const contenidoTextarea = document.getElementById('Contenido');
		const gradoSelect = document.getElementById('grado');
		const tipoSelect = document.getElementById('TipoFicha');
		
		if (tituloInput) {
			tituloInput.addEventListener('blur', sincronizarMetadatosFicha);
		}
		if (contenidoTextarea) {
			contenidoTextarea.addEventListener('blur', sincronizarMetadatosFicha);
		}
		if (gradoSelect) {
			gradoSelect.addEventListener('change', sincronizarMetadatosFicha);
		}
		if (tipoSelect) {
			tipoSelect.addEventListener('change', sincronizarMetadatosFicha);
		}
		
		console.log('✓ [Main] Listeners de sincronización de metadatos activados');
	}

	const btn = document.getElementById('generar-btn');
	if (btn) {
		btn.addEventListener('click', generarFicha);
	}



	// Botón de limpiar sesión
	const limpiarSesionBtn = document.getElementById('limpiar-sesion-btn');
	if (limpiarSesionBtn) {
		limpiarSesionBtn.addEventListener('click', async () => {
			if (confirm('¿Estás seguro de que deseas limpiar todos los ejercicios de la sesión?')) {
				try {
					await ejercicioSessionService.clear();
					alert('✓ Sesión limpiada correctamente');
				} catch (error) {
					alert('❌ Error al limpiar la sesión: ' + error.message);
				}
			}
		});
	}

	// Botón de ver ejercicios en sesión
	const verSesionBtn = document.getElementById('ver-sesion-btn');
	if (verSesionBtn) {
		verSesionBtn.addEventListener('click', async () => {
			try {
				const response = await ejercicioSessionService.getAll();
				console.log('📊 Ejercicios en sesión:', response.data);

				if (response.count === 0) {
					alert('No hay ejercicios en sesión');
				} else {
					const resumen = response.data.map((ej, idx) =>
						`${idx + 1}. ${ej.tipo.toUpperCase()} - ${ej.contenido.title || 'Sin título'} (ID: ${ej.id})`
					).join('\n');
					alert(`Ejercicios en sesión (${response.count}):\n\n${resumen}`);
				}
			} catch (error) {
				alert('❌ Error al obtener ejercicios: ' + error.message);
			}
		});
	}

	// Configurar modal y búsqueda de imágenes
	console.log('🚀 [Main] Inicializando sistema de controladores...');
	appController.init();
	console.log('✓ [Main] Sistema de controladores inicializado');
});