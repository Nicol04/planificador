export class GeminiService {
  constructor(apiKey) {
    this.apiKey = apiKey;
    this.apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";
  }

  async generar(prompt, schema = null) {
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

      const response = await fetch(`${this.apiUrl}?key=${this.apiKey}`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          contents: [
            {
              parts: [
                { text: prompt }
              ]
            }
          ],
          generationConfig
        })
      });

      if (!response.ok) throw new Error("Error en la llamada a Gemini");

      const data = await response.json();

      // Extraer el texto de la respuesta
      const texto = data?.candidates?.[0]?.content?.parts?.[0]?.text ?? "Error al generar contenido.";

      // Si es JSON estructurado, parsear
      if (schema) {
        try {
          return JSON.parse(texto);
        } catch (e) {
          return { texto: "Error al parsear JSON." };
        }
      }

      return { texto };
    } catch (error) {
      console.error(error);
      return { texto: "Error al generar contenido." };
    }
  }
}
