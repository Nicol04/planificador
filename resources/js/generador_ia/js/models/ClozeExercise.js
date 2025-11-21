class ClozeExercise {
  constructor(title = '', description = '') {
    this.tipo = 'ClozeExercise';
    this.title = title;
    this.description = description;
    this.items = [];
    this.sessionId = null;
  }

  setTitle(t) { this.title = t; }
  setDescription(d) { this.description = d; }
  setSessionId(id) { this.sessionId = id; }

  async add(imageSrc, placeholder = '_________') {
    if (window.getFirstImage && imageSrc) {
      try {
        const autoImageUrl = await window.getFirstImage(imageSrc);
        this.items.push({ imageSrc: autoImageUrl, placeholder, searchQuery: imageSrc });
        return;
      } catch (error) {
        console.error('[ClozeExercise] Error en asignación automática:', error);
      }
    }
    this.items.push({ imageSrc, placeholder, searchQuery: imageSrc });
  }

  renderInto(container) {
    try {
      console.log('🎨 [ClozeExercise] Renderizando:', this.title);

      const titleInput = document.createElement('textarea');
    titleInput.rows = 2; // Definimos las 2 filas
    titleInput.value = this.title;
    titleInput.className = 'text-3xl font-extrabold text-slate-900 mb-3 border-b-2 border-transparent focus:border-emerald-600 focus:outline-none w-full px-3 py-2 transition-colors';
    titleInput.onchange = (e) => {
      console.log('✏️ [ClozeExercise] Título editado:', e.target.value);
      this.title = e.target.value;
      this._syncToSession('title', e.target.value);
    };
    container.appendChild(titleInput);

    if (this.description || this.description === '') {
      const descInput = document.createElement('textarea');
      descInput.value = this.description;
      descInput.className = 'text-base text-slate-600 mb-6 border-b border-transparent focus:border-emerald-400 focus:outline-none w-full px-3 py-2 transition-colors resize-y rounded-md';
      descInput.placeholder = 'Agrega una descripción...';
      descInput.rows = 3;
      descInput.oninput = (e) => {
        descInput.style.height = 'auto';
        descInput.style.height = descInput.scrollHeight + 'px';
      };
      descInput.onchange = (e) => {
        console.log('✏️ [ClozeExercise] Descripción editada:', e.target.value);
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

    this.items.forEach((it, idx) => {
      const card = document.createElement('div');
      card.className = 'bg-gradient-to-br from-white to-slate-50 border-2 border-slate-200 rounded-2xl p-5 hover:border-emerald-400 hover:shadow-lg transition-all duration-200 flex flex-col items-center';

      const header = document.createElement('div');
      header.className = 'flex items-center gap-3 mb-4';

      const badge = document.createElement('div');
      badge.className = 'w-8 h-8 bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-full flex items-center justify-center font-bold text-sm shadow-lg';
      badge.textContent = idx + 1;

      const titleText = document.createElement('span');
      titleText.className = 'text-sm font-semibold text-slate-700';
      titleText.textContent = 'Completa la frase';

      header.appendChild(badge);
      header.appendChild(titleText);

      const imgWrapper = document.createElement('div');
      imgWrapper.className = 'relative group mb-4 flex justify-center w-full';

      const imgBox = document.createElement('div');
      imgBox.className = 'w-40 h-40 rounded-xl overflow-hidden border-3 border-slate-300 shadow-lg group-hover:border-emerald-500 transition-all flex items-center justify-center mx-auto';

      const img = document.createElement('img');
      img.src = it.imageSrc;
      img.alt = '';
      img.className = 'w-full h-full object-cover';

      const btn = document.createElement('button');
      btn.textContent = '🔄';
      btn.className = 'no-imprimir absolute -top-2 -right-2 w-8 h-8 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full shadow-lg flex items-center justify-center text-sm opacity-0 group-hover:opacity-100 transition-opacity';
      btn.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log(`🖱️ [ClozeExercise] Botón clickeado para item ${idx + 1}`);
        window.openImageModal?.(it.searchQuery || it.imageSrc, (newUrl) => {
          console.log(`✓ [ClozeExercise] Imagen actualizada para item ${idx + 1}:`, newUrl);
          img.src = newUrl;
          it.imageSrc = newUrl;
          this._syncToSession(`items.${idx}.imageSrc`, newUrl);
        });
      };

      imgBox.appendChild(img);
      imgWrapper.appendChild(imgBox);
      imgWrapper.appendChild(btn);

      const inputWrapper = document.createElement('div');
        inputWrapper.className = 'relative w-full flex';

      const textarea = document.createElement('textarea');
      textarea.value = it.placeholder || '_________';
      textarea.className = 'w-full md:w-[95%] px-3 py-2 text-center text-sm font-bold text-slate-800 bg-white border-2 border-slate-300 rounded-lg focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-all resize-y';
      textarea.placeholder = 'Escribe aquí...';
      textarea.rows = 3;
      textarea.onchange = (e) => {
        console.log(`✏️ [ClozeExercise] Placeholder editado para item ${idx + 1}:`, e.target.value);
        it.placeholder = e.target.value;
        this._syncToSession(`items.${idx}.placeholder`, e.target.value);
      };

      inputWrapper.appendChild(textarea);

      card.appendChild(header);
      card.appendChild(imgWrapper);
      card.appendChild(inputWrapper);
      grid.appendChild(card);
    });

    container.appendChild(grid);
      console.log(`✓ [ClozeExercise] ${this.items.length} items renderizados`);
    } catch (err) {
      if (window.handleModelError) window.handleModelError('ClozeExercise', err);
      console.error('ClozeExercise.renderInto error', err);
    }
  }

  getJSON() {
    try {
      return {
        title: this.title,
        description: this.description,
        items: this.items.map(({ imageSrc, placeholder }) => ({ imageSrc, placeholder }))
      };
    } catch (err) {
      if (window.handleModelError) window.handleModelError('ClozeExercise', err);
      console.error('ClozeExercise.getJSON error', err);
      return { title: this.title, description: this.description, items: [] };
    }
  }

  static getJSONSchemaString() {
    return '{"title":"Completa las oraciones con la imagen correcta","description":"Observa las imágenes y completa con la palabra que falta.","items":[{"imageSrc":"img/manzana.png","placeholder":"La _______ es roja."},{"imageSrc":"img/perro.png","placeholder":"El _______ ladra fuerte."},{"imageSrc":"img/libro.png","placeholder":"Leo un _______ en la escuela."}]}'
  }

  _syncToSession(path, value) {
    if (!this.sessionId || !window.ejercicioSessionService) {
      return;
    }

    window.ejercicioSessionService.updateContent(this.sessionId, path, value)
      .catch(err => console.error(`❌ [ClozeExercise] Error sincronizando ${path}:`, err));
  }
}

export default ClozeExercise;
