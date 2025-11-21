export const API_KEY = window.userGeminiKey ?? null;

export const CX = "973b1193b135e4de3";

export async function searchImages(query) {
  if (!query) return [];
  if (!API_KEY) {
    console.error("❌ No se encontró API_KEY para búsqueda de imágenes");
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
