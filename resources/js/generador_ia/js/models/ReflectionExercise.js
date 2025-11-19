class ReflectionExercise {

    constructor(title = '', description = '') {
        this.tipo = 'ReflectionExercise';
        this.title = title;
        this.description = description;
        this.text = '';
        this.imageSrc = '';
        this.searchQuery = '';
        this.questions = [];
        this.sessionId = null;
    }


    // Calcula el número de filas para el textarea de texto según el contenido y formato A4
    _calculateTextRows(text) {
        // Tamaño mínimo y máximo de filas para hoja A4 y fuente pequeña
        const minRows = 8; // más espacio por defecto
        const maxRows = 30; // máximo para evitar desbordar A4
        if (!text) return minRows;
        // Calcula líneas reales y líneas estimadas por longitud
        const lines = text.split(/\r?\n/).length;
        // Para fuente pequeña y ancho A4, aprox 90 caracteres por fila
        const approxExtra = Math.ceil(text.length / 90);
        // Suma 4 filas extra para margen visual
        return Math.max(minRows, Math.min(lines + approxExtra + 4, maxRows));
    }

    setTitle(t) { this.title = t; }
    setDescription(d) { this.description = d; }
    setText(text) { this.text = text; }
    setSessionId(id) { this.sessionId = id; }

    async setImage(imageSrc) {
        if (window.getFirstImage && imageSrc) {
            try {
                const autoImageUrl = await window.getFirstImage(imageSrc);
                this.imageSrc = autoImageUrl;
                this.searchQuery = imageSrc;
                return;
            } catch (error) {
                console.error('[ReflectionExercise] Error en asignación automática:', error);
            }
        }
        this.imageSrc = imageSrc;
        this.searchQuery = imageSrc;
    }

    addQuestion(question) { this.questions.push(question); }

    renderInto(container) {
        console.log('🎨 [ReflectionExercise] Renderizando:', this.title);

        const titleInput = document.createElement('input');
        titleInput.type = 'text';
        titleInput.value = this.title;
        titleInput.className = 'text-3xl font-extrabold text-slate-900 mb-3 border-b-2 border-transparent focus:border-violet-600 focus:outline-none w-full px-3 py-2 transition-colors';
        titleInput.onchange = (e) => {
            console.log('✏️ [ReflectionExercise] Título editado:', e.target.value);
            this.title = e.target.value;
            this._syncToSession('title', e.target.value);
        };
        container.appendChild(titleInput);

        if (this.description || this.description === '') {
            const descInput = document.createElement('textarea');
            descInput.value = this.description;
            descInput.rows = 2;
            descInput.className = 'text-base text-slate-600 mb-6 border-b border-transparent focus:border-violet-400 focus:outline-none w-full px-3 py-2 transition-colors resize-none';
            descInput.placeholder = 'Agrega una descripción...';
            descInput.onchange = (e) => {
                console.log('✏️ [ReflectionExercise] Descripción editada:', e.target.value);
                this.description = e.target.value;
                this._syncToSession('description', e.target.value);
            };
            container.appendChild(descInput);
        }

        const textCard = document.createElement('div');
        textCard.className = 'bg-gradient-to-br from-white to-slate-50 border-2 border-slate-200 rounded-2xl p-6 mb-6 shadow-md';

        const textHeader = document.createElement('div');
        textHeader.className = 'flex items-center gap-2 mb-4 pb-3 border-b-2 border-slate-200';


        const textTitle = document.createElement('h3');
        textTitle.className = 'text-lg font-bold text-slate-800';
        textTitle.textContent = 'Texto de Lectura';

        textHeader.appendChild(textTitle);

        if (this.imageSrc) {
            const imgWrapper = document.createElement('div');
            imgWrapper.className = 'relative group mb-6 flex justify-center';

            const imgBox = document.createElement('div');
            imgBox.className = 'w-72 h-72 rounded-2xl overflow-hidden border-3 border-slate-300 shadow-xl group-hover:border-violet-500 transition-all';

            const img = document.createElement('img');
            img.src = this.imageSrc;
            img.alt = this.title || 'Imagen ilustrativa';
            img.className = 'w-full h-full object-cover';

            const btn = document.createElement('button');
            btn.textContent = '🔄';
            btn.className = 'no-imprimir absolute top-4 right-4 px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-lg shadow-lg font-semibold text-sm opacity-0 group-hover:opacity-100 transition-all';
            btn.onclick = () => {
                console.log('🖱️ [ReflectionExercise] Botón cambiar imagen clickeado');
                window.openImageModal?.(this.searchQuery || this.imageSrc, (newUrl) => {
                    console.log('✓ [ReflectionExercise] Imagen actualizada:', newUrl);
                    img.src = newUrl;
                    this.imageSrc = newUrl;
                    this._syncToSession('imageSrc', newUrl);
                });
            };

            imgBox.appendChild(img);
            imgWrapper.appendChild(imgBox);
            imgWrapper.appendChild(btn);
            textCard.appendChild(imgWrapper);
        }

        const textArea = document.createElement('textarea');
        textArea.value = this.text;
        textArea.className = 'w-full px-4 py-4 text-base leading-relaxed text-slate-800 bg-white border-2 border-slate-300 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-violet-500 focus:outline-none resize-y transition-all';
        textArea.rows = this._calculateTextRows(this.text);
        textArea.placeholder = 'Escribe o edita el texto de lectura aquí...';
        textArea.onchange = (e) => {
            console.log('✏️ [ReflectionExercise] Texto editado');
            this.text = e.target.value;
            textArea.rows = this._calculateTextRows(e.target.value);
            this._syncToSession('text', e.target.value);
        };

        textCard.appendChild(textHeader);
        textCard.appendChild(textArea);
        container.appendChild(textCard);

        const questionsHeader = document.createElement('div');
        questionsHeader.className = 'flex items-center gap-2 mb-4 mt-8';

        const questionsIcon = document.createElement('div');
        questionsIcon.className = 'w-8 h-8 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center text-white text-lg shadow-lg';
        questionsIcon.innerHTML = '❓';

        const questionsTitle = document.createElement('h3');
        questionsTitle.className = 'text-lg font-bold text-slate-800';
        questionsTitle.textContent = 'Preguntas de Reflexión';

        questionsHeader.appendChild(questionsIcon);
        questionsHeader.appendChild(questionsTitle);
        container.appendChild(questionsHeader);

        const questionsList = document.createElement('div');
        questionsList.className = 'space-y-4';

        this.questions.forEach((q, idx) => {
            const questionCard = document.createElement('div');
            questionCard.className = 'bg-gradient-to-br from-white to-orange-50 border-2 border-orange-200 rounded-xl p-4 hover:border-orange-400 hover:shadow-md transition-all duration-200';

            const questionHeader = document.createElement('div');
            questionHeader.className = 'flex items-start gap-3';

            const number = document.createElement('div');
            number.className = 'flex-shrink-0 w-8 h-8 bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg mt-1';
            number.textContent = idx + 1;

            const textarea = document.createElement('textarea');
            textarea.value = q;
            textarea.className = 'flex-1 px-4 py-3 text-sm font-bold text-slate-800 bg-white border-2 border-orange-200 rounded-lg focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 transition-all resize-y';
            textarea.placeholder = 'Escribe una pregunta...';
            textarea.rows = 3;
            textarea.onchange = (e) => {
                console.log(`✏️ [ReflectionExercise] Pregunta ${idx + 1} editada:`, e.target.value);
                this.questions[idx] = e.target.value;
                this._syncToSession(`questions.${idx}`, e.target.value);
            };

            questionHeader.appendChild(number);
            questionHeader.appendChild(textarea);
            questionCard.appendChild(questionHeader);
            questionsList.appendChild(questionCard);
        });

        container.appendChild(questionsList);
        console.log(`✓ [ReflectionExercise] ${this.questions.length} preguntas renderizadas`);
    }

    getJSON() {
        return {
            title: this.title,
            description: this.description,
            text: this.text,
            imageSrc: this.imageSrc,
            questions: this.questions
        };
    }

    static getJSONSchemaString() {
        return '{"title":"Lectura reflexiva: El ciclo del agua","description":"Lee el siguiente texto y responde las preguntas de reflexión.","imageSrc":"ciclo del agua ilustración","text":"El agua es uno de los recursos más importantes de nuestro planeta... hacia ríos y océanos, comenzando el ciclo nuevamente.","questions":["¿Por qué el agua es importante para nuestro planeta?","¿Qué causa la evaporación del agua?","¿Qué sucede cuando el vapor de agua sube a la atmósfera?","¿A dónde va el agua después de que llueve?","¿Por qué crees que es importante cuidar el agua?"]}'
    }

    _syncToSession(path, value) {
        if (!this.sessionId || !window.ejercicioSessionService) {
            return;
        }

        window.ejercicioSessionService.updateContent(this.sessionId, path, value)
            .catch(err => console.error(`❌ [ReflectionExercise] Error sincronizando ${path}:`, err));
    }
}

export default ReflectionExercise;