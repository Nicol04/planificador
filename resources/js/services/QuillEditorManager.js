// ...existing code...
import Quill from "quill";
import { marked } from "marked";

export class QuillEditorManager {
  constructor() {
    this.editors = {};
    this._hiddenMap = {
      '#inicio-editor': 'inicioInput',
      '#desarrollo-editor': 'desarrolloInput',
      '#conclusion-editor': 'conclusionInput',
    };
  }

  // Cambiado: aceptar opciones (placeholder, modules, etc.)
  initializeEditor(selector, theme = 'snow', options = {}) {
    // Si el elemento no existe, salir
    const el = document.querySelector(selector);
    if (!el) return null;

    // Si ya existe instancia en este elemento, devolverla (evita duplicados)
    if (this.editors[selector]) {
        return this.editors[selector];
    }const config = {
        theme,
        modules: options.modules ?? { toolbar: false },
        placeholder: options.placeholder ?? 'Escribe aquí...'
    };

    // Inicializar Quill
    const editor = new Quill(el, config);
    this.editors[selector] = editor;

    // Lógica de Sincronización (Editor -> Input Oculto)
    const hiddenId = this._hiddenMap[selector];
    
    const syncToInput = () => {
        const hid = document.getElementById(hiddenId);
        if (hid && editor.root.innerHTML !== hid.value) {
            hid.value = editor.root.innerHTML;
            // Disparar eventos para que Livewire/JS detecten el cambio
            hid.dispatchEvent(new Event('input', { bubbles: true }));
            hid.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    editor.on('text-change', () => syncToInput());

    // Cargar contenido inicial si el input oculto tiene datos y el editor está vacío
    const hid = document.getElementById(hiddenId);
    if (hid && hid.value && editor.getText().trim() === "") {
        editor.clipboard.dangerouslyPasteHTML(hid.value);
    }

    return editor;
  }

  // Ensure editor exists and is editable
  ensureEditable(selector) {
    const editor = this.editors[selector];
    if (editor) {
      try {
        editor.enable(true);
        if (editor.root) editor.root.setAttribute('contenteditable','true');
      } catch (e) {}
      return true;
    }
    // intentar crear si no existe
    const created = this.initializeEditor(selector);
    if (created) {
      try { created.enable(true); created.root.setAttribute('contenteditable','true'); } catch (e) {}
      return true;
    }
    // No se pudo crear editor
    return false;
  }

  // Forzar que el contenedor sea editable y remover posibles bloqueos (pointer-events, disabled clases comunes)
  fixEditable(selector) {
    const el = document.querySelector(selector);
    if (!el) return;
    // asegurar contenteditable en el root del editor si existe
    const editor = this.editors[selector];
    if (editor && editor.root) editor.root.setAttribute('contenteditable','true');

    // Subir por los ancestros y remover estilos/atributos que suelen bloquear interacción en Filament
    let node = el;
    for (let i = 0; i < 6 && node; i++, node = node.parentElement) {
      // eliminar atributo disabled si está solo en el contenedor del editor (no tocar inputs)
      if (node.hasAttribute && node.hasAttribute('disabled')) {
        try { node.removeAttribute('disabled'); } catch (e) {}
      }
      // eliminar estilos inline que bloquean
      try {
        if (node.style && node.style.pointerEvents === 'none') node.style.pointerEvents = 'auto';
      } catch (e) {}
      // eliminar clases típicas que Filament puede aplicar (no destructivo: sólo quita clases específicas)
      if (node.classList) {
        if (node.classList.contains('opacity-50')) node.classList.remove('opacity-50');
        if (node.classList.contains('pointer-events-none')) node.classList.remove('pointer-events-none');
      }
    }
  }
  // Usar API de Quill para pegar HTML (dispara eventos)
  // Usar API de Quill para pegar HTML (dispara eventos)
  setContent(selector, content) {
    let editor = this.editors[selector];
    if (!editor) {
      editor = this.initializeEditor(selector);
    }
    if (editor) {
      if (typeof content === 'string' && /^\s*</.test(content)) {
        editor.clipboard.dangerouslyPasteHTML(content || '');
      } else {
        editor.setText(content || '');
      }
      // asegurar editable
      try { editor.enable(true); editor.root.setAttribute('contenteditable','true'); } catch (e) {}
      return;
    }

    // Fallback: si no hay editor posible, escribir HTML directo y hacerlo editable
    const el = document.querySelector(selector);
    if (el) {
      if (typeof content === 'string' && /^\s*</.test(content)) el.innerHTML = content;
      else el.textContent = content || '';
      el.setAttribute('contenteditable', 'true');
    }
  }

  // Nuevo: inyectar HTML tal cual (sin normalizar)
  setHTML(selector, htmlContent) {
    const editor = this.editors[selector];
    if (editor) {
        // dangerouslyPasteHTML limpia y formatea el HTML para Quill
        editor.clipboard.dangerouslyPasteHTML(htmlContent);
    } else {
        // Fallback por si el editor no cargó visualmente aún
        const el = document.querySelector(selector);
        if (el) el.innerHTML = htmlContent;
    }
  }

  getContent(selector) {
    if (this.editors[selector]) {
      return this.editors[selector].root.innerHTML;
    }
    const el = document.querySelector(selector);
    return el ? el.innerHTML : '';
  }


  setMarkdown(selector, markdown) {
    let editor = this.editors[selector];
    if (!editor) {
      editor = this.initializeEditor(selector);
    }

    if (editor) {
      // Si ya es HTML, no normalizar ni pasar por marked
      if (typeof markdown === 'string' && /^\s*</.test(markdown)) {
        editor.root.innerHTML = markdown;
        try { editor.enable(true); editor.root.setAttribute('contenteditable','true'); } catch (e) {}
        // sincronizar hidden
        const hid = this._hiddenMap[selector] && document.getElementById(this._hiddenMap[selector]);
        if (hid) { hid.value = editor.root.innerHTML; hid.dispatchEvent(new Event('input', { bubbles: true })); }
        return;
      }

      const normalized = this._normalizeMarkdown(markdown);
      const html = marked.parse(normalized);
      editor.root.innerHTML = html;
      try { editor.enable(true); editor.root.setAttribute('contenteditable','true'); } catch (e) {}
      const hid2 = this._hiddenMap[selector] && document.getElementById(this._hiddenMap[selector]);
      if (hid2) { hid2.value = editor.root.innerHTML; hid2.dispatchEvent(new Event('input', { bubbles: true })); }
      return;
    }

    // Fallback: escribir HTML en el contenedor y hacerlo editable
    const el = document.querySelector(selector);
    if (el) {
      if (typeof markdown === 'string' && /^\s*</.test(markdown)) el.innerHTML = markdown;
      else el.innerHTML = marked.parse(this._normalizeMarkdown(markdown || ''));
      el.setAttribute('contenteditable', 'true');
      const hid3 = this._hiddenMap[selector] && document.getElementById(this._hiddenMap[selector]);
      if (hid3) { hid3.value = el.innerHTML; hid3.dispatchEvent(new Event('input', { bubbles: true })); }
    }
  }

  // Normaliza texto Markdown recibido de la API, pero de forma NO agresiva.
  _normalizeMarkdown(markdown) {
    if (typeof markdown !== 'string') return '';
    let s = markdown;
    s = s.replace(/\\r\\n/g, '\n').replace(/\\n/g, '\n').replace(/\\r/g, '\n');
    if (!/<[a-z][\s\S]*>/i.test(s)) {
      s = s.replace(/\\"/g, '"').replace(/\\'/g, "'").replace(/\\\\/g, '\\');
    }
    if (((s.startsWith('"') && s.endsWith('"')) || (s.startsWith("'") && s.endsWith("'")))
        && !s.includes('```') && !/<[a-z][\s\S]*>/i.test(s)) {
      s = s.slice(1, -1);
    }
    s = s.trim().replace(/\n{3,}/g, '\n\n');
    return s;
  }
}