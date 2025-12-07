class ClassificationExercise {
  constructor(title = '', description = '') {
    this.tipo = 'ClassificationExercise';
    this.title = title;
    this.description = description;
    this.images = [];
    this.texts = [];
    this.sessionId = null;
  }

  setTitle(t) { this.title = t; }
  setDescription(d) { this.description = d; }
  setSessionId(id) { this.sessionId = id; }

  async add(imageSrc, text) {
    let finalImage = imageSrc;

    if (window.getFirstImage && imageSrc) {
      try {
        finalImage = await window.getFirstImage(imageSrc);
      } catch (error) {
        console.error("[ClassificationExercise] Error asignando imagen automáticamente:", error);
      }
    }

    this.images.push({
      imageSrc: finalImage,
      searchQuery: imageSrc
    });

    this.texts.push(text);
  }

  shuffleImages() {
    // Crear un array de pares para barajar juntos
    const pairs = this.images.map((image, idx) => ({
      image,
      text: this.texts[idx]
    }));

    // Barajar los pares
    for (let i = pairs.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [pairs[i], pairs[j]] = [pairs[j], pairs[i]];
    }

    // Asignar de vuelta a los arrays originales
    this.images = pairs.map(pair => pair.image);
    this.texts = pairs.map(pair => pair.text);
  }

  renderInto(container) {
    try {
      container.innerHTML = "";
      this.shuffleImages();

      console.log('🎨 Renderizando ejercicio:', this.title);

    const titleInput = document.createElement('textarea');
    titleInput.rows = 2; // Definimos las 2 filas
    titleInput.value = this.title;
    titleInput.className = 'text-3xl font-extrabold text-slate-900 mb-3 border-b-2 border-transparent focus:border-blue-600 focus:outline-none w-full px-3 py-2 transition-colors';
    titleInput.onchange = (e) => {
      this.title = e.target.value;
      this._syncToSession('title', e.target.value);
    };
    container.appendChild(titleInput);

    const descInput = document.createElement('textarea');
    descInput.value = this.description;
    descInput.className = 'text-base text-slate-600 mb-6 border-b border-transparent focus:border-blue-400 focus:outline-none w-full px-3 py-2 transition-colors resize-y rounded-md';
    descInput.placeholder = 'Agrega una descripción...';
    descInput.rows = 3;
    descInput.oninput = (e) => {
      descInput.style.height = 'auto';
      descInput.style.height = descInput.scrollHeight + 'px';
    };
    descInput.onchange = (e) => {
      this.description = e.target.value;
      this._syncToSession('description', e.target.value);
    };
    setTimeout(() => {
      descInput.style.height = 'auto';
      descInput.style.height = descInput.scrollHeight + 'px';
    }, 0);
    container.appendChild(descInput);

    const grid = document.createElement('div');
    grid.className = 'space-y-3';

    this.texts.forEach((textValue, idx) => {
      const { imageSrc, searchQuery } = this.images[idx];

      const row = document.createElement('div');
      row.className = 'flex items-center gap-4 p-4 bg-white border-2 border-slate-200 rounded-xl hover:border-blue-400 hover:shadow-md transition-all duration-200';

      const leftSection = document.createElement('div');
      leftSection.className = 'flex items-center gap-3';

          const numberBadge = document.createElement('div');
          numberBadge.className = 'flex-shrink-0 w-8 h-8 text-white rounded-full flex items-center justify-center font-bold text-sm shadow-lg bg-slate-500';
      numberBadge.textContent = idx + 1;

      const imgWrapper = document.createElement('div');
      imgWrapper.className = 'relative group';

      const imgBox = document.createElement('div');
      imgBox.className = 'w-40 h-40 rounded-lg overflow-hidden border-2 border-slate-300 shadow-md group-hover:border-blue-500 transition-colors flex items-center justify-center mx-auto';

      const img = document.createElement('img');
      img.src = imageSrc;
      img.alt = textValue;
      img.className = 'w-full h-full object-cover';

      const btn = document.createElement('button');
      btn.className = 'no-imprimir absolute -top-2 -right-2 w-7 h-7 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity';
      btn.innerHTML = '🔄';
      btn.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        window.openImageModal?.(searchQuery, (newUrl) => {
          img.src = newUrl;
          this.images[idx].imageSrc = newUrl;
          this._syncToSession(`items.${idx}.imageSrc`, newUrl);
        });
      };

      imgBox.appendChild(img);
      imgWrapper.appendChild(imgBox);
      imgWrapper.appendChild(btn);

      leftSection.appendChild(numberBadge);
      leftSection.appendChild(imgWrapper);

      const rightSection = document.createElement('div');
      rightSection.className = 'flex-1 flex items-center gap-3';

      const connector = document.createElement('div');
      connector.className = 'hidden md:block w-12 h-0.5 from-slate-300 to-slate-400';

      const textInput = document.createElement('input');
      textInput.type = 'text';
      textInput.value = textValue;
      textInput.className = 'flex-1 px-4 py-3 text-base font-medium text-slate-800 bg-slate-50 border-2 border-slate-200 rounded-lg focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 transition-all';
      textInput.onchange = (e) => {
        this.texts[idx] = e.target.value;
        this._syncToSession(`items.${idx}.text`, e.target.value);
      };

      rightSection.appendChild(connector);
      rightSection.appendChild(textInput);

      row.appendChild(leftSection);
      row.appendChild(rightSection);
      grid.appendChild(row);
    });

    container.appendChild(grid);
    } catch (err) {
      const errMsg = `ClassificationExercise.renderInto error: ${err?.message || err}`;
      if (window.handleModelError) window.handleModelError('ClassificationExercise', err);
      console.error(errMsg, err);
    }
  }

  getJSON() {
    try {
      return {
        title: this.title,
        description: this.description,
        items: this.texts.map((text, i) => ({
          imageSrc: this.images[i]?.imageSrc || "",
          text
        }))
      };
    } catch (err) {
      if (window.handleModelError) window.handleModelError('ClassificationExercise', err);
      console.error('ClassificationExercise.getJSON error', err);
      return { title: this.title, description: this.description, items: [] };
    }
  }

  static getJSONSchemaString() {
    return '(Generarás un JSON donde imageSrc contendrá rutas de imágenes asignadas de forma aleatoria y text contendrá nombres generados según el tema. El propósito es crear un ejercicio donde el usuario debe unir cada imagen con su nombre correspondiente.) {"title":"Ejercicio de ejemplo","description":"Une la foto con su nombre.","items":[{"imageSrc":"img/a.png","text":"Texto 1"},{"imageSrc":"img/b.png","text":"Texto 2"}]}';
  }

  _syncToSession(path, value) {
    if (!this.sessionId || !window.ejercicioSessionService) {
      return;
    }

    window.ejercicioSessionService.updateContent(this.sessionId, path, value)
      .catch(err => console.error(`❌ [ClassificationExercise] Error sincronizando ${path}:`, err));
  }
}

export default ClassificationExercise;
