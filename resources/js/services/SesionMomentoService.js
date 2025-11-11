// SesionMomentoService.js
// Servicio para operaciones AJAX relacionadas con SesionMomento

/**
 * Detecta si la URL actual es una vista de edición y extrae el id de la sesión si existe.
 */
export function getSesionIdFromEditUrl() {
    const match = window.location.pathname.match(/sesions\/(\d+)\/edit/);
    return match ? match[1] : null;
}

export const SesionMomentoService = {
    /**
     * Guarda los valores de los momentos en la sesión vía POST
     */
    saveMomentos: async (inicio, desarrollo, cierre) => {
        try {
            const response = await fetch('/sesion-momento/session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ inicio, desarrollo, cierre })
            });
            return await response.json();
        } catch (error) {
            console.error('❌ Error al guardar momentos en sesión:', error);
            throw error;
        }
    },

    /**
     * Obtiene los valores de los momentos de la sesión vía GET usando el id en la URL
     */
    getMomentosById: async (sesionId) => {
        try {
            const response = await fetch(`/sesion-momento/${sesionId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            return await response.json();
        } catch (error) {
            console.error('❌ Error al obtener momentos de sesión por id:', error);
            throw error;
        }
    }
};
