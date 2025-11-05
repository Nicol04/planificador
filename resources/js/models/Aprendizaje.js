export class Aprendizaje {
  constructor({
    tema = '',
    titulo = '',
    proposito = '',
    competencia = '',
    capacidades = '',
    estandares = '',
    criterios = '',
    evidencias = '',
    instrumentos = '',
    genero = '',
    grado_aula = ''
  } = {}) {
    this.tema = tema;
    this.titulo = titulo;
    this.proposito = proposito;
    this.competencia = competencia;
    this.capacidades = capacidades;
    this.estandares = estandares;
    this.criterios = criterios;
    this.evidencias = evidencias;
    this.instrumentos = instrumentos;
    this.genero = genero;
    this.grado_aula = grado_aula;
  }

  static fromSessionData(s = {}) {
    return new Aprendizaje({
      tema: s.tema || '',
      titulo: s.titulo || '',
      proposito: s.proposito_sesion || s.proposito || '',
      competencia: Array.isArray(s.competencias) ? s.competencias.map(c => c.competencia_nombre).join(', ') : (s.competencias || ''),
      capacidades: Array.isArray(s.competencias) ? s.competencias.map(c => c.capacidades.join(', ')).join('; ') : (s.capacidades || ''),
      estandares: Array.isArray(s.competencias) ? s.competencias.map(c => c.estandares.join(', ')).join('; ') : (s.estandares || ''),
      criterios: Array.isArray(s.competencias) ? s.competencias.map(c => c.criterios.join(', ')).join('; ') : (s.criterios || ''),
      evidencias: Array.isArray(s.evidencias) ? s.evidencias.join(', ') : (s.evidencias || ''),
      instrumentos: Array.isArray(s.competencias) ? s.competencias.map(c => [...(c.instrumentos_predefinidos ? [c.instrumentos_predefinidos] : []), ...(c.instrumentos_personalizados || [])].join(', ')).join('; ') : (s.instrumentos || ''),
      genero: s.genero || '',
      grado_aula: s.grado_aula || ''
    });
  }
}
