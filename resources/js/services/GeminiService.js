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

   /* const prompt = `Eres un asistente experto en diseño de sesiones de aprendizaje. Dada una descripción del tema y los datos de la sesión, genera UNIFICADAMENTE el texto completo dividido en tres partes: inicio, desarrollo y conclusion. Responde ÚNICAMENTE con un JSON válido (sin texto adicional) con esta estructura exacta:
{
  "inicio": { "texto": "<texto de inicio: contexto, propósito, conexión con objetivos y enganche inicial>" },
  "desarrollo": { "texto": "<texto de desarrollo: actividades, pasos, contenidos, recursos y sugerencias metodológicas>" },
  "conclusion": { "texto": "<texto de cierre: síntesis, indicadores de logro, evidencias y recomendaciones finales>" }
}

Genera textos claros y en español, de extensión moderada (2-6 párrafos por sección si procede). No incluyas listas de metadatos ni explicaciones fuera del JSON. Usa la siguiente descripción para orientar la generación: */

const prompt = `
Eres un asistente pedagógico experto en diseño y redacción técnica de sesiones de aprendizaje.

Tu tarea es generar el contenido completo de una ficha pedagógica compuesta por tres secciones: **inicio**, **desarrollo** y **conclusión**, en un lenguaje formal, descriptivo y en **tercera persona**, tal como se redacta en los documentos de planificación docente (no dirigido a los estudiantes).

⚠️ Indicaciones clave:
- No uses frases como "Estimado docente", "niños y niñas", "exploradores", "ustedes" o "vamos a...".
- Utiliza siempre expresiones formales como "La docente presenta...", "El docente orienta...", "Se da a conocer...", "Se promueve que los estudiantes...".
- Los textos deben describir lo que **ocurre en cada momento** de la sesión, no lo que se dice directamente a los estudiantes.
- No incluyas comentarios ni explicaciones fuera del JSON.
- Responde únicamente con un JSON válido con esta estructura exacta:

{
  "inicio": { "texto": "<HTML formal del inicio>" },
  "desarrollo": { "texto": "<HTML formal del desarrollo>" },
  "conclusion": { "texto": "<HTML formal de la conclusión>" }
}

📋 Criterios para cada momento:

🟢 **Inicio:**
Describe el saludo, la oración de la mañana (mencionando el lema "Siempre bendecidos y listos para aprender"), la motivación inicial, la activación de saberes previos mediante preguntas o dinámicas, la comunicación del propósito, los criterios de evaluación y los acuerdos de convivencia.

🟡 **Desarrollo:**
Incluye las siguientes etapas:
- **Problematización:** se plantea una situación o texto para analizar; se formulan preguntas iniciales que invitan al diálogo ("Dialoguemos acerca de las respuestas").
- **Análisis de la información:** se plantea una pregunta central de investigación o comprensión; puede incluir un enlace a un video educativo relacionado con el tema y grado.
- **Toma de decisiones o elaboración:** se indica cómo los estudiantes aplican lo aprendido o elaboran productos/evidencias según los criterios definidos.
- **Socialización:** se describe cómo los estudiantes comparten sus productos en el aula, promoviendo el trabajo colaborativo.
- **Formalización:** el docente explica y consolida los conocimientos principales.
- **Metacognición:** se incluyen preguntas de reflexión sobre el proceso de aprendizaje (por ejemplo: <em>¿Qué aprendieron hoy?, ¿Cómo lo lograron?, ¿Qué fue lo más interesante?, ¿Para qué les servirá?</em>).
- Cierre con la entrega de una ficha informativa o ficha de trabajo para reforzar lo aprendido.

🔵 **Conclusión:**
Resume el cierre de la sesión destacando la reflexión y metacognición guiada por el o la docente, la síntesis de los aprendizajes y cómo se evidencian los logros respecto a las competencias y criterios evaluados. 
Debe incluir preguntas de metacognición adecuadas al grado y tono formal, así como una retroalimentación general.

📄 El contenido de cada campo "texto" debe estar en formato HTML (usa <p>, <ul>, <li>, <strong>, <em>), listo para insertarse en una vista.

Usa el siguiente contexto de aprendizaje para personalizar el contenido según el tema, propósito, evidencias, competencias, capacidades y grado:

${descripcionTexto}
`;

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
