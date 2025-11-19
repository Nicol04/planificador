class GeminiService {
    constructor(apiKey, config = {}) {
        if (!apiKey) {
            throw new Error('API Key is required');
        }

        this.apiKey = apiKey;
        this.baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
        this.model = config.model || 'gemini-2.5-flash';

        // Configuración por defecto
        this.defaultConfig = {
            temperature: config.temperature !== undefined ? config.temperature : 1.0,
            topP: config.topP !== undefined ? config.topP : 0.8,
            topK: config.topK !== undefined ? config.topK : 10,
            stopSequences: config.stopSequences || [],
            maxOutputTokens: config.maxOutputTokens || undefined,
            responseMimeType: config.responseMimeType || 'application/json',
            responseSchema: config.responseSchema || undefined
        };
        
        // System instruction separada
        this.systemInstruction = config.systemInstruction || undefined;
    }

    /**
     * Genera contenido usando la API de Gemini
     * @param {string} prompt - El texto del prompt
     * @param {Object} options - Opciones adicionales para la generación
     * @returns {Promise<Object>} - El JSON de respuesta
     */
    async generateContent(prompt, options = {}) {
        try {
            const generationConfig = {
                ...this.defaultConfig,
                ...options
            };

            // Limpiar valores undefined
            Object.keys(generationConfig).forEach(key => {
                if (generationConfig[key] === undefined) {
                    delete generationConfig[key];
                }
            });

            // Establecer mime type por defecto si no se especifica
            if (!generationConfig.responseMimeType) {
                generationConfig.responseMimeType = 'text/plain';
            }

            // Valida que el mime type esté permitido por la API
            const allowedMimeTypes = ['text/plain', 'application/json', 'text/html'];
            if (!allowedMimeTypes.includes(generationConfig.responseMimeType)) {
                console.warn(`Using non-standard mime type: ${generationConfig.responseMimeType}. Falling back to text/plain`);
                generationConfig.responseMimeType = 'text/plain';
            }

            // Mapear a los nombres que espera la API (snake_case)
            const apiGenerationConfig = {};
            if (generationConfig.temperature !== undefined) apiGenerationConfig.temperature = generationConfig.temperature;
            if (generationConfig.topP !== undefined) apiGenerationConfig.top_p = generationConfig.topP;
            if (generationConfig.topK !== undefined) apiGenerationConfig.top_k = generationConfig.topK;
            if (generationConfig.stopSequences !== undefined) apiGenerationConfig.stop_sequences = generationConfig.stopSequences;
            if (generationConfig.maxOutputTokens !== undefined) apiGenerationConfig.max_output_tokens = generationConfig.maxOutputTokens;
            if (generationConfig.responseMimeType !== undefined) apiGenerationConfig.response_mime_type = generationConfig.responseMimeType;
            if (generationConfig.responseSchema !== undefined) apiGenerationConfig.response_schema = generationConfig.responseSchema;

            const requestBody = {
                contents: [
                    {
                        parts: [
                            {
                                text: prompt
                            }
                        ]
                    }
                ],
                generation_config: apiGenerationConfig
            };
            
            // Agregar system_instruction si existe
            if (this.systemInstruction || options.systemInstruction) {
                const instruction = options.systemInstruction || this.systemInstruction;
                requestBody.system_instruction = {
                    parts: [
                        {
                            text: instruction
                        }
                    ]
                };
            }

            const url = `${this.baseUrl}/${this.model}:generateContent`;

            const response = await axios.post(url, requestBody, {
                headers: {
                    'x-goog-api-key': this.apiKey,
                    'Content-Type': 'application/json'
                }
            });

            const data = response.data;

            // Extraer el texto de la respuesta
            if (data.candidates && data.candidates.length > 0) {
                const textContent = data.candidates[0].content.parts[0].text;

                // Si la respuesta es JSON, parsearlo
                if (generationConfig.responseMimeType === 'application/json') {
                    try {
                        return JSON.parse(textContent);
                    } catch (e) {
                        console.warn('Failed to parse JSON response, returning raw text');
                        return { text: textContent };
                    }
                }

                return { text: textContent };
            }

            throw new Error('No candidates in response');

        } catch (error) {
            // Manejo de errores de axios
            if (error.response) {
                // Error de respuesta del servidor
                throw new Error(`Gemini API Error: ${error.response.status} - ${JSON.stringify(error.response.data)}`);
            } else if (error.request) {
                // Error de red
                throw new Error('Network error: No response received from Gemini API');
            } else {
                // Otro tipo de error
                console.error('Error generating content:', error);
                throw error;
            }
        }
    }

    /**
     * Actualiza la configuración del servicio
     * @param {Object} config - Nueva configuración
     */
    updateConfig(config) {
        this.defaultConfig = {
            ...this.defaultConfig,
            ...config
        };
    }

    /**
     * Establece el modelo a usar
     * @param {string} model - Nombre del modelo (ej: 'gemini-2.5-flash', 'gemini-pro')
     */
    setModel(model) {
        this.model = model;
    }

    /**
     * Establece la temperatura
     * @param {number} temperature - Valor entre 0 y 2
     */
    setTemperature(temperature) {
        this.defaultConfig.temperature = temperature;
    }

    /**
     * Establece el formato de respuesta
     * @param {string} mimeType - Tipo MIME (ej: 'application/json', 'text/plain')
     * @param {Object} schema - Esquema JSON opcional para estructurar la respuesta
     */
    setResponseFormat(mimeType, schema = undefined) {
        this.defaultConfig.responseMimeType = mimeType;
        this.defaultConfig.responseSchema = schema;
    }

    /**
     * Establece las instrucciones del sistema
     * @param {string} instruction - Instrucciones del sistema para el comportamiento del modelo
     */
    setSystemInstruction(instruction) {
        this.systemInstruction = instruction;
    }

    /**
     * Obtiene la configuración actual
     * @returns {Object} - Configuración actual
     */
    getConfig() {
        return { ...this.defaultConfig };
    }
}

// Exportar como módulo ES6
export default GeminiService;
