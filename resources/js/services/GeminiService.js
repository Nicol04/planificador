import { Inicio } from "../models/Inicio.js";
import { Desarrollo } from "../models/Desarrollo.js";
import { Conclusion } from "../models/Conclusion.js";

export class GeminiService {
  constructor(apiKey) {
    this.apiKey = apiKey;
    this.apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";
  }

  async generar(prompt, schema = null) {
    console.log('🔵 GeminiService.generar() iniciado');
    console.log('📝 Prompt (primeros 200 chars):', prompt.substring(0, 200) + '...');
    console.log('📋 Schema:', schema ? 'Sí (JSON esperado)' : 'No');
    
    try {
      const generationConfig = {
        temperature: 0.3,
        topP: 0.8,
        topK: 10
      };

      if (schema) {
        generationConfig.responseMimeType = "application/json";
        generationConfig.responseSchema = schema;
      }

      const requestBody = {
        contents: [
          {
            parts: [
              { text: prompt }
            ]
          }
        ],
        generationConfig
      };

      console.log('📤 Enviando petición a Gemini API...');

      const response = await fetch(`${this.apiUrl}?key=${this.apiKey}`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(requestBody)
      });

      console.log('📥 Respuesta recibida, status:', response.status);

      if (!response.ok) {
        const errorText = await response.text();
        console.error('❌ Error en respuesta:', errorText);
        throw new Error(`Error en la llamada a Gemini: ${response.status}`);
      }

      const data = await response.json();
      console.log('✅ Datos JSON parseados correctamente');

      // Extraer el texto de la respuesta
      const texto = data?.candidates?.[0]?.content?.parts?.[0]?.text ?? "Error al generar contenido.";
      console.log('📄 Texto extraído (primeros 200 chars):', texto.substring(0, 200) + '...');

      // Si se solicitó un schema, intentamos devolver un objeto parseado.
      if (schema) {
        // Intentos tolerantes de parseo:
        // 1) JSON.parse directo
        // 2) Si resulta en una string que contiene JSON, intentar parsear de nuevo
        // 3) Extraer la primera substring que parezca JSON entre la primera '{' y la última '}'
        try {
          let parsed = JSON.parse(texto);
          if (typeof parsed === 'string') {
            // A veces la API retorna una cadena que a su vez contiene JSON
            try {
              parsed = JSON.parse(parsed);
            } catch (_) {
              // dejar parsed como la cadena
            }
          }

          if (parsed && typeof parsed === 'object') return parsed;
        } catch (e) {
          // continuar a intento de extracción
        }

        // Intento: extraer substring JSON
        const firstBrace = texto.indexOf('{');
        const lastBrace = texto.lastIndexOf('}');
        if (firstBrace !== -1 && lastBrace !== -1 && lastBrace > firstBrace) {
          const candidate = texto.slice(firstBrace, lastBrace + 1);
          try {
            const parsed2 = JSON.parse(candidate);
            if (parsed2 && typeof parsed2 === 'object') return parsed2;
          } catch (e) {
            // si falla, seguiremos al fallback
          }
        }

        // Fallback: devolver el texto crudo para que el llamador lo muestre
        return { texto };
      }

      return { texto };
    } catch (error) {
      console.error(error);
      return { texto: "Error al generar contenido." };
    }
  }

  /**
   * Genera inicio, desarrollo y conclusion a partir de una descripción.
   * - description: string u objeto que describe la unidad/tema.
   * Retorna: { inicio: Inicio, desarrollo: Desarrollo, conclusion: Conclusion, raw }
   */
  async generarTodo(description = "") {
    // Normalizar descripción a texto
    const descripcionTexto = typeof description === 'string' ? description : JSON.stringify(description, null, 2);

    const prompt = `Eres un asistente experto en diseño de unidades de aprendizaje. Dada una descripción del tema o unidad, genera UNIFICADAMENTE el texto completo dividido en tres partes: inicio, desarrollo y conclusion. Responde ÚNICAMENTE con un JSON válido (sin texto adicional) con esta estructura exacta:
{
  "inicio": { "texto": "<texto de inicio: contexto, propósito, conexión con objetivos y enganche inicial>" },
  "desarrollo": { "texto": "<texto de desarrollo: actividades, pasos, contenidos, recursos y sugerencias metodológicas>" },
  "conclusion": { "texto": "<texto de cierre: síntesis, indicadores de logro, evidencias y recomendaciones finales>" }
}

Genera textos claros y en español, de extensión moderada (2-6 párrafos por sección si procede). No incluyas listas de metadatos ni explicaciones fuera del JSON. Usa la siguiente descripción para orientar la generación:
${descripcionTexto}`;

    // Llamar al generador general
    const result = await this.generar(prompt);

    // Intentar obtener un objeto parseado: la función generar ya intenta algunas estrategias cuando se pasa schema,
    // pero aquí manejamos el caso donde devuelve texto.
    let parsed = null;
    if (result && typeof result === 'object' && (result.inicio || result.desarrollo || result.conclusion)) {
      parsed = result;
    } else {
      const texto = result?.texto ?? '';
      try {
        parsed = JSON.parse(texto);
      } catch (e) {
        // intentar extracción de substring JSON
        const first = texto.indexOf('{');
        const last = texto.lastIndexOf('}');
        if (first !== -1 && last !== -1 && last > first) {
          try {
            parsed = JSON.parse(texto.slice(first, last + 1));
          } catch (e2) {
            parsed = null;
          }
        }
      }
    }

    const inicio = new Inicio();
    const desarrollo = new Desarrollo();
    const conclusion = new Conclusion();

    if (parsed) {
      if (parsed.inicio) inicio.fromJson(parsed.inicio);
      if (parsed.desarrollo) desarrollo.fromJson(parsed.desarrollo);
      // aceptar tanto 'conclusion' como 'final' por compatibilidad
      if (parsed.conclusion) conclusion.fromJson(parsed.conclusion);
      else if (parsed.final) conclusion.fromJson(parsed.final);
    } else {
      // Fallback: si no hay JSON, colocar todo el texto en 'desarrollo' para que el llamador lo revise.
      desarrollo.fromJson({ texto: result?.texto ?? '' });
    }

    return { inicio, desarrollo, conclusion, raw: parsed ?? { texto: result?.texto ?? '' } };
  }
}
