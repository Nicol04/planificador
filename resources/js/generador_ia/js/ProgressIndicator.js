/**
 * ProgressIndicator - Sistema de indicadores visuales para el progreso de generación
 * Muestra en tiempo real el estado de cada ejercicio mientras se genera
 */
class ProgressIndicator {
  constructor(containerId = 'ficha-contenido') {
    this.container = document.getElementById(containerId);
    this.indicatorWrapper = null;
    this.currentSteps = [];
    this.colors = {
      'ClozeExercise': { from: 'emerald-500', to: 'emerald-600', ring: 'emerald-200' },
      'ClassificationExercise': { from: 'blue-500', to: 'blue-600', ring: 'blue-200' },
      'SelectionExercise': { from: 'amber-500', to: 'amber-600', ring: 'amber-200' },
      'ReflectionExercise': { from: 'violet-500', to: 'violet-600', ring: 'violet-200' }
    };
  }

  /**
   * Inicializa el contenedor de progreso
   */
  init() {
    if (!this.container) return;

    // Limpiar contenido anterior
    this.container.innerHTML = '';

    // Crear wrapper principal
    this.indicatorWrapper = document.createElement('div');
    this.indicatorWrapper.id = 'progress-indicator-wrapper';
    this.indicatorWrapper.className = 'space-y-4 p-6';
    this.container.appendChild(this.indicatorWrapper);
  }

  /**
   * Crea un nuevo indicador de ejercicio
   * @param {string} exerciseType - Tipo de ejercicio
   * @param {number} index - Índice del ejercicio
   * @param {string} title - Título del ejercicio
   */
  createExerciseIndicator(exerciseType, index, title = '') {
    if (!this.indicatorWrapper) this.init();

    const color = this.colors[exerciseType] || { from: 'slate-500', to: 'slate-600', ring: 'slate-200' };
    
    const card = document.createElement('div');
    card.id = `exercise-indicator-${index}`;
    card.className = 'bg-white border-2 border-slate-200 rounded-xl p-5 shadow-lg transition-all duration-300';

    // Header con número y tipo
    const header = document.createElement('div');
    header.className = 'flex items-center gap-4 mb-4';

    const badge = document.createElement('div');
    badge.className = `flex-shrink-0 w-12 h-12 bg-gradient-to-br from-${color.from} to-${color.to} text-white rounded-full flex items-center justify-center font-bold text-lg shadow-lg`;
    badge.textContent = index + 1;

    const titleSection = document.createElement('div');
    titleSection.className = 'flex-1';

    const exerciseTitle = document.createElement('h3');
    exerciseTitle.className = 'text-lg font-bold text-slate-800';
    exerciseTitle.textContent = title || this._getExerciseTypeName(exerciseType);

    const exerciseSubtitle = document.createElement('p');
    exerciseSubtitle.className = 'text-sm text-slate-500';
    exerciseSubtitle.textContent = 'Generando contenido...';

    titleSection.appendChild(exerciseTitle);
    titleSection.appendChild(exerciseSubtitle);

    header.appendChild(badge);
    header.appendChild(titleSection);

    // Spinner de carga
    const spinnerWrapper = document.createElement('div');
    spinnerWrapper.className = 'flex items-center justify-center mb-4';

    const spinner = document.createElement('div');
    spinner.className = 'inline-block';
    spinner.innerHTML = `
      <svg class="animate-spin h-10 w-10 text-${color.from}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    `;

    spinnerWrapper.appendChild(spinner);

    // Progress bar
    const progressBarWrapper = document.createElement('div');
    progressBarWrapper.className = 'w-full bg-slate-200 rounded-full h-3 mb-4 overflow-hidden';

    const progressBar = document.createElement('div');
    progressBar.id = `progress-bar-${index}`;
    progressBar.className = `h-full bg-gradient-to-r from-${color.from} to-${color.to} rounded-full transition-all duration-500 ease-out`;
    progressBar.style.width = '0%';

    progressBarWrapper.appendChild(progressBar);

    // Steps list
    const stepsList = document.createElement('div');
    stepsList.id = `steps-list-${index}`;
    stepsList.className = 'space-y-2';

    card.appendChild(header);
    card.appendChild(spinnerWrapper);
    card.appendChild(progressBarWrapper);
    card.appendChild(stepsList);

    this.indicatorWrapper.appendChild(card);

    return card;
  }

  /**
   * Añade un paso al indicador
   * @param {number} index - Índice del ejercicio
   * @param {string} stepText - Texto del paso
   * @param {string} status - Estado: 'loading', 'success', 'error'
   */
  addStep(index, stepText, status = 'loading') {
    const stepsList = document.getElementById(`steps-list-${index}`);
    if (!stepsList) return;

    // Limpiar lista de pasos
    stepsList.innerHTML = '';

    // Guardar el paso actual en el array de pasos
    if (!this.currentSteps[index]) {
      this.currentSteps[index] = [];
    }
    this.currentSteps[index].push({ text: stepText, status });

    // Re-renderizar todos los pasos
    this.currentSteps[index].forEach((stepData, stepIndex) => {
      const step = document.createElement('div');
      step.className = 'flex items-center gap-3 text-sm transition-all duration-300';

      const icon = document.createElement('div');
      icon.className = 'flex-shrink-0';

      if (stepData.status === 'loading') {
        icon.innerHTML = `
          <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        `;
      } else if (stepData.status === 'success') {
        icon.innerHTML = `
          <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        `;
      } else if (stepData.status === 'error') {
        icon.innerHTML = `
          <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        `;
      }

      const text = document.createElement('span');
      text.className = stepData.status === 'success' ? 'text-slate-600' : 'text-slate-700 font-medium';
      text.textContent = stepData.text;

      step.appendChild(icon);
      step.appendChild(text);
      stepsList.appendChild(step);
    });

    // Scroll suave hacia el último paso
    const lastStep = stepsList.lastElementChild;
    if (lastStep) {
      lastStep.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  /**
   * Actualiza el progreso de la barra
   * @param {number} index - Índice del ejercicio
   * @param {number} percentage - Porcentaje (0-100)
   */
  updateProgress(index, percentage) {
    const progressBar = document.getElementById(`progress-bar-${index}`);
    if (progressBar) {
      progressBar.style.width = `${percentage}%`;
    }
  }

  /**
   * Actualiza el estado del último paso
   * @param {number} index - Índice del ejercicio
   * @param {string} status - Nuevo estado: 'success', 'error'
   */
  updateLastStepStatus(index, status) {
    if (!this.currentSteps[index] || this.currentSteps[index].length === 0) return;
    
    // Actualizar el estado del último paso
    const lastStepIndex = this.currentSteps[index].length - 1;
    this.currentSteps[index][lastStepIndex].status = status;
    
    // Re-renderizar
    const stepsList = document.getElementById(`steps-list-${index}`);
    if (!stepsList) return;

    stepsList.innerHTML = '';

    this.currentSteps[index].forEach((stepData) => {
      const step = document.createElement('div');
      step.className = 'flex items-center gap-3 text-sm transition-all duration-300';

      const icon = document.createElement('div');
      icon.className = 'flex-shrink-0';

      if (stepData.status === 'loading') {
        icon.innerHTML = `
          <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        `;
      } else if (stepData.status === 'success') {
        icon.innerHTML = `
          <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        `;
      } else if (stepData.status === 'error') {
        icon.innerHTML = `
          <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        `;
      }

      const text = document.createElement('span');
      text.className = stepData.status === 'success' ? 'text-slate-600' : 'text-slate-700 font-medium';
      text.textContent = stepData.text;

      step.appendChild(icon);
      step.appendChild(text);
      stepsList.appendChild(step);
    });
  }

  /**
   * Marca un ejercicio como completado
   * @param {number} index - Índice del ejercicio
   */
  completeExercise(index) {
    const card = document.getElementById(`exercise-indicator-${index}`);
    if (!card) return;

    // Actualizar progreso al 100%
    this.updateProgress(index, 100);

    // Añadir animación de éxito
    card.classList.add('border-green-500', 'bg-green-50');

    // Ocultar spinner
    const spinner = card.querySelector('.animate-spin');
    if (spinner) {
      spinner.parentElement.classList.add('hidden');
    }

    // Actualizar subtítulo
    const subtitle = card.querySelector('p');
    if (subtitle) {
      subtitle.textContent = '✓ Completado exitosamente';
      subtitle.classList.remove('text-slate-500');
      subtitle.classList.add('text-green-600', 'font-semibold');
    }

    // Después de 2 segundos, hacer fade out
    setTimeout(() => {
      card.style.opacity = '0';
      card.style.transform = 'scale(0.95)';
      setTimeout(() => {
        card.remove();
      }, 300);
    }, 1500);
  }

  /**
   * Marca un ejercicio como error
   * @param {number} index - Índice del ejercicio
   * @param {string} errorMessage - Mensaje de error
   */
  errorExercise(index, errorMessage = 'Error al generar') {
    const card = document.getElementById(`exercise-indicator-${index}`);
    if (!card) return;

    card.classList.add('border-red-500', 'bg-red-50');

    const spinner = card.querySelector('.animate-spin');
    if (spinner) {
      spinner.parentElement.classList.add('hidden');
    }

    const subtitle = card.querySelector('p');
    if (subtitle) {
      subtitle.textContent = `✗ ${errorMessage}`;
      subtitle.classList.remove('text-slate-500');
      subtitle.classList.add('text-red-600', 'font-semibold');
    }
  }

  /**
   * Limpia todos los indicadores
   */
  clear() {
    if (this.indicatorWrapper) {
      this.indicatorWrapper.remove();
      this.indicatorWrapper = null;
    }
  }

  /**
   * Obtiene el nombre legible del tipo de ejercicio
   */
  _getExerciseTypeName(type) {
    const names = {
      'ClozeExercise': 'Ejercicio de Completar',
      'ClassificationExercise': 'Ejercicio de Unir',
      'SelectionExercise': 'Ejercicio de Seleccionar',
      'ReflectionExercise': 'Ejercicio de Reflexión'
    };
    return names[type] || 'Ejercicio';
  }
}

// Exportar para uso global
export default ProgressIndicator;
