/**
 * Servicio para exportar fichas educativas a PDF
 * Prepara el documento para impresión ocultando elementos interactivos
 */
class PdfExportService {
  constructor() {
    this.printStyles = null;
  }

  /**
   * Aplica estilos de impresión para ocultar elementos interactivos
   */
  applyPrintStyles() {
    console.log('🖨️ [PdfExport] Aplicando estilos de impresión');
    
    if (!this.printStyles) {
      this.printStyles = document.createElement('style');
      this.printStyles.id = 'pdf-export-styles';
      this.printStyles.textContent = `
        @media print {
          /* Ocultar solo elementos específicos de la interfaz */
          .no-imprimir,
          #imageModal {
            display: none !important;
          }

          /* Ocultar todos los botones */
          button {
            display: none !important;
          }

          /* Ocultar panel de controles */
          .lg\\:col-span-2 {
            display: none !important;
          }

          /* Ajustar el contenedor principal */
          body {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
          }

          /* Maximizar el área de la ficha */
          .max-w-7xl {
            max-width: 100% !important;
            padding: 0 !important;
          }

          .lg\\:col-span-3 {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
          }

          /* Ajustar el contenedor de la ficha */
          .bg-white.rounded-lg.shadow-xl {
            box-shadow: none !important;
            border-radius: 0 !important;
            padding: 20mm !important;
            min-height: auto !important;
          }

          /* Asegurar que las imágenes se vean bien */
          img {
            max-width: 100% !important;
            height: auto !important;
            page-break-inside: avoid;
            display: block !important;
          }

          /* Evitar saltos de página en elementos */
          .seleccion-grid,
          .clasificacion-grid,
          .cloze-grid,
          .cloze-item,
          .ficha-card {
            page-break-inside: avoid;
          }

          /* Ajustar inputs para que se vean como texto */
          input[type="text"],
          textarea {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            outline: none !important;
            color: #1e293b !important;
          }

          /* Mantener bordes decorativos */
          .border-b-2,
          .border-t {
            border-color: #cbd5e1 !important;
          }

          /* Asegurar visibilidad del contenido */
          #ficha-contenido,
          #ficha-contenido * {
            visibility: visible !important;
            opacity: 1 !important;
          }
        }
      `;
      document.head.appendChild(this.printStyles);
    }
  }

  /**
   * Elimina los estilos de impresión
   */
  removePrintStyles() {
    console.log('🖨️ [PdfExport] Removiendo estilos de impresión');
    if (this.printStyles) {
      this.printStyles.remove();
      this.printStyles = null;
    }
  }

  /**
   * Genera vista previa para impresión
   */
  generatePreview() {
    console.log('👁️ [PdfExport] Generando vista previa de impresión');
    
    const fichaContenido = document.getElementById('ficha-contenido');
    if (!fichaContenido || !fichaContenido.innerHTML.trim()) {
      console.error('❌ [PdfExport] No hay contenido generado');
      alert('Primero debes generar una ficha antes de exportar');
      return;
    }

    console.log('✅ [PdfExport] Contenido encontrado. Abriendo diálogo de impresión...');
    
    // Simplemente abrir el diálogo de impresión
    // Los estilos @media print se encargarán del resto
    window.print();
  }

  /**
   * Exporta directamente a PDF (abre el diálogo de impresión)
   */
  exportToPdf() {
    this.generatePreview();
  }
}

export default PdfExportService;
