import { searchImages } from './apiClient.js';

// Caché para almacenar resultados de búsqueda
const imageCache = new Map();

/**
 * Busca imágenes según un query y devuelve un array de URLs
 * @param {string} query - Término de búsqueda
 * @returns {Promise<string[]>} Array de URLs de imágenes
 */
export async function buscarImagenes(query) {
  try {
    if (!query || query.trim() === '') {
      return [];
    }

    const items = await searchImages(query);
    
    // Extrae solo las URLs de las imágenes
    return items.map(item => item.link).filter(link => link);
  } catch (error) {
    console.error('Error al buscar imágenes:', error);
    return [];
  }
}

/**
 * Obtiene la primera imagen de los resultados de búsqueda
 * @param {string} query - Término de búsqueda
 * @returns {Promise<string>} URL de la primera imagen encontrada o una imagen placeholder
 */
export async function getFirstImage(query) {
  try {
    if (!query || query.trim() === '') {
      console.warn('[SearchImage] Query vacío, usando placeholder');
      return 'https://via.placeholder.com/300x200?text=Sin+imagen';
    }

    console.log(`🔍 [SearchImage] Buscando primera imagen para: "${query}"`);
    
    // Verificar si ya está en caché
    if (imageCache.has(query)) {
      console.log(`✓ [SearchImage] Usando resultado cacheado para "${query}"`);
      const cachedItems = imageCache.get(query);
      if (cachedItems && cachedItems.length > 0) {
        return cachedItems[0].link;
      }
    }
    
    // Espera de 500ms para no saturar el servidor
    await new Promise(resolve => setTimeout(resolve, 500));
    
    const items = await searchImages(query);
    
    // Guardar en caché
    if (items && items.length > 0) {
      console.log(`💾 [SearchImage] Guardando ${items.length} resultados en caché para "${query}"`);
      imageCache.set(query, items);
    }
    
    if (items && items.length > 0 && items[0].link) {
      console.log(`✓ [SearchImage] Imagen encontrada para "${query}":`, items[0].link.substring(0, 50) + '...');
      return items[0].link;
    }
    
    console.warn(`⚠️ [SearchImage] No se encontraron imágenes para "${query}", usando placeholder`);
    return 'https://via.placeholder.com/300x200?text=' + encodeURIComponent(query);
  } catch (error) {
    console.error(`❌ [SearchImage] Error buscando imagen para "${query}":`, error);
    return 'https://via.placeholder.com/300x200?text=Error';
  }
}

/**
 * Obtiene imágenes cacheadas para un query
 * @param {string} query - Término de búsqueda
 * @returns {Array|null} Array de items cacheados o null si no existe
 */
export function getCachedImages(query) {
  if (imageCache.has(query)) {
    console.log(`✓ [SearchImage] Recuperando ${imageCache.get(query).length} imágenes cacheadas para "${query}"`);
    return imageCache.get(query);
  }
  return null;
}

/**
 * Limpia el caché de imágenes
 */
export function clearImageCache() {
  console.log('🗑️ [SearchImage] Limpiando caché de imágenes');
  imageCache.clear();
}
