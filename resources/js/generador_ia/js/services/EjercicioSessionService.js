/**
 * Servicio para gestionar ejercicios en sesión de Laravel
 * Proporciona métodos para CRUD de ejercicios sin persistencia en BD
 */

export class EjercicioSessionService {
  constructor() {
    this.baseUrl = '/session/ejercicios';
    this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  /**
   * Headers por defecto para peticiones fetch
   */
  getHeaders() {
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': this.csrfToken,
      'X-Requested-With': 'XMLHttpRequest'
    };
  }

  /**
   * Obtener todos los ejercicios almacenados en sesión
   */
  async getAll() {
    try {
      console.log('📥 [EjercicioSession] Obteniendo todos los ejercicios de sesión');
      const response = await fetch(this.baseUrl, {
        method: 'GET',
        headers: this.getHeaders()
      });
      const data = await response.json();
      console.log(`✓ [EjercicioSession] ${data.count} ejercicios obtenidos`);
      return data;
    } catch (error) {
      console.error('❌ [EjercicioSession] Error al obtener ejercicios:', error);
      throw error;
    }
  }


  /**
 * Guardar un nuevo ejercicio en sesión
 * @param {string} tipo - Tipo de ejercicio: SelectionExercise, ClassificationExercise, ClozeExercise, ReflectionExercise
 * @param {object} contenido - Objeto JSON con el contenido del ejercicio
 * @param {string} descripcion - Opcional: descripción de la ficha (solo se envía en el primer ejercicio)
 * @param {string} nombre - Opcional: título de la ficha
 * @param {string} grado - Opcional: grado de la ficha
 * @param {string} tipo_ejercicio - Opcional: tipo de ejercicio de la ficha
 */
async store(tipo, contenido, descripcion = null, nombre = null, grado = null, tipo_ejercicio = null) {
  try {
    console.log(`💾 [EjercicioSession] Guardando ejercicio tipo "${tipo}"`);
    
    // Payload base que el backend exige
    const payload = { tipo, contenido };

    // 👉 DESCRIPCIÓN (backend espera "descripcion", NO "descripcion_ficha")
    if (descripcion) {
      payload.descripcion = descripcion;
      console.log(`📝 [EjercicioSession] Incluye descripción:`, descripcion);
    }

    if (nombre) {
      payload.nombre = nombre;
      console.log(`📝 [EjercicioSession] Incluye nombre (título):`, nombre);
    }

    // 👉 GRADO
    if (grado) {
      payload.grado = grado;
      console.log(`📝 [EjercicioSession] Incluye grado:`, grado);
    }

    // 👉 TIPO DE EJERCICIO
    if (tipo_ejercicio) {
      payload.tipo_ejercicio = tipo_ejercicio;
      console.log(`📝 [EjercicioSession] Incluye tipo_ejercicio:`, tipo_ejercicio);
    }

    console.log('[EjercicioSession] JSON enviado:', JSON.stringify(payload, null, 2));

    const response = await fetch(this.baseUrl, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      const errorData = await response.json();
      console.error('❌ [EjercicioSession] Error del servidor:', errorData);
      throw new Error(`Error ${response.status}: ${JSON.stringify(errorData)}`);
    }

    const data = await response.json();
    console.log(`✓ [EjercicioSession] Ejercicio guardado con ID: ${data.data.id}`);
    return data;

  } catch (error) {
    console.error('❌ [EjercicioSession] Error al guardar ejercicio:', error);
    throw error;
  }
}


  /**
   * Obtener un ejercicio específico por ID
   */
  async getById(id) {
    try {
      console.log(`📥 [EjercicioSession] Obteniendo ejercicio ID: ${id}`);
      const response = await fetch(`${this.baseUrl}/${id}`, {
        method: 'GET',
        headers: this.getHeaders()
      });
      const data = await response.json();
      console.log(`✓ [EjercicioSession] Ejercicio obtenido`);
      return data;
    } catch (error) {
      console.error(`❌ [EjercicioSession] Error al obtener ejercicio ${id}:`, error);
      throw error;
    }
  }

  /**
   * Actualizar un ejercicio completo
   */
  async update(id, tipo, contenido) {
    try {
      console.log(`🔄 [EjercicioSession] Actualizando ejercicio ID: ${id}`);
      const payload = { tipo, contenido };
      // Imprimir el JSON total enviado
      console.log('[EjercicioSession] JSON enviado:', JSON.stringify(payload, null, 2));
      console.log(`[LOG][update] Valor enviado a sesión:`, payload);
      const response = await fetch(`${this.baseUrl}/${id}`, {
        method: 'PUT',
        headers: this.getHeaders(),
        body: JSON.stringify(payload)
      });
      const data = await response.json();
      console.log(`✓ [EjercicioSession] Ejercicio actualizado`);
      return data;
    } catch (error) {
      console.error(`❌ [EjercicioSession] Error al actualizar ejercicio ${id}:`, error);
      throw error;
    }
  }

  /**
   * Actualizar contenido parcial de un ejercicio (ideal para cambios de imagen o texto)
   * @param {string} id - ID del ejercicio
   * @param {string} path - Ruta en notación de puntos: "title", "options.0.imageSrc", "items.2.text"
   * @param {any} value - Nuevo valor
   */
  async updateContent(id, path, value) {
    try {
      console.log(`✏️ [EjercicioSession] Actualizando contenido: ${path} = ${value}`);
      const payload = { path, value };
      // Imprimir el JSON total enviado
      console.log('[EjercicioSession] JSON enviado:', JSON.stringify(payload, null, 2));
      console.log(`[LOG][updateContent] Valor enviado a sesión:`, payload);
      const response = await fetch(`${this.baseUrl}/${id}/content`, {
        method: 'PATCH',
        headers: this.getHeaders(),
        body: JSON.stringify(payload)
      });
      const data = await response.json();
      console.log(`✓ [EjercicioSession] Contenido actualizado`);
      return data;
    } catch (error) {
      console.error(`❌ [EjercicioSession] Error al actualizar contenido de ${id}:`, error);
      throw error;
    }
  }

  /**
   * Eliminar un ejercicio de sesión
   */
  async delete(id) {
    try {
      console.log(`🗑️ [EjercicioSession] Eliminando ejercicio ID: ${id}`);
      const response = await fetch(`${this.baseUrl}/${id}`, {
        method: 'DELETE',
        headers: this.getHeaders()
      });
      const data = await response.json();
      console.log(`✓ [EjercicioSession] Ejercicio eliminado`);
      console.log(`[LOG][delete] Ejercicio eliminado:`, data);
      return data;
    } catch (error) {
      console.error(`❌ [EjercicioSession] Error al eliminar ejercicio ${id}:`, error);
      throw error;
    }
  }

  /**
   * Limpiar todos los ejercicios de sesión
   */
  async clear() {
    try {
      console.log('🧹 [EjercicioSession] Limpiando todos los ejercicios');
      const response = await fetch(this.baseUrl, {
        method: 'DELETE',
        headers: this.getHeaders()
      });
      const data = await response.json();
      console.log('✓ [EjercicioSession] Todos los ejercicios eliminados');
      console.log(`[LOG][clear] Sesión limpiada:`, data);
      return data;
    } catch (error) {
      console.error('❌ [EjercicioSession] Error al limpiar ejercicios:', error);
      throw error;
    }
  }

  /**
   * Reemplazar todos los ejercicios en sesión
   * Útil cuando Gemini genera todos los ejercicios de una vez
   * @param {array} ejercicios - Array de ejercicios a reemplazar
   * @param {string} fichaNombre - Opcional: nombre de la ficha
   * @param {string} fichaDescripcion - Opcional: descripción de la ficha
   * @param {string} grado - Opcional: grado de la ficha
   * @param {string} tipo_ejercicio - Opcional: tipo de ejercicio de la ficha
   */
  async replaceAll(ejercicios, fichaNombre = null, fichaDescripcion = null, grado = null, tipo_ejercicio = null) {
    try {
      console.log(`📦 [EjercicioSession] Reemplazando todos los ejercicios (${ejercicios.length} items)`);
      const payload = { ejercicios };
      // Incluir metadatos opcionales de la ficha
      if (fichaNombre) {
        payload.ficha_nombre = fichaNombre;
        console.log(`📝 [EjercicioSession] Incluye nombre de ficha:`, fichaNombre);
      }
      if (fichaDescripcion) {
        payload.ficha_descripcion = fichaDescripcion;
        console.log(`📝 [EjercicioSession] Incluye descripción de ficha:`, fichaDescripcion);
      }
      if (grado) {
        payload.grado = grado;
        console.log(`📝 [EjercicioSession] Incluye grado de ficha:`, grado);
      }
      if (tipo_ejercicio) {
        payload.tipo_ejercicio = tipo_ejercicio;
        console.log(`📝 [EjercicioSession] Incluye tipo_ejercicio de ficha:`, tipo_ejercicio);
      }
      // Imprimir el JSON total enviado
      console.log('[EjercicioSession] JSON enviado:', JSON.stringify(payload, null, 2));
      console.log(`[LOG][replaceAll] Valor enviado a sesión:`, payload);
      const response = await fetch(`${this.baseUrl}/replace-all`, {
        method: 'POST',
        headers: this.getHeaders(),
        body: JSON.stringify(payload)
      });
      const data = await response.json();
      console.log(`✓ [EjercicioSession] ${data.count} ejercicios reemplazados`);
      return data;
    } catch (error) {
      console.error('❌ [EjercicioSession] Error al reemplazar ejercicios:', error);
      throw error;
    }
  }
}

export default EjercicioSessionService;
