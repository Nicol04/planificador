export const API_KEY = window.userSearchApiKey || null;
export const CX = window.userIdSearch || null;

if (!API_KEY || !CX) {
    console.error("❌ No se encontraron las claves necesarias para la búsqueda de imágenes.");
}

export async function searchImages(query) {
    if (!query) return [];
    if (!API_KEY || !CX) {
        console.error("❌ No se puede realizar la búsqueda: faltan claves de API o ID de búsqueda.");
        return [];
    }

    const url = `https://www.googleapis.com/customsearch/v1?q=${encodeURIComponent(query)}&cx=${CX}&key=${API_KEY}&searchType=image`;
    const res = await fetch(url);
    if (!res.ok) {
        const text = await res.text();
        throw new Error(`API error: ${res.status} ${res.statusText} - ${text}`);
    }
    const data = await res.json();
    return data.items || [];
}
