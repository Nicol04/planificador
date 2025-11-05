import { Inicio } from "../models/Inicio.js";
import { Desarrollo } from "../models/Desarrollo.js";
import { Conclusion } from "../models/Conclusion.js";
import { GeminiService } from "../services/GeminiService.js";

export class FichaController {

  constructor(apiKey) {
    this.gemini = new GeminiService(apiKey);
    this.inicio = new Inicio();
    this.desarrollo = new Desarrollo();
    this.conclusion = new Conclusion();
    this.aprendizajes = [];
  }

  setAprendizajes(aprendizajes) {
    this.aprendizajes = aprendizajes;
  }

  async generarTodo() {
    console.log('🚀 Generando ficha completa con contexto de aprendizaje...');
    const descripcion = this._aprendizajesPrompt();
    const resultado = await this.gemini.generarTodo(descripcion);
    this.inicio = resultado.inicio;
    this.desarrollo = resultado.desarrollo;
    this.conclusion = resultado.conclusion;
    console.log('✅ Ficha completa generada:', { inicio: this.inicio, desarrollo: this.desarrollo, conclusion: this.conclusion });
    window.renderFicha();
  }


  _aprendizajesPrompt() {
    if (!this.aprendizajes || this.aprendizajes.length === 0) return '';
    const a = this.aprendizajes[0];
    const generoDocente = a.genero && a.genero.toLowerCase() === 'femenino' ? 'La docente' : 'El docente';

    return `\n\nContexto de aprendizaje:
    Tema: ${a.tema}
    Título: ${a.titulo}
    Propósito: ${a.proposito}
    Género del docente: ${a.genero || 'N/A'} (${generoDocente})
    Grado del aula: ${a.grado_aula || 'N/A'}
    Competencias: ${a.competencia}
    Capacidades: ${a.capacidades}
    Estándares: ${a.estandares}
    Criterios: ${a.criterios}
    Evidencias: ${a.evidencias}
    Instrumentos: ${a.instrumentos}`;
  }

  async generarInicio() {
    console.log('🟢 Generando Inicio...');
    //const prompt = `Eres un asistente pedagógico. Genera el texto del "Inicio" de una ficha educativa en formato JSON:\n{\n  "texto": "..."\n}\nDebe introducir el tema, motivar al estudiante y conectar con sus conocimientos previos. ${this._aprendizajesPrompt()}`;

    const prompt = `
Eres un asistente pedagógico experto en planificación de clases. 
Genera el texto del "Inicio" de una ficha educativa en formato JSON:

{
  "texto": "..."
}

El Inicio debe contener:
- Saludo inicial, oración o referencia al lema ( "Siempre bendecidos y listos para aprender").
- Introducción antes de entrar al tema, haciendo preguntas simples para motivar la curiosidad.
- Comunicar claramente el propósito de la sesión.
- Dar a conocer los criterios de la sesión.
- Establecer los acuerdos del día.
- Utiliza el contexto del aprendizaje proporcionado:
${this._aprendizajesPrompt()}
`;

    const schema = {
      "type": "OBJECT",
      "properties": {
        "texto": { "type": "STRING" }
      }
    };
    const json = await this.gemini.generar(prompt, schema);
    console.log('✅ Inicio generado:', json);
    this.inicio.fromJson(json);
    window.renderFicha();
    return this.inicio;
  }


  async generarDesarrollo() {
    //const prompt = `Eres un asistente pedagógico. Genera el texto del "Desarrollo" de una ficha educativa en formato JSON:\n{\n  "texto": "..."\n}\nDebe presentar los contenidos principales con lenguaje claro y didáctico. ${this._aprendizajesPrompt()}`;

    const prompt = `
Eres un asistente pedagógico experto en planificación de clases. 
Genera el texto del "Desarrollo" de una ficha educativa en formato JSON:

{
  "texto": "..."
}

El Desarrollo debe incluir:
- ${this.aprendizajes.length > 0 ? (this.aprendizajes[0].genero.toLowerCase() === 'femenino' ? 'La docente' : 'El docente') : 'El docente'} presenta la situación de aprendizaje.
- Explicación del tema, competencias, capacidades y estándares según el grado del aula.
- Actividades de análisis, búsqueda de información, preguntas y resolución de problemas.
- Indicar cómo los estudiantes aplicarán lo aprendido y elaborarán productos o evidencias.
- Referirse al contexto del aprendizaje proporcionado:
${this._aprendizajesPrompt()}
`;

    const schema = {
      "type": "OBJECT",
      "properties": {
        "texto": { "type": "STRING" }
      }
    };
    const json = await this.gemini.generar(prompt, schema);
    this.desarrollo.fromJson(json);
    window.renderFicha();
    return this.desarrollo;
  }


  async generarConclusion() {
    //const prompt = `Eres un asistente pedagógico. Genera el texto de la "Conclusión" de una ficha educativa en formato JSON:\n{\n  "texto": "..."\n}\nDebe resumir lo aprendido y motivar la reflexión del estudiante. ${this._aprendizajesPrompt()}`;

    const prompt = `
Eres un asistente pedagógico experto en planificación de clases. 
Genera el texto de la "Conclusión" de una ficha educativa en formato JSON:

{
  "texto": "..."
}

La Conclusión debe incluir:
- Reflexión y metacognición guiada por ${this.aprendizajes.length > 0 ? (this.aprendizajes[0].genero.toLowerCase() === 'femenino' ? 'la docente' : 'el docente') : 'el docente'}.
- Preguntas para reforzar lo aprendido y fomentar análisis personal.
- Recordar cómo las actividades realizadas se conectan con los criterios, competencias y evidencias de la sesión.
- Basarse en el contexto del aprendizaje proporcionado:
${this._aprendizajesPrompt()}
`;

    const schema = {
      "type": "OBJECT",
      "properties": {
        "texto": { "type": "STRING" }
      }
    };
    const json = await this.gemini.generar(prompt, schema);
    this.conclusion.fromJson(json);
    window.renderFicha();
    return this.conclusion;
  }
}
