export class Aprendizaje {
  constructor({
    titulo = '',
    tema = '',
    proposito = '',
    competencia = '',
    capacidades = '',
    desempenos = '',
    criterios = '',
    evidencias = '',
    instrumentos = ''
  } = {}) {
    this.titulo = titulo;
    this.tema = tema;
    this.proposito = proposito;
    this.competencia = competencia;
    this.capacidades = capacidades;
    this.desempenos = desempenos;
    this.criterios = criterios;
    this.evidencias = evidencias;
    this.instrumentos = instrumentos;
  }
}
