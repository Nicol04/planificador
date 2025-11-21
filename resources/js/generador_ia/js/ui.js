export function showLoading(container) {
  console.log('🔄 [UI] Mostrando estado de carga');
  container.textContent = "Cargando...";
}

export function showError(container, message = "Error al buscar imágenes.") {
  console.error('❌ [UI] Error:', message);
  container.textContent = message;
}

export function renderResults(container, items) {
  console.log(`🖼️ [UI] Renderizando ${items?.length || 0} resultados`);
  container.innerHTML = "";
  if (!items || items.length === 0) {
    container.textContent = "No se encontraron resultados.";
    return;
  }

  items.forEach((item, idx) => {
    const img = document.createElement('img');
    img.src = item.link;
    img.alt = item.title || '';
    img.className = 'cursor-pointer hover:opacity-80 transition w-20 h-20 object-cover border-2 border-gray-300 rounded hover:border-blue-500';
    img.title = 'Click para seleccionar';
    container.appendChild(img);
  });
}

export function openModal(query, callback) {
  console.log(`🔍 [Modal] Abriendo modal para búsqueda: "${query}"`);
  window.modalCallback = callback;
  const modal = document.getElementById('imageModal');
  const searchInput = document.getElementById('modalSearchQuery');
  // Si estamos en modo edición, el input debe estar vacío
  if (window.location.pathname.match(/\/docente\/ficha-aprendizajes\/[0-9]+\/edit/)) {
    searchInput.value = '';
  } else {
    searchInput.value = query;
  }
  modal.classList.remove('hidden');
}
