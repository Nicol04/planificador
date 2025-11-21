import ImageModalController from './ImageModalController.js';
import ImageSearchController from './ImageSearchController.js';
import ImageSourceController from './ImageSourceController.js';
import AdvancedConfigController from './AdvancedConfigController.js';

/**
 * Controlador Principal de la Aplicación
 * Coordina todos los controladores y maneja la inicialización
 */
export default class AppController {
	constructor() {
		this.imageModalController = new ImageModalController();
		this.imageSearchController = new ImageSearchController(this.imageModalController);
		this.imageSourceController = new ImageSourceController(this.imageModalController);
		this.advancedConfigController = new AdvancedConfigController();
	}

	/**
	 * Inicializar todos los controladores
	 */
	init() {
		console.log('🚀 [AppController] Inicializando sistema de controladores...');

		// Inicializar controladores del modal
		this.imageModalController.init();
		this.imageSearchController.init();
		this.imageSourceController.init();
		this.advancedConfigController.init();

		// Exponer función global para abrir el modal
		this.setupGlobalModalFunction();

		console.log('✓ [AppController] Sistema de controladores inicializado');
	}

	/**
	 * Configurar función global para abrir el modal desde cualquier parte
	 */
	setupGlobalModalFunction() {
		window.openImageModal = (query, callback) => {
			console.log(`[AppController] openImageModal llamado con query: "${query}"`);
			
			// Abrir modal
			this.imageModalController.open(query, callback);
			
			// Intentar mostrar resultados cacheados automáticamente
			const hasCachedResults = this.imageSearchController.showCachedResults(query);
			
			// Si no hay caché, mostrar tab de URL por defecto
			if (!hasCachedResults) {
				this.imageModalController.showTab('tabUrl');
			}
		};

		console.log('✓ [AppController] window.openImageModal configurado');
	}

	/**
	 * Obtener el controlador de configuración avanzada
	 */
	getAdvancedConfigController() {
		return this.advancedConfigController;
	}

	/**
	 * Obtener el controlador del modal de imágenes
	 */
	getImageModalController() {
		return this.imageModalController;
	}

	/**
	 * Obtener el controlador de búsqueda de imágenes
	 */
	getImageSearchController() {
		return this.imageSearchController;
	}

	/**
	 * Obtener el controlador de fuentes de imágenes
	 */
	getImageSourceController() {
		return this.imageSourceController;
	}
}
