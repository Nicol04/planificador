/**
 * Controlador del Modal de Imágenes
 * Maneja la apertura, cierre, tabs y preview del modal
 */
export default class ImageModalController {
	constructor() {
		this.currentCallback = null;
		this.selectedImageUrl = null;
		this.modal = null;
		this.previewContainer = null;
		this.btnConfirm = null;
		this.tabs = ['tabUrl', 'tabFile', 'tabClipboard', 'tabSearch'];
		this.panels = {
			tabUrl: 'panelUrl',
			tabFile: 'panelFile',
			tabClipboard: 'panelClipboard',
			tabSearch: 'panelSearch'
		};
	}

	/**
	 * Inicializar el controlador
	 */
	init() {
		this.modal = document.getElementById('imageModal');
		this.previewContainer = document.getElementById('previewContainer');
		this.btnConfirm = document.getElementById('btnConfirm');

		if (!this.modal || !this.previewContainer || !this.btnConfirm) {
			console.error('[ImageModalController] Elementos del modal no encontrados');
			return;
		}

		this.setupTabSystem();
		this.setupConfirmButton();
		this.setupCloseButton();

		console.log('✓ [ImageModalController] Inicializado correctamente');
	}

	/**
	 * Configurar sistema de tabs
	 */
	setupTabSystem() {
		this.tabs.forEach(tabId => {
			const tabBtn = document.getElementById(tabId);
			if (tabBtn) {
				tabBtn.addEventListener('click', () => this.showTab(tabId));
			}
		});
	}

	/**
	 * Mostrar un tab específico
	 */
	showTab(tabId) {
		console.log(`📑 [ImageModalController] Cambiando a tab: ${tabId}`);
		
		this.tabs.forEach(id => {
			const btn = document.getElementById(id);
			const panel = document.getElementById(this.panels[id]);
			
			if (!btn || !panel) return;

			if (id === tabId) {
				btn.className = 'tab-btn px-3 py-2 text-sm rounded-lg bg-blue-600 text-white';
				panel.classList.remove('hidden');
			} else {
				btn.className = 'tab-btn px-3 py-2 text-sm rounded-lg bg-gray-200';
				panel.classList.add('hidden');
			}
		});
	}

	/**
	 * Configurar botón de confirmación
	 */
	setupConfirmButton() {
		this.btnConfirm.addEventListener('click', () => {
			if (this.selectedImageUrl) {
				console.log(`✓ [ImageModalController] Confirmando selección de imagen`);
				if (this.currentCallback) {
					this.currentCallback(this.selectedImageUrl);
				}
				this.close();
			}
		});
	}

	/**
	 * Configurar botón de cerrar
	 */
	setupCloseButton() {
		const closeBtn = document.getElementById('modalClose');
		if (closeBtn) {
			closeBtn.addEventListener('click', () => this.close());
		}
	}

	/**
	 * Abrir el modal
	 * @param {string} query - Query de búsqueda inicial
	 * @param {Function} callback - Callback a ejecutar al confirmar
	 */
	open(query, callback) {
		console.log(`[ImageModalController] Abriendo modal con query: "${query}"`);
		
		this.currentCallback = callback;
		this.clearPreview();

		// Preparar input de búsqueda
		const modalSearchQueryInput = document.getElementById('modalSearchQuery');
		if (modalSearchQueryInput) {
			// En modo edición, dejar vacío; en modo creación, pre-llenar
			const isEditMode = window.location.pathname.match(/\/docente\/ficha-aprendizajes\/[0-9]+\/edit/);
			modalSearchQueryInput.value = isEditMode ? '' : query;
			console.log(`[ImageModalController] Modo: ${isEditMode ? 'edición' : 'creación'}`);
		}

		// Mostrar modal
		this.modal.classList.remove('hidden');
		console.log('[ImageModalController] Modal mostrado');
	}

	/**
	 * Cerrar el modal
	 */
	close() {
		console.log('❌ [ImageModalController] Cerrando modal');
		
		this.modal.classList.add('hidden');
		this.clearPreview();
		
		// Limpiar inputs
		const inputUrl = document.getElementById('inputUrl');
		const inputFile = document.getElementById('inputFile');
		const modalResults = document.getElementById('modalResults');
		
		if (inputUrl) inputUrl.value = '';
		if (inputFile) inputFile.value = '';
		if (modalResults) modalResults.innerHTML = '';
	}

	/**
	 * Actualizar preview con una URL
	 * @param {string} url - URL de la imagen
	 */
	updatePreview(url) {
		console.log(`🖼️ [ImageModalController] Actualizando preview:`, url.substring(0, 50) + '...');
		
		this.selectedImageUrl = url;
		this.previewContainer.innerHTML = `<img src="${url}" alt="Preview">`;
		this.btnConfirm.disabled = false;
	}

	/**
	 * Limpiar preview
	 */
	clearPreview() {
		console.log(`🧹 [ImageModalController] Limpiando preview`);
		
		this.selectedImageUrl = null;
		this.previewContainer.innerHTML = '<p class="text-gray-400 text-sm">No hay imagen seleccionada</p>';
		this.btnConfirm.disabled = true;
	}

	/**
	 * Obtener la URL de imagen seleccionada
	 */
	getSelectedImageUrl() {
		return this.selectedImageUrl;
	}
}
