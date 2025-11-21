import { searchImages } from '../../service/apiClient.js';
import { getCachedImages } from '../../service/SearchImage.js';
import { showLoading, showError, renderResults } from '../ui.js';

/**
 * Controlador de Búsqueda de Imágenes
 * Maneja las búsquedas, caché y resultados
 */
export default class ImageSearchController {
	constructor(modalController) {
		this.modalController = modalController;
		this.modalSearchBtn = null;
		this.modalSearchQuery = null;
		this.modalResults = null;
	}

	/**
	 * Inicializar el controlador
	 */
	init() {
		this.modalSearchBtn = document.getElementById('modalSearchBtn');
		this.modalSearchQuery = document.getElementById('modalSearchQuery');
		this.modalResults = document.getElementById('modalResults');

		if (!this.modalSearchBtn || !this.modalSearchQuery || !this.modalResults) {
			console.error('[ImageSearchController] Elementos de búsqueda no encontrados');
			return;
		}

		this.setupSearchButton();
		this.setupSearchInput();

		console.log('✓ [ImageSearchController] Inicializado correctamente');
	}

	/**
	 * Configurar botón de búsqueda
	 */
	setupSearchButton() {
		this.modalSearchBtn.addEventListener('click', () => this.performSearch());
	}

	/**
	 * Configurar input de búsqueda (Enter para buscar)
	 */
	setupSearchInput() {
		this.modalSearchQuery.addEventListener('keypress', (e) => {
			if (e.key === 'Enter') {
				this.modalSearchBtn.click();
			}
		});
	}

	/**
	 * Realizar búsqueda de imágenes
	 */
	async performSearch() {
		const query = this.modalSearchQuery.value.trim();
		
		if (!query) {
			console.warn('[ImageSearchController] Query vacío');
			return;
		}

		console.log(`🔍 [ImageSearchController] Buscando imágenes para: "${query}"`);

		// Verificar si hay resultados cacheados
		const cachedItems = getCachedImages(query);
		if (cachedItems) {
			console.log(`✓ [ImageSearchController] Usando ${cachedItems.length} imágenes cacheadas`);
			this.renderSearchResults(cachedItems);
			return;
		}

		// Si no hay caché, hacer búsqueda normal
		showLoading(this.modalResults);
		
		try {
			const items = await searchImages(query);
			console.log(`✓ [ImageSearchController] ${items.length} imágenes encontradas`);
			this.renderSearchResults(items);
		} catch (error) {
			console.error('❌ [ImageSearchController] Error en búsqueda:', error);
			showError(this.modalResults, 'Error al buscar imágenes');
		}
	}

	/**
	 * Renderizar resultados de búsqueda
	 * @param {Array} items - Array de resultados
	 */
	renderSearchResults(items) {
		renderResults(this.modalResults, items);

		// Configurar callback para cada imagen
		this.modalResults.querySelectorAll('img').forEach((img, idx) => {
			img.onclick = () => {
				console.log(`✓ [ImageSearchController] Imagen ${idx + 1} seleccionada`);
				this.modalController.updatePreview(img.src);
			};
		});
	}

	/**
	 * Buscar y mostrar automáticamente resultados cacheados
	 * @param {string} query - Query de búsqueda
	 * @returns {boolean} - True si se encontraron resultados en caché
	 */
	showCachedResults(query) {
		const cachedItems = getCachedImages(query);
		
		if (cachedItems) {
			console.log(`[ImageSearchController] Mostrando ${cachedItems.length} imágenes cacheadas para "${query}"`);
			
			// Cambiar a tab de búsqueda
			this.modalController.showTab('tabSearch');
			
			// Renderizar resultados
			this.renderSearchResults(cachedItems);
			
			return true;
		}
		
		return false;
	}
}
