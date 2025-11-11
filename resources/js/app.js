import 'flowbite';
import 'quill/dist/quill.core.css';
import 'quill/dist/quill.bubble.css';
import 'quill/dist/quill.snow.css'; // <-- agregar para mostrar toolbar snow

// Añadir imports para marked y axios y exponerlos globalmente
import { marked } from 'marked';
import axios from 'axios';
window.marked = marked;
window.axios = axios;