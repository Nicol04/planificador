import { Aprendizaje } from '../models/Aprendizaje.js';

export class AprendizajeController {
  constructor() {
    this.aprendizajes = [];
  }

  obtenerDatosSesion() {
    return {
      titulo: localStorage.getItem('sesion_titulo') || '',
      proposito_sesion: localStorage.getItem('sesion_proposito_sesion') || '',
      tiempo_estimado: localStorage.getItem('sesion_tiempo_estimado') || '',
      aula_curso_id: localStorage.getItem('sesion_aula_curso_id') || '',
      grado: localStorage.getItem('sesion_grado') || '',
      nivel: localStorage.getItem('sesion_nivel') || '',
      curso: localStorage.getItem('sesion_curso') || '',
      tema: '', // Puedes agregar el tema si lo tienes en otro campo
    };
  }

  agregarAprendizaje(data) {
    const aprendizaje = new Aprendizaje(data);
    this.aprendizajes.push(aprendizaje);
    return aprendizaje;
  }

  eliminarAprendizaje(index) {
    if (index >= 0 && index < this.aprendizajes.length) {
      this.aprendizajes.splice(index, 1);
    }
  }

  obtenerAprendizajes() {
    return this.aprendizajes;
  }
}
