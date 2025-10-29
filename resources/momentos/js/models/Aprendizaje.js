export class Aprendizaje {
  constructor({
    titulo = '',
    tema = '',
    proposito_sesion = '',
    aula_curso_id = '',
    curso = '',
    aula = '',
    nivel = '',
  } = {}) {
    this.titulo = titulo;
    this.tema = tema;
    this.proposito_sesion = proposito_sesion;
    this.aula_curso_id = aula_curso_id;
    this.curso = curso;
    this.aula = aula;
    this.nivel = nivel;
  }
}
