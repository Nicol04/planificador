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
Eres un asistente pedagógico experta/o en planificación de sesiones.
Genera ÚNICAMENTE un JSON válido (sin texto adicional) con esta estructura EXACTA:
{
  "texto": "<HTML aquí>"
}

El campo "texto" debe contener HTML (usa <p>, <strong>, <em>, <ul>, <li>).
El Inicio debe incluir, en este orden, y usando lenguaje natural apropiado para el grado:
1) Saludo inicial y una breve oración o referencia al lema: "Siempre bendecidos y listos para aprender".
2) Actividad para recuperar saberes previos: 1–2 preguntas abiertas relacionadas con el TEMA (mención explícita del tema).
3) Indicación de que "La docente" o "El docente" (según el género proporcionado) anotará aportes.
4) Comunicación textual EXACTA del PROPÓSITO de la sesión (usar el texto del propósito tal cual viene en el contexto).
5) Mostrar los CRITERIOS de evaluación tal cual aparecen en el contexto (listarlos).
6) Proponer 2 normas/acuerdos del día breves y claras.

Usa el contexto de aprendizaje proporcionado a continuación para adaptar redacción y vocabulario (grado, género, evidencias, criterios, instrumentos). No incluyas instrucciones técnicas ni explicaciones sobre el JSON, responde SOLO con el JSON pedido.

Contexto:
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
Genera el texto del "Desarrollo" de una ficha educativa en formato JSON válido con la siguiente estructura:

{
  "texto": "<h3>...</h3><p>...</p> ..."
}

Requisitos del contenido:
- Escrito en tono formal y descriptivo (no dirigido directamente a los estudiantes).
- Utiliza subtítulos en HTML (<h3>) para organizar las fases del desarrollo.
- Emplea párrafos (<p>) y listas (<ul>, <li>) si corresponde.
- Describe las siguientes etapas pedagógicas con contenido específico y relevante al tema:

1. <h3>Problematización:</h3>
   - ${this.aprendizajes.length > 0 ? (this.aprendizajes[0].genero.toLowerCase() === 'femenino' ? 'La docente' : 'El docente') : 'La docente'} presenta una situación o texto relacionado con el tema de aprendizaje, por ejemplo sobre la conquista del Perú, incluyendo información histórica breve y precisa.
   - Formula preguntas iniciales para análisis, y usa la frase **"Dialoguemos acerca de las respuestas"**.
   - Ejemplo: "¿Quiénes llegaron al Perú? ¿Cómo eran los incas? ¿Qué cambios ocurrieron con la llegada de los españoles?"

2. <h3>Análisis de la información:</h3>
   - Describe los contenidos de manera más detallada: eventos, personajes, lugares y conceptos históricos.
   - Incluye preguntas guía que fomenten el pensamiento crítico.
   - Añade un enlace a un video educativo pertinente al tema y grado. Ejemplo:
     "Para complementar la información, se visualiza el video educativo disponible en <a href='https://www.youtube.com/ejemplo-video'>https://www.youtube.com/ejemplo-video</a>".

3. <h3>Toma de decisiones y elaboración del producto:</h3>
   - Explica las actividades que permiten aplicar lo aprendido: dibujos, trípticos, resúmenes, esquemas.
   - Conecta estas actividades con los criterios de evaluación y competencias de la sesión.
   - Describe paso a paso cómo los estudiantes producen la evidencia de aprendizaje.

4. <h3>Socialización:</h3>
   - Describe cómo los estudiantes presentan sus productos o conclusiones en el aula.
   - Incluye interacción, intercambio de ideas y retroalimentación guiada por ${this.aprendizajes.length > 0 ? (this.aprendizajes[0].genero.toLowerCase() === 'femenino' ? 'la docente' : 'el docente') : 'la docente'}.

5. <h3>Formalización:</h3>
   - Presenta la síntesis de los aprendizajes y conclusiones finales del tema.
   - Al final, ${this.aprendizajes.length > 0 ? (this.aprendizajes[0].genero.toLowerCase() === 'femenino' ? 'la docente' : 'el docente') : 'la docente'} entrega una ficha de información o ficha de trabajo para reforzar lo aprendido.
   - Incluye preguntas de metacognición para reflexionar sobre el proceso de aprendizaje y conectar con los criterios y evidencias de la sesión.

- Todo el contenido debe generarse en **HTML listo para insertar en la vista**, usando <p>, <ul>, <li>, <strong>, <em> y <h3> donde corresponda.
- Mantener coherencia académica y descriptiva, en tercera persona.
- Basarse en el siguiente contexto de aprendizaje:
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
Eres un asistente pedagógico experto en planificación de sesiones de aprendizaje para educación básica. 
Genera el texto de la **Conclusión (Cierre)** de una sesión en formato JSON válido con esta estructura:

{
  "texto": "<p> ... texto en HTML ... </p>"
}

Requisitos del contenido:
- Escrito en tono formal y descriptivo (no dirigido directamente al estudiante).
- Presenta una metacognición guiada por ${this.aprendizajes.length > 0 ? (this.aprendizajes[0].genero.toLowerCase() === 'femenino' ? 'la docente' : 'el docente') : 'la docente'}, donde se promueve la reflexión sobre lo aprendido durante la sesión.
- Incluye **preguntas de introspección generadas automáticamente** adecuadas al grado de los estudiantes (por ejemplo: <em>¿Qué aprendieron hoy?, ¿Cómo lo lograron?, ¿Qué fue lo más interesante?, ¿Para qué servirá lo aprendido?</em>).
- Resume cómo las actividades realizadas contribuyeron al desarrollo de las competencias y criterios de evaluación.
- Utiliza párrafos en HTML (<p>) y listas (<ul>, <li>) cuando sea apropiado.
- No emplees la segunda persona directa ("tú" o "ustedes"), sino en tercera persona o impersonal ("los estudiantes reflexionan", "se invita a considerar").
- Usa un tono pedagógico, formal y coherente con el contexto del aprendizaje.
- Basarse en el siguiente contexto de la sesión:
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
