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
      const html = marked.parse(markdown);
      this.editors[selector].root.innerHTML = html;
    }
  }
}