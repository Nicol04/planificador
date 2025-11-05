import { Aprendizaje } from '../models/Aprendizaje.js';

export class AprendizajeController {
  constructor() {
    this.aprendizajes = [];
  }

  agregarAprendizaje(data) {
    const aprendizaje = data instanceof Aprendizaje ? data : new Aprendizaje(data);
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
