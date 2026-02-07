/**
 * Funcionalidad interactiva para Tutoriales
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('✨ Módulo Tutoriales iniciado');
    
    // Inicializar funcionalidades
    initializeSearch();
    initializeFilters();
    lazyLoadVideos();
});

/**
 * Inicializar búsqueda en tiempo real
 */
function initializeSearch() {
    const searchInput = document.querySelector('input[type="search"]');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            filterCards(term);
        });
    }
}

/**
 * Inicializar filtros de categoría
 */
function initializeFilters() {
    const categorySelect = document.querySelector('select');
    
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const category = this.value.toLowerCase();
            filterCards(null, category);
        });
    }
}

/**
 * Filtrar tarjetas por búsqueda y categoría
 */
function filterCards(searchTerm = null, category = null) {
    const cards = document.querySelectorAll('.tutorial-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const title = card.querySelector('.tutorial-title')?.textContent.toLowerCase() || '';
        const description = card.querySelector('.tutorial-description')?.textContent.toLowerCase() || '';
        const categoryText = card.querySelector('.category-badge')?.textContent.toLowerCase() || '';
        const cardCategory = card.getAttribute('data-category')?.toLowerCase() || '';
        
        let matchesSearch = true;
        let matchesCategory = true;
        
        if (searchTerm) {
            matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
        }
        
        if (category) {
            matchesCategory = cardCategory.includes(category) || categoryText.includes(category);
        }
        
        if (matchesSearch && matchesCategory) {
            card.style.display = '';
            card.style.animation = 'fadeInUp 0.4s ease-out';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    updateResultCounter(visibleCount);
    showEmptyStateIfNeeded(visibleCount);
}

/**
 * Actualizar contador de resultados
 */
function updateResultCounter(count) {
    const counter = document.querySelector('.results-counter');
    if (counter) {
        counter.textContent = `Mostrando ${count} tutorial${count !== 1 ? 's' : ''}`;
    }
}

/**
 * Mostrar estado vacío si no hay resultados
 */
function showEmptyStateIfNeeded(visibleCount) {
    const grid = document.querySelector('.tutorials-grid');
    let emptyState = grid?.querySelector('.empty-state');
    
    if (!grid) return;
    
    if (visibleCount === 0) {
        if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.className = 'empty-state';
            emptyState.innerHTML = `
                <div class="empty-icon">🎬</div>
                <h3 class="empty-title">No se encontraron tutoriales</h3>
                <p class="empty-text">Intenta ajustar los filtros de búsqueda</p>
            `;
            grid.appendChild(emptyState);
        }
        emptyState.style.display = '';
    } else if (emptyState) {
        emptyState.style.display = 'none';
    }
}

/**
 * Lazy loading de videos
 */
function lazyLoadVideos() {
    const videos = document.querySelectorAll('iframe[data-src]');
    
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const iframe = entry.target;
                    iframe.src = iframe.dataset.src;
                    iframe.removeAttribute('data-src');
                    observer.unobserve(iframe);
                }
            });
        }, {
            rootMargin: '50px'
        });
        
        videos.forEach(video => observer.observe(video));
    } else {
        // Fallback
        videos.forEach(video => {
            video.src = video.dataset.src;
        });
    }
}

/**
 * Limpiar filtros
 */
function clearFilters() {
    const searchInput = document.querySelector('input[type="search"]');
    const categorySelect = document.querySelector('select');
    
    if (searchInput) searchInput.value = '';
    if (categorySelect) categorySelect.value = '';
    
    filterCards(null, null);
}

// Exponer funciones globales
window.tutorialsModule = {
    filterCards,
    clearFilters,
    updateResultCounter
};
