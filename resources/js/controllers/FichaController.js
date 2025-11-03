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
    
    // Construir descripción con todos los datos del aprendizaje
    const descripcion = this._aprendizajesPrompt();
    
    // Llamar al método unificado de GeminiService que genera las 3 secciones en una sola petición
    const resultado = await this.gemini.generarTodo(descripcion);
    
    // Asignar los objetos generados
    this.inicio = resultado.inicio;
    this.desarrollo = resultado.desarrollo;
    this.conclusion = resultado.conclusion;
    
    console.log('✅ Ficha completa generada:', { inicio: this.inicio, desarrollo: this.desarrollo, conclusion: this.conclusion });
    
    // Actualizar la UI
    window.renderFicha();
  }


  _aprendizajesPrompt() {
    if (!this.aprendizajes || this.aprendizajes.length === 0) return '';
    const a = this.aprendizajes[0];
    return `\n\nContexto de aprendizaje:\nNombre de la sesión: ${a.nombre}\nPropósito: ${a.proposito}\nCompetencia: ${a.competencia}\nCapacidades: ${a.capacidades}\nDesempeños: ${a.desempenos}\nCriterios: ${a.criterios}\nEvidencias: ${a.evidencias}\nInstrumentos: ${a.instrumentos}`;
  }

  async generarInicio() {
    console.log('🟢 Generando Inicio...');
    const prompt = `Eres un asistente pedagógico. Genera el texto del "Inicio" de una ficha educativa en formato JSON:\n{\n  "texto": "..."\n}\nDebe introducir el tema, motivar al estudiante y conectar con sus conocimientos previos. ${this._aprendizajesPrompt()}`;
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
    const prompt = `Eres un asistente pedagógico. Genera el texto del "Desarrollo" de una ficha educativa en formato JSON:\n{\n  "texto": "..."\n}\nDebe presentar los contenidos principales con lenguaje claro y didáctico. ${this._aprendizajesPrompt()}`;
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
    const prompt = `Eres un asistente pedagógico. Genera el texto de la "Conclusión" de una ficha educativa en formato JSON:\n{\n  "texto": "..."\n}\nDebe resumir lo aprendido y motivar la reflexión del estudiante. ${this._aprendizajesPrompt()}`;
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
