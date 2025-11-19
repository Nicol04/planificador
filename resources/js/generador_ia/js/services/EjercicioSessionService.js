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
   * @param {string} descripcionFicha - Opcional: descripción de la ficha (solo se envía en el primer ejercicio)
   */
  async store(tipo, contenido, descripcionFicha = null) {
    try {
      console.log(`💾 [EjercicioSession] Guardando ejercicio tipo "${tipo}"`);
      
      const payload = { tipo, contenido };
      
      // Incluir descripción si está disponible
      if (descripcionFicha) {
        payload.descripcion_ficha = descripcionFicha;
        console.log(`📝 [EjercicioSession] Incluye descripción de ficha`);
      }

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
      const response = await fetch(`${this.baseUrl}/${id}`, {
        method: 'PUT',
        headers: this.getHeaders(),
        body: JSON.stringify({ tipo, contenido })
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
      const response = await fetch(`${this.baseUrl}/${id}/content`, {
        method: 'PATCH',
        headers: this.getHeaders(),
        body: JSON.stringify({ path, value })
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
      return data;
    } catch (error) {
      console.error('❌ [EjercicioSession] Error al limpiar ejercicios:', error);
      throw error;
    }
  }

  /**
   * Reemplazar todos los ejercicios en sesión
   * Útil cuando Gemini genera todos los ejercicios de una vez
   */
  async replaceAll(ejercicios) {
    try {
      console.log(`📦 [EjercicioSession] Reemplazando todos los ejercicios (${ejercicios.length} items)`);
      const response = await fetch(`${this.baseUrl}/replace-all`, {
        method: 'POST',
        headers: this.getHeaders(),
        body: JSON.stringify({ ejercicios })
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
