import GeminiService from '../service/GeminiService.js';
import { searchImages } from '../service/apiClient.js';
import { showLoading, showError, renderResults, openModal } from './ui.js';
import { getFirstImage, getCachedImages } from '../service/SearchImage.js';
import PdfExportService from '../service/PdfExportService.js';
import EjercicioSessionService from './services/EjercicioSessionService.js';

import ClassificationExercise from './models/ClassificationExercise.js';
import ClozeExercise from './models/ClozeExercise.js';
import SelectionExercise from './models/SelectionExercise.js';
import ReflectionExercise from './models/ReflectionExercise.js';
import ProgressIndicator from './ProgressIndicator.js';

//const GEMINI_API_KEY = 'AIzaSyBvv7CkK1CYFzZJw6gLeJnjPF6HNkawpw8';

const GEMINI_API_KEY = window.userGeminiKey ?? null;
if (!GEMINI_API_KEY) {
	console.warn('⚠️ No se encontró gemini_api_key del usuario. Configure una clave o use un proxy server-side.');
}

const gemini = new GeminiService(GEMINI_API_KEY);
const pdfExporter = new PdfExportService();
const ejercicioSessionService = new EjercicioSessionService();

// Hacer disponible globalmente para los modelos
window.ejercicioSessionService = ejercicioSessionService;

/**
 * Guardar un ejercicio en sesión de Laravel después de generarlo
 * @param {Object} ejercicioInstancia - Instancia del ejercicio (SelectionExercise, etc.)
 * @param {boolean} esPrimero - Si es el primer ejercicio (para incluir descripción)
 */
async function guardarEjercicioEnSesion(ejercicioInstancia, esPrimero = false) {
	try {
		const tipo = ejercicioInstancia.tipo; // Obtener tipo del modelo
		const contenido = ejercicioInstancia.getJSON();

		const payload = {
			tipo,
			contenido
		};

		// Si es el primer ejercicio, incluir la descripción de la ficha
		if (esPrimero) {
			const descripcionFicha = document.getElementById('Contenido')?.value || '';
			if (descripcionFicha) {
				payload.descripcion_ficha = descripcionFicha;
				console.log(`📝 [Main] Guardando descripción de ficha: "${descripcionFicha.substring(0, 50)}..."`);
			}
		}

		const response = await ejercicioSessionService.store(payload.tipo, payload.contenido, payload.descripcion_ficha);

		// Asignar el ID de sesión al ejercicio para sincronización futura
		ejercicioInstancia.setSessionId(response.data.id);

		console.log(`💾 Ejercicio ${tipo} guardado en sesión con ID: ${response.data.id}`);
		return response.data;
	} catch (error) {
		console.error(`❌ Error guardando ejercicio ${tipo} en sesión:`, error);
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
		console.log(`✓ [Main] ${data.data.count} ejercicios cargados para "${data.data.ficha_nombre}"`);

		// Limpiar contenedor
		fichaContenido.innerHTML = '';

		if (data.data.count === 0) {
			fichaContenido.innerHTML = '<div class="text-center text-slate-400 py-8">No hay ejercicios asociados a esta ficha</div>';
			return;
		}

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
	const { tipo, contenido } = ejercicioData;
	console.log(`🎨 [Main] Renderizando ejercicio tipo: ${tipo}`);

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
	const gradoPrimaria = document.getElementById('GradoPrimaria').value;
	const contenido = document.getElementById('Contenido').value;
	const autoAsignarImagenes = document.getElementById('AutoAsignarImagenes')?.checked || false;
	const temperature = parseFloat(document.getElementById('Temperature').value) || 1.0;
	const topP = parseFloat(document.getElementById('TopP').value) || 1.0;
	const topK = parseInt(document.getElementById('topK').value) || 40;

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

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
	// Verificar si estamos en modo edición y cargar ejercicios existentes
	cargarEjerciciosSiEsEdicion();

	const btn = document.getElementById('generar-btn');
	if (btn) {
		btn.addEventListener('click', generarFicha);
	}

	// Botón de exportar PDF
	const exportarPdfBtn = document.getElementById('exportar-pdf-btn');
	if (exportarPdfBtn) {
		exportarPdfBtn.addEventListener('click', () => {
			console.log('🖨️ [Main] Botón exportar PDF clickeado');
			pdfExporter.exportToPdf();
		});
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
	console.log('🚀 [Main] Inicializando sistema de carga de imágenes');

	let currentCallback = null;
	let selectedImageUrl = null;
	const previewContainer = document.getElementById('previewContainer');
	const btnConfirm = document.getElementById('btnConfirm');

	function updatePreview(url) {
		console.log(`🖼️ [Main] Actualizando vista previa:`, url.substring(0, 50) + '...');
		selectedImageUrl = url;
		previewContainer.innerHTML = `<img src="${url}" alt="Preview">`;
		btnConfirm.disabled = false;
	}

	function clearPreview() {
		console.log(`🧹 [Main] Limpiando vista previa`);
		selectedImageUrl = null;
		previewContainer.innerHTML = '<p class="text-gray-400 text-sm">No hay imagen seleccionada</p>';
		btnConfirm.disabled = true;
	}

	window.openImageModal = (query, callback) => {
		console.log(`📂 [Main] Abriendo modal con query: "${query}"`);
		currentCallback = callback;
		// Si estamos en modo edición, el input debe estar vacío
		if (window.location.pathname.match(/\/docente\/ficha-aprendizajes\/[0-9]+\/edit/)) {
			document.getElementById('modalSearchQuery').value = '';
		} else {
			document.getElementById('modalSearchQuery').value = query;
		}
		document.getElementById('imageModal').classList.remove('hidden');
		clearPreview();

		// Si hay caché para este query, mostrarlo automáticamente en el tab de búsqueda
		const cachedItems = getCachedImages(query);
		if (cachedItems) {
			console.log(`💾 [Main] Mostrando ${cachedItems.length} imágenes cacheadas automáticamente`);
			showTab('tabSearch');
			renderResults(modalResults, cachedItems);

			// Configurar callback para cada imagen cacheada
			modalResults.querySelectorAll('img').forEach((img, idx) => {
				img.onclick = () => {
					console.log(`✓ [Main] Imagen ${idx + 1} seleccionada de caché`);
					updatePreview(img.src);
				};
			});
		} else {
			showTab('tabUrl');
		}
	};

	// Sistema de tabs
	const tabs = ['tabUrl', 'tabFile', 'tabClipboard', 'tabSearch'];
	const panels = {
		tabUrl: 'panelUrl',
		tabFile: 'panelFile',
		tabClipboard: 'panelClipboard',
		tabSearch: 'panelSearch'
	};

	function showTab(tabId) {
		console.log(`📑 [Main] Cambiando a tab: ${tabId}`);
		tabs.forEach(id => {
			const btn = document.getElementById(id);
			const panel = document.getElementById(panels[id]);
			if (id === tabId) {
				btn.className = 'tab-btn px-3 py-2 text-sm rounded-lg bg-blue-600 text-white';
				panel.classList.remove('hidden');
			} else {
				btn.className = 'tab-btn px-3 py-2 text-sm rounded-lg bg-gray-200';
				panel.classList.add('hidden');
			}
		});
	}

	tabs.forEach(id => {
		document.getElementById(id)?.addEventListener('click', () => showTab(id));
	});

	// Confirmar selección
	btnConfirm?.addEventListener('click', () => {
		if (selectedImageUrl) {
			console.log(`✓ [Main] Confirmando selección de imagen`);
			currentCallback?.(selectedImageUrl);
			closeModal();
		}
	});

	// URL
	document.getElementById('btnUrl')?.addEventListener('click', () => {
		const url = document.getElementById('inputUrl').value.trim();
		console.log(`🔗 [Main] URL ingresada:`, url);
		if (url) {
			updatePreview(url);
		}
	});

	document.getElementById('inputUrl')?.addEventListener('keypress', (e) => {
		if (e.key === 'Enter') {
			document.getElementById('btnUrl').click();
		}
	});

	// Archivo
	document.getElementById('inputFile')?.addEventListener('change', () => {
		document.getElementById('btnFile')?.click();
	});

	document.getElementById('btnFile')?.addEventListener('click', () => {
		const input = document.getElementById('inputFile');
		const file = input.files?.[0];
		console.log(`📁 [Main] Archivo seleccionado:`, file?.name);
		if (file) {
			const reader = new FileReader();
			reader.onload = (e) => {
				console.log(`✓ [Main] Archivo cargado como Base64`);
				updatePreview(e.target.result);
			};
			reader.readAsDataURL(file);
		}
	});

	// Portapapeles
	const clipboardDropzone = document.getElementById('clipboardDropzone');

	clipboardDropzone?.addEventListener('click', () => {
		console.log(`📋 [Main] Dropzone clickeado, esperando paste...`);
		clipboardDropzone.focus();
	});

	document.addEventListener('paste', (e) => {
		const modal = document.getElementById('imageModal');
		if (!modal.classList.contains('hidden')) {
			const items = e.clipboardData?.items;
			console.log(`📋 [Main] Evento paste detectado, items:`, items?.length);
			for (let item of items || []) {
				if (item.type.indexOf('image') !== -1) {
					const file = item.getAsFile();
					console.log(`✓ [Main] Imagen detectada en portapapeles`);
					const reader = new FileReader();
					reader.onload = (ev) => {
						updatePreview(ev.target.result);
					};
					reader.readAsDataURL(file);
					break;
				}
			}
		}
	});

	// Búsqueda
	const modalSearchBtn = document.getElementById('modalSearchBtn');
	const modalResults = document.getElementById('modalResults');

	document.getElementById('modalSearchQuery')?.addEventListener('keypress', (e) => {
		if (e.key === 'Enter') {
			modalSearchBtn?.click();
		}
	});

	modalSearchBtn?.addEventListener('click', async () => {
		const query = document.getElementById('modalSearchQuery').value;
		console.log(`🔍 [Main] Buscando imágenes para: "${query}"`);

		// Verificar si hay resultados cacheados
		const cachedItems = getCachedImages(query);
		if (cachedItems) {
			console.log(`✓ [Main] Usando ${cachedItems.length} imágenes cacheadas`);
			renderResults(modalResults, cachedItems);

			// Configurar callback para cada imagen
			modalResults.querySelectorAll('img').forEach((img, idx) => {
				img.onclick = () => {
					console.log(`✓ [Main] Imagen ${idx + 1} seleccionada de caché`);
					updatePreview(img.src);
				};
			});
			return;
		}

		// Si no hay caché, hacer búsqueda normal
		showLoading(modalResults);
		try {
			const items = await searchImages(query);
			console.log(`✓ [Main] ${items.length} imágenes encontradas`);
			renderResults(modalResults, items);

			// Configurar callback para cada imagen
			modalResults.querySelectorAll('img').forEach((img, idx) => {
				img.onclick = () => {
					console.log(`✓ [Main] Imagen ${idx + 1} seleccionada de búsqueda`);
					updatePreview(img.src);
				};
			});
		} catch (error) {
			console.error('❌ [Main] Error en búsqueda:', error);
			showError(modalResults, 'Error al buscar imágenes');
		}
	});

	function closeModal() {
		console.log('❌ [Main] Cerrando modal');
		document.getElementById('imageModal').classList.add('hidden');
		clearPreview();
		document.getElementById('inputUrl').value = '';
		document.getElementById('inputFile').value = '';
		modalResults.innerHTML = '';
	}

	document.getElementById('modalClose')?.addEventListener('click', closeModal);

	// Botón de vista previa eliminado: la lógica de preview/impresión ha sido removida.

	// ========== CONFIGURACIÓN AVANZADA ==========
	const toggleAdvancedBtn = document.getElementById('toggleAdvanced');
	const advancedConfig = document.getElementById('advancedConfig');
	const advancedToggleText = document.getElementById('advancedToggleText');

	// Toggle del panel de configuración avanzada
	toggleAdvancedBtn?.addEventListener('click', () => {
		const isHidden = advancedConfig.classList.contains('hidden');

		if (isHidden) {
			advancedConfig.classList.remove('hidden');
			advancedToggleText.textContent = 'Ocultar configuración avanzada';
			console.log('⚙️ [Main] Panel de configuración avanzada abierto');
		} else {
			advancedConfig.classList.add('hidden');
			advancedToggleText.textContent = 'Mostrar configuración avanzada';
			console.log('⚙️ [Main] Panel de configuración avanzada cerrado');
		}
	});

	// Actualizar valores mostrados en los sliders
	const temperatureSlider = document.getElementById('Temperature');
	const temperatureValue = document.getElementById('temperatureValue');

	temperatureSlider?.addEventListener('input', (e) => {
		temperatureValue.textContent = parseFloat(e.target.value).toFixed(1);
	});

	const topPSlider = document.getElementById('TopP');
	const topPValue = document.getElementById('topPValue');

	topPSlider?.addEventListener('input', (e) => {
		topPValue.textContent = parseFloat(e.target.value).toFixed(2);
	});

	const topKSlider = document.getElementById('topK');
	const topKValue = document.getElementById('topKValue');

	topKSlider?.addEventListener('input', (e) => {
		topKValue.textContent = e.target.value;
	});

	console.log('⚙️ [Main] Sistema de configuración avanzada inicializado');
});