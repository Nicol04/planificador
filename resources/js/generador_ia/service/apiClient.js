// ESTOS DOS VALORES AÑADEMOS A TU .ENV PARA QUE NO ESTÉN EN EL CÓDIGO FUENTE

export const API_KEY = "AIzaSyBtow2Dzgpcuuko3cSVCh4L2A5s8j32r9Y";
export const CX = "973b1193b135e4de3";


export async function searchImages(query) {
  if (!query) return [];
  const url = `https://www.googleapis.com/customsearch/v1?q=${encodeURIComponent(query)}&cx=${CX}&key=${API_KEY}&searchType=image`;
  const res = await fetch(url);
  if (!res.ok) {
    const text = await res.text();
    throw new Error(`API error: ${res.status} ${res.statusText} - ${text}`);
  }
  const data = await res.json();
  return data.items || [];
}
