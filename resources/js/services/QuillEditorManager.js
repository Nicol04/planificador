// ...existing code...
import Quill from "quill";
import { marked } from "marked";

export class QuillEditorManager {
  constructor() {
    this.editors = {};
  }

  initializeEditor(selector, theme = 'bubble') {
    const editor = new Quill(selector, { theme });
    this.editors[selector] = editor;
    return editor;
  }

  setContent(selector, content) {
    if (this.editors[selector]) {
      this.editors[selector].root.innerHTML = content;
    }
  }

  // Nuevo: inyectar HTML tal cual (sin normalizar)
  setHTML(selector, html) {
    if (this.editors[selector]) {
      this.editors[selector].root.innerHTML = html || '';
    }
  }

  getContent(selector) {
    if (this.editors[selector]) {
      return this.editors[selector].root.innerHTML;
    }
    return '';
  }

  setMarkdown(selector, markdown) {
    if (!this.editors[selector]) return;

    // Si ya es HTML, no normalizar ni pasar por marked
    if (typeof markdown === 'string' && /^\s*</.test(markdown)) {
      this.editors[selector].root.innerHTML = markdown;
      return;
    }

    const normalized = this._normalizeMarkdown(markdown);
    const html = marked.parse(normalized);
    this.editors[selector].root.innerHTML = html;
  }

  // Normaliza texto Markdown recibido de la API, pero de forma NO agresiva.
  _normalizeMarkdown(markdown) {
    if (typeof markdown !== 'string') return '';
    let s = markdown;

    // Reemplaza secuencias escapadas de nueva línea por saltos reales
    s = s.replace(/\\r\\n/g, '\n').replace(/\\n/g, '\n').replace(/\\r/g, '\n');

    // Si NO parece contener HTML, desescapa comillas/backslashes
    if (!/<[a-z][\s\S]*>/i.test(s)) {
      s = s.replace(/\\"/g, '"').replace(/\\'/g, "'").replace(/\\\\/g, '\\');
    }

    // Desenvuelve comillas envolventes solo si no contiene tags HTML y no hay triple-backticks
    if (((s.startsWith('"') && s.endsWith('"')) || (s.startsWith("'") && s.endsWith("'")))
        && !s.includes('```') && !/<[a-z][\s\S]*>/i.test(s)) {
      s = s.slice(1, -1);
    }

    // No eliminar triple backticks: dejar que marked los procese como code fences
    // Normaliza saltos de línea múltiples
    s = s.trim().replace(/\n{3,}/g, '\n\n');

    return s;
  }
}