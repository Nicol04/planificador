/**
 * Controlador de Fuentes de Imágenes
 * Maneja las diferentes formas de cargar imágenes: URL, archivo y portapapeles
 */
export default class ImageSourceController {
	constructor(modalController) {
		this.modalController = modalController;
		this.inputUrl = null;
		this.btnUrl = null;
		this.inputFile = null;
		this.btnFile = null;
		this.clipboardDropzone = null;
	}

	/**
	 * Inicializar el controlador
	 */
	init() {
		this.inputUrl = document.getElementById('inputUrl');
		this.btnUrl = document.getElementById('btnUrl');
		this.inputFile = document.getElementById('inputFile');
		this.btnFile = document.getElementById('btnFile');
		this.clipboardDropzone = document.getElementById('clipboardDropzone');

		if (this.inputUrl && this.btnUrl) {
			this.setupUrlSource();
		}

		if (this.inputFile && this.btnFile) {
			this.setupFileSource();
		}

		if (this.clipboardDropzone) {
			this.setupClipboardSource();
		}

		console.log('✓ [ImageSourceController] Inicializado correctamente');
	}

	/**
	 * Configurar fuente de URL
	 */
	setupUrlSource() {
		// Botón para cargar URL
		this.btnUrl.addEventListener('click', () => {
			const url = this.inputUrl.value.trim();
			console.log(`🔗 [ImageSourceController] URL ingresada:`, url);
			
			if (url) {
				this.modalController.updatePreview(url);
			}
		});

		// Enter para confirmar URL
		this.inputUrl.addEventListener('keypress', (e) => {
			if (e.key === 'Enter') {
				this.btnUrl.click();
			}
		});
	}

	/**
	 * Configurar fuente de archivo
	 */
	setupFileSource() {
		// Trigger de clic en botón cuando se selecciona archivo
		this.inputFile.addEventListener('change', () => {
			this.btnFile.click();
		});

		// Procesar archivo seleccionado
		this.btnFile.addEventListener('click', () => {
			const file = this.inputFile.files?.[0];
			
			if (!file) {
				console.warn('[ImageSourceController] No se seleccionó ningún archivo');
				return;
			}

			console.log(`📁 [ImageSourceController] Archivo seleccionado:`, file.name);

			const reader = new FileReader();
			
			reader.onload = (e) => {
				console.log(`✓ [ImageSourceController] Archivo cargado como Base64`);
				this.modalController.updatePreview(e.target.result);
			};

			reader.onerror = (error) => {
				console.error('❌ [ImageSourceController] Error al leer archivo:', error);
			};

			reader.readAsDataURL(file);
		});
	}

	/**
	 * Configurar fuente de portapapeles
	 */
	setupClipboardSource() {
		// Focus al hacer click en el dropzone
		this.clipboardDropzone.addEventListener('click', () => {
			console.log(`📋 [ImageSourceController] Dropzone clickeado, esperando paste...`);
			this.clipboardDropzone.focus();
		});

		// Detectar paste en el documento (solo cuando el modal está abierto)
		document.addEventListener('paste', (e) => {
			const modal = document.getElementById('imageModal');
			
			// Solo procesar si el modal está visible
			if (modal && modal.classList.contains('hidden')) {
				return;
			}

			const items = e.clipboardData?.items;
			console.log(`📋 [ImageSourceController] Evento paste detectado, items:`, items?.length);

			// Buscar imagen en el portapapeles
			for (let item of items || []) {
				if (item.type.indexOf('image') !== -1) {
					const file = item.getAsFile();
					console.log(`✓ [ImageSourceController] Imagen detectada en portapapeles`);

					const reader = new FileReader();
					
					reader.onload = (ev) => {
						this.modalController.updatePreview(ev.target.result);
					};

					reader.onerror = (error) => {
						console.error('❌ [ImageSourceController] Error al leer portapapeles:', error);
					};

					reader.readAsDataURL(file);
					break;
				}
			}
		});
	}
}
