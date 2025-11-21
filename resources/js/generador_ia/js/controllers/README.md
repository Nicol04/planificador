# Arquitectura de Controladores - Generador IA

## 📋 Descripción

Este documento describe la arquitectura modular implementada para separar la lógica del modal de imágenes y otros componentes en controladores independientes.

## 🏗️ Estructura de Controladores

### **AppController** (`controllers/AppController.js`)
Controlador principal que coordina todos los demás controladores y maneja la inicialización global.

**Responsabilidades:**
- Instanciar y coordinar todos los controladores
- Inicializar el sistema completo
- Exponer funciones globales (como `window.openImageModal`)
- Proporcionar acceso a los controladores hijos

**Métodos principales:**
- `init()` - Inicializa todos los controladores
- `setupGlobalModalFunction()` - Configura la función global para abrir el modal
- `getAdvancedConfigController()` - Obtiene el controlador de configuración
- `getImageModalController()` - Obtiene el controlador del modal

---

### **ImageModalController** (`controllers/ImageModalController.js`)
Maneja toda la lógica del modal de imágenes: apertura, cierre, tabs y preview.

**Responsabilidades:**
- Abrir y cerrar el modal
- Gestionar el sistema de tabs (URL, Archivo, Portapapeles, Búsqueda)
- Actualizar y limpiar el preview de imágenes
- Confirmar la selección de imagen

**Métodos principales:**
- `init()` - Inicializa el controlador
- `open(query, callback)` - Abre el modal con un query y callback
- `close()` - Cierra el modal y limpia el estado
- `showTab(tabId)` - Muestra un tab específico
- `updatePreview(url)` - Actualiza el preview con una imagen
- `clearPreview()` - Limpia el preview
- `getSelectedImageUrl()` - Obtiene la URL seleccionada

---

### **ImageSearchController** (`controllers/ImageSearchController.js`)
Maneja las búsquedas de imágenes, caché y resultados.

**Responsabilidades:**
- Realizar búsquedas de imágenes
- Gestionar el caché de resultados
- Renderizar resultados de búsqueda
- Configurar callbacks para las imágenes

**Métodos principales:**
- `init()` - Inicializa el controlador
- `performSearch()` - Realiza una búsqueda de imágenes
- `renderSearchResults(items)` - Renderiza los resultados
- `showCachedResults(query)` - Muestra resultados cacheados si existen

**Dependencias:**
- Requiere instancia de `ImageModalController` para actualizar preview
- Usa `apiClient.js` para búsquedas
- Usa `SearchImage.js` para gestión de caché

---

### **ImageSourceController** (`controllers/ImageSourceController.js`)
Maneja las diferentes formas de cargar imágenes: URL, archivo y portapapeles.

**Responsabilidades:**
- Gestionar carga desde URL
- Gestionar carga desde archivo local
- Gestionar carga desde portapapeles (paste)
- Procesar y convertir imágenes a formato adecuado

**Métodos principales:**
- `init()` - Inicializa el controlador
- `setupUrlSource()` - Configura la fuente de URL
- `setupFileSource()` - Configura la fuente de archivo
- `setupClipboardSource()` - Configura la fuente de portapapeles

**Dependencias:**
- Requiere instancia de `ImageModalController` para actualizar preview

---

### **AdvancedConfigController** (`controllers/AdvancedConfigController.js`)
Maneja los controles de configuración avanzada de la generación de IA.

**Responsabilidades:**
- Gestionar el panel de configuración avanzada
- Controlar los sliders de temperatura, topP y topK
- Proporcionar los valores de configuración a la generación

**Métodos principales:**
- `init()` - Inicializa el controlador
- `setupToggle()` - Configura el toggle del panel
- `setupSliders()` - Configura los sliders
- `getConfig()` - Obtiene la configuración actual
- `setConfig(config)` - Establece una configuración

**Valores configurables:**
- `temperature` (0.0 - 2.0) - Controla la creatividad de la IA
- `topP` (0.0 - 1.0) - Controla la diversidad del vocabulario
- `topK` (1 - 100) - Controla el número de tokens considerados

---

## 🔄 Flujo de Datos

```
main.js
  └─> AppController
       ├─> ImageModalController
       │    ├─> Maneja tabs y preview
       │    └─> Coordina con otros controladores
       │
       ├─> ImageSearchController
       │    ├─> Usa ImageModalController.updatePreview()
       │    └─> Renderiza resultados
       │
       ├─> ImageSourceController
       │    ├─> Usa ImageModalController.updatePreview()
       │    └─> Procesa diferentes fuentes
       │
       └─> AdvancedConfigController
            └─> Proporciona configuración a generarFicha()
```

---

## 🚀 Uso

### Inicialización en `main.js`

```javascript
import AppController from './controllers/AppController.js';

const appController = new AppController();

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar todos los controladores
    appController.init();
});
```

### Abrir modal de imágenes

```javascript
// Desde cualquier parte del código
window.openImageModal('búsqueda de imagen', (imageUrl) => {
    console.log('Imagen seleccionada:', imageUrl);
    // Hacer algo con la imagen
});
```

### Obtener configuración avanzada

```javascript
const config = appController.getAdvancedConfigController().getConfig();
// config = { temperature: 1.0, topP: 1.0, topK: 40 }
```

---

## ✅ Beneficios de esta Arquitectura

1. **Separación de Responsabilidades**: Cada controlador tiene una función específica y bien definida
2. **Mantenibilidad**: Código más fácil de mantener y actualizar
3. **Testabilidad**: Cada controlador puede ser probado independientemente
4. **Reutilización**: Los controladores pueden ser reutilizados en otros contextos
5. **Escalabilidad**: Fácil agregar nuevos controladores sin afectar los existentes
6. **Claridad**: El código es más legible y autodocumentado

---

## 📝 Notas Importantes

- Todos los controladores siguen el patrón de inicialización con `init()`
- Los controladores hijos reciben dependencias en el constructor
- Los controladores no acceden directamente al DOM en el constructor
- El logging está implementado con prefijos `[NombreControlador]` para debugging
- La función `window.openImageModal` es expuesta globalmente para compatibilidad

---

## 🔧 Mantenimiento

Para agregar un nuevo controlador:

1. Crear el archivo en `controllers/NombreController.js`
2. Implementar el patrón `constructor()` + `init()`
3. Agregar al `AppController` si necesita coordinación global
4. Documentar responsabilidades y métodos principales
5. Actualizar este README

---

## 📦 Archivos Modificados

- `main.js` - Refactorizado para usar AppController
- `controllers/AppController.js` - Nuevo controlador principal
- `controllers/ImageModalController.js` - Lógica del modal
- `controllers/ImageSearchController.js` - Búsqueda de imágenes
- `controllers/ImageSourceController.js` - Fuentes de imágenes
- `controllers/AdvancedConfigController.js` - Configuración avanzada

---

**Autor**: Sistema de Controladores Modulares  
**Fecha**: 2025  
**Versión**: 1.0.0
