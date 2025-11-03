import 'flowbite';
import 'quill/dist/quill.core.css';
import 'quill/dist/quill.bubble.css';

// Añadir imports para marked y axios y exponerlos globalmente
import { marked } from 'marked';
import axios from 'axios';
window.marked = marked;
window.axios = axios;