export class Aprendizaje {
  constructor({
    nombre = '',
    proposito = '',
    competencia = '',
    capacidades = '',
    desempenos = '',
    criterios = '',
    evidencias = '',
    instrumentos = ''
  } = {}) {
    this.nombre = nombre;
    this.proposito = proposito;
    this.competencia = competencia;
    this.capacidades = capacidades;
    this.desempenos = desempenos;
    this.criterios = criterios;
    this.evidencias = evidencias;
    this.instrumentos = instrumentos;
  }
}
