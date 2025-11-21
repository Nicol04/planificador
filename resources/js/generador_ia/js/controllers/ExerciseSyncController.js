/**
 * Controlador de Sincronización de Ejercicios
 * Maneja la sincronización entre ejercicios de BD y sesión de Laravel
 */
export default class ExerciseSyncController {
	constructor(ejercicioSessionService) {
		this.ejercicioSessionService = ejercicioSessionService;
		this.ejercicioMap = new Map(); // Map<bdId, sessionId>
	}

	/**
	 * Sincronizar ejercicios cargados desde BD con la sesión
	 * @param {Array} ejerciciosBD - Ejercicios desde la base de datos
	 * @returns {Promise<Map>} - Map de IDs BD -> IDs sesión
	 */
	async syncFromDatabase(ejerciciosBD) {
		console.log(`🔄 [ExerciseSyncController] Sincronizando ${ejerciciosBD.length} ejercicios desde BD...`);
		
		// Limpiar sesión antes de sincronizar
		await this.ejercicioSessionService.clear();
		
		this.ejercicioMap.clear();

		for (const ejercicioBD of ejerciciosBD) {
			try {
				// Guardar en sesión
				const response = await this.ejercicioSessionService.store(
					ejercicioBD.tipo,
					ejercicioBD.contenido,
					null, // descripcion_ficha (no necesaria aquí)
					null  // nombre (no necesario aquí)
				);

				// Mapear ID de BD con ID de sesión
				this.ejercicioMap.set(ejercicioBD.id, response.data.id);
				
				console.log(`✓ [ExerciseSyncController] Ejercicio BD ${ejercicioBD.id} -> Sesión ${response.data.id}`);
			} catch (error) {
				console.error(`❌ [ExerciseSyncController] Error sincronizando ejercicio ${ejercicioBD.id}:`, error);
			}
		}

		console.log(`✅ [ExerciseSyncController] ${this.ejercicioMap.size} ejercicios sincronizados`);
		return this.ejercicioMap;
	}

	/**
	 * Obtener sessionId a partir de un ID de BD
	 * @param {number|string} bdId - ID del ejercicio en BD
	 * @returns {string|null} - sessionId o null si no existe
	 */
	getSessionId(bdId) {
		return this.ejercicioMap.get(bdId) || null;
	}

	/**
	 * Obtener BD ID a partir de un sessionId
	 * @param {string} sessionId - ID del ejercicio en sesión
	 * @returns {number|string|null} - BD ID o null si no existe
	 */
	getBdId(sessionId) {
		for (const [bdId, sessId] of this.ejercicioMap.entries()) {
			if (sessId === sessionId) {
				return bdId;
			}
		}
		return null;
	}

	/**
	 * Verificar si un ejercicio está sincronizado
	 * @param {number|string} bdId - ID del ejercicio en BD
	 * @returns {boolean}
	 */
	isSynced(bdId) {
		return this.ejercicioMap.has(bdId);
	}

	/**
	 * Limpiar el mapa de sincronización
	 */
	clear() {
		this.ejercicioMap.clear();
		console.log('🧹 [ExerciseSyncController] Mapa de sincronización limpiado');
	}

	/**
	 * Obtener estadísticas de sincronización
	 * @returns {Object}
	 */
	getStats() {
		return {
			totalSynced: this.ejercicioMap.size,
			mappings: Array.from(this.ejercicioMap.entries()).map(([bdId, sessionId]) => ({
				bdId,
				sessionId
			}))
		};
	}
}
