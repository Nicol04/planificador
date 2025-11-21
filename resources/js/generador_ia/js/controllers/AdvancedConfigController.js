/**
 * Controlador de Configuración Avanzada
 * Maneja los sliders y controles avanzados de la generación de IA
 */
export default class AdvancedConfigController {
	constructor() {
		this.toggleBtn = null;
		this.configPanel = null;
		this.toggleText = null;
		this.temperatureSlider = null;
		this.temperatureValue = null;
		this.topPSlider = null;
		this.topPValue = null;
		this.topKSlider = null;
		this.topKValue = null;
	}

	/**
	 * Inicializar el controlador
	 */
	init() {
		this.toggleBtn = document.getElementById('toggleAdvanced');
		this.configPanel = document.getElementById('advancedConfig');
		this.toggleText = document.getElementById('advancedToggleText');

		if (this.toggleBtn && this.configPanel && this.toggleText) {
			this.setupToggle();
		}

		this.temperatureSlider = document.getElementById('Temperature');
		this.temperatureValue = document.getElementById('temperatureValue');

		this.topPSlider = document.getElementById('TopP');
		this.topPValue = document.getElementById('topPValue');

		this.topKSlider = document.getElementById('topK');
		this.topKValue = document.getElementById('topKValue');

		this.setupSliders();

		console.log('✓ [AdvancedConfigController] Inicializado correctamente');
	}

	/**
	 * Configurar toggle del panel de configuración
	 */
	setupToggle() {
		this.toggleBtn.addEventListener('click', () => {
			const isHidden = this.configPanel.classList.contains('hidden');

			if (isHidden) {
				this.configPanel.classList.remove('hidden');
				this.toggleText.textContent = 'Ocultar configuración avanzada';
				console.log('⚙️ [AdvancedConfigController] Panel abierto');
			} else {
				this.configPanel.classList.add('hidden');
				this.toggleText.textContent = 'Mostrar configuración avanzada';
				console.log('⚙️ [AdvancedConfigController] Panel cerrado');
			}
		});
	}

	/**
	 * Configurar sliders de configuración
	 */
	setupSliders() {
		// Temperature slider
		if (this.temperatureSlider && this.temperatureValue) {
			this.temperatureSlider.addEventListener('input', (e) => {
				const value = parseFloat(e.target.value).toFixed(1);
				this.temperatureValue.textContent = value;
				console.log(`⚙️ [AdvancedConfigController] Temperature: ${value}`);
			});
		}

		// Top P slider
		if (this.topPSlider && this.topPValue) {
			this.topPSlider.addEventListener('input', (e) => {
				const value = parseFloat(e.target.value).toFixed(2);
				this.topPValue.textContent = value;
				console.log(`⚙️ [AdvancedConfigController] Top P: ${value}`);
			});
		}

		// Top K slider
		if (this.topKSlider && this.topKValue) {
			this.topKSlider.addEventListener('input', (e) => {
				const value = e.target.value;
				this.topKValue.textContent = value;
				console.log(`⚙️ [AdvancedConfigController] Top K: ${value}`);
			});
		}
	}

	/**
	 * Obtener la configuración actual
	 * @returns {Object} - Objeto con temperature, topP y topK
	 */
	getConfig() {
		return {
			temperature: parseFloat(this.temperatureSlider?.value) || 1.0,
			topP: parseFloat(this.topPSlider?.value) || 1.0,
			topK: parseInt(this.topKSlider?.value) || 40
		};
	}

	/**
	 * Establecer configuración
	 * @param {Object} config - Objeto con temperature, topP y topK
	 */
	setConfig(config) {
		if (config.temperature !== undefined && this.temperatureSlider) {
			this.temperatureSlider.value = config.temperature;
			this.temperatureValue.textContent = config.temperature.toFixed(1);
		}

		if (config.topP !== undefined && this.topPSlider) {
			this.topPSlider.value = config.topP;
			this.topPValue.textContent = config.topP.toFixed(2);
		}

		if (config.topK !== undefined && this.topKSlider) {
			this.topKSlider.value = config.topK;
			this.topKValue.textContent = config.topK;
		}

		console.log('⚙️ [AdvancedConfigController] Configuración actualizada:', config);
	}
}
