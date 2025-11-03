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

  getContent(selector) {
    if (this.editors[selector]) {
      return this.editors[selector].root.innerHTML;
    }
    return '';
  }

  setMarkdown(selector, markdown) {
    if (this.editors[selector]) {
      const normalized = this._normalizeMarkdown(markdown);
      const html = marked.parse(normalized);
      this.editors[selector].root.innerHTML = html;
    }
  }

  // Normaliza texto Markdown recibido de la API para evitar que aparezcan
  // secuencias escapadas literales como "\n", comillas envolventes, o
  // bloques con triple backticks sin procesar.
  _normalizeMarkdown(markdown) {
    if (typeof markdown !== 'string') return '';
    let s = markdown;

    // Unwrap whole-string surrounding quotes
    if ((s.startsWith('"') && s.endsWith('"')) || (s.startsWith("'") && s.endsWith("'"))) {
      s = s.slice(1, -1);
    }

    // Replace escaped CRLF / newlines ("\\r\\n", "\\n") with real newlines
    s = s.replace(/\\r\\n/g, '\n').replace(/\\n/g, '\n').replace(/\\r/g, '\n');

    // Unescape common escaped characters
    s = s.replace(/\\"/g, '"').replace(/\\'/g, "'").replace(/\\\\/g, '\\');

    // Remove wrapping triple backticks and an optional language tag
    s = s.replace(/^```[a-zA-Z0-9_+-]*\n?/, '').replace(/\n?```$/, '');

    // Trim whitespace and normalize multiple blank lines to max two
    s = s.trim().replace(/\n{3,}/g, '\n\n');

    return s;
  }
}