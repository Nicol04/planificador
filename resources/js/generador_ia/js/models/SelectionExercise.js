class SelectionExercise {
  constructor(title = '', description = '') {
    this.tipo = 'SelectionExercise';
    this.title = title;
    this.description = description;
    this.options = [];
    this.sessionId = null;
  }

  setTitle(t) { this.title = t; }
  setDescription(d) { this.description = d; }
  setSessionId(id) { this.sessionId = id; }

  async add(imageSrc, text) {
    if (window.getFirstImage && imageSrc) {
      try {
        const autoImageUrl = await window.getFirstImage(imageSrc);
        this.options.push({ imageSrc: autoImageUrl, text, searchQuery: imageSrc });
        return;
      } catch (error) {
        console.error('[SelectionExercise] Error en asignación automática:', error);
      }
    }
    this.options.push({ imageSrc, text, searchQuery: imageSrc });
  }

  renderInto(container) {
    try {
      console.log('🎨 [SelectionExercise] Renderizando:', this.title);

      const titleInput = document.createElement('textarea');
    titleInput.rows = 2; // Definimos las 2 filas
    titleInput.value = this.title;
    titleInput.className = 'text-3xl font-extrabold text-slate-900 mb-3 border-b-2 border-transparent focus:border-amber-600 focus:outline-none w-full px-3 py-2 transition-colors';
    titleInput.onchange = (e) => {
      console.log('✏️ [SelectionExercise] Título editado:', e.target.value);
      this.title = e.target.value;
      this._syncToSession('title', e.target.value);
    };
    container.appendChild(titleInput);

    if (this.description || this.description === '') {
      const descInput = document.createElement('textarea');
      descInput.value = this.description;
      descInput.className = 'text-base text-slate-600 mb-6 border-b border-transparent focus:border-amber-400 focus:outline-none w-full px-3 py-2 transition-colors resize-y rounded-md';
      descInput.placeholder = 'Agrega una descripción...';
      descInput.rows = 3;
      descInput.oninput = (e) => {
        descInput.style.height = 'auto';
        descInput.style.height = descInput.scrollHeight + 'px';
      };
      descInput.onchange = (e) => {
        console.log('✏️ [SelectionExercise] Descripción editada:', e.target.value);
        this.description = e.target.value;
        this._syncToSession('description', e.target.value);
      };
      setTimeout(() => {
        descInput.style.height = 'auto';
        descInput.style.height = descInput.scrollHeight + 'px';
      }, 0);
      container.appendChild(descInput);
    }

    const grid = document.createElement('div');
    grid.className = 'grid grid-cols-3 gap-6';

    this.options.forEach((opt, idx) => {
      const card = document.createElement('div');
      card.className = 'bg-gradient-to-br from-white to-slate-50 border border-slate-200 rounded-2xl p-5 hover:border-amber-400 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer group';

      const imgWrapper = document.createElement('div');
        imgWrapper.className = 'relative mb-2'; // Reduce bottom margin

      const badge = document.createElement('div');
      badge.className = 'absolute -top-3 -left-3 w-8 h-8 bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-full flex items-center justify-center font-bold text-sm shadow-lg z-10';
      badge.textContent = idx + 1;

      const imgBox = document.createElement('div');
      imgBox.className = 'w-full aspect-square rounded-xl overflow-hidden border border-slate-300 shadow-md group-hover:border-amber-500 transition-all flex items-center justify-center';

      const img = document.createElement('img');
      img.src = opt.imageSrc;
      img.alt = opt.text || '';
      img.className = 'max-w-full max-h-full object-contain mx-auto my-auto group-hover:scale-110 transition-transform duration-300';

      const btn = document.createElement('button');
      btn.textContent = '🔄';
      btn.className = 'no-imprimir absolute top-2 right-2 w-8 h-8 bg-amber-600 hover:bg-amber-700 text-white rounded-full shadow-lg flex items-center justify-center text-sm opacity-0 group-hover:opacity-100 transition-opacity z-10';
      btn.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log(`🖱️ [SelectionExercise] Botón clickeado para opción ${idx + 1}`);
        window.openImageModal?.(opt.searchQuery || opt.imageSrc, (newUrl) => {
          console.log(`✓ [SelectionExercise] Imagen actualizada para opción ${idx + 1}:`, newUrl);
          img.src = newUrl;
          opt.imageSrc = newUrl;
          this._syncToSession(`options.${idx}.imageSrc`, newUrl);
        });
      };

      imgBox.appendChild(img);
      imgWrapper.appendChild(badge);
      imgWrapper.appendChild(imgBox);
      imgWrapper.appendChild(btn);

      const textWrapper = document.createElement('div');
        textWrapper.className = 'relative flex items-center justify-center mt-1'; // Reduce top margin

      const textIcon = document.createElement('div');
      textIcon.className = 'top-1/2 -translate-y-1/2 text-slate-400';

      const textarea = document.createElement('textarea');
      textarea.value = opt.text || '';
      textarea.className = 'w-full min-h-[2.5rem] max-h-32 pr-3 py-2 text-sm font-medium text-center text-slate-800 bg-white border-0 border-slate-200 rounded-lg focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200 transition-all resize-y flex items-center justify-center';
      textarea.placeholder = 'Nombre...';
      textarea.onclick = (e) => e.stopPropagation();
      textarea.onchange = (e) => {
        console.log(`✏️ [SelectionExercise] Texto editado para opción ${idx + 1}:`, e.target.value);
        opt.text = e.target.value;
        this._syncToSession(`options.${idx}.text`, e.target.value);
      };

      textWrapper.appendChild(textIcon);
      textWrapper.appendChild(textarea);

      card.appendChild(imgWrapper);
      card.appendChild(textWrapper);
      grid.appendChild(card);
    });

    container.appendChild(grid);
      console.log(`✓ [SelectionExercise] ${this.options.length} opciones renderizadas`);
    } catch (err) {
      if (window.handleModelError) window.handleModelError('SelectionExercise', err);
      console.error('SelectionExercise.renderInto error', err);
    }
  }

  getJSON() {
    try {
      return {
        title: this.title,
        description: this.description,
        options: this.options.map(({ imageSrc, text }) => ({ imageSrc, text }))
      };
    } catch (err) {
      if (window.handleModelError) window.handleModelError('SelectionExercise', err);
      console.error('SelectionExercise.getJSON error', err);
      return { title: this.title, description: this.description, options: [] };
    }
  }

  static getJSONSchemaString() {
    return '{"title":"Selecciona los objetos correctos","description":"Encierra o marca con una x sobre las imágenes que cumplen la condición indicada.","options":[{"imageSrc":"img/sol.png","text":"Sol"},{"imageSrc":"img/luna.png","text":"Luna"},{"imageSrc":"img/estrella.png","text":"Estrella"}]}'
  }

  _syncToSession(path, value) {
    if (!this.sessionId || !window.ejercicioSessionService) {
      console.log('⚠️ [SelectionExercise] No se puede sincronizar: sin sessionId o servicio');
      return;
    }

    window.ejercicioSessionService.updateContent(this.sessionId, path, value)
      .then(() => console.log(`✓ [SelectionExercise] Sincronizado: ${path}`))
      .catch(err => console.error(`❌ [SelectionExercise] Error sincronizando ${path}:`, err));
  }
}

export default SelectionExercise;
