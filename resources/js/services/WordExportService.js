export class WordExportService {
  constructor(quillManager) {
    this.quillManager = quillManager;
  }

  generateFichaHTML(fichaData, aprendizajeData) {
    const inicioHTML = this.quillManager.getContent('#inicio-editor');
    const desarrolloHTML = this.quillManager.getContent('#desarrollo-editor');
    const conclusionHTML = this.quillManager.getContent('#conclusion-editor');

    return `
      <html>
        <head>
          <meta charset="UTF-8">
          <title>Ficha de Sesión de Aprendizaje</title>
          <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { text-align: center; color: #2563eb; }
            h2 { color: #1e40af; border-bottom: 2px solid #3b82f6; padding-bottom: 5px; }
            .section { margin-bottom: 20px; }
            .form-data { background: #f3f4f6; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; }
            td { padding: 5px; border: 1px solid #ddd; }
          </style>
        </head>
        <body>
          <h1>Ficha de Sesión de Aprendizaje</h1>

          <div class="form-data">
            <h2>Datos de la Sesión</h2>
            <table>
              <tr><td><strong>Nombre de la Sesión:</strong></td><td>${aprendizajeData.titulo || 'N/A'}</td></tr>
              <tr><td><strong>Propósito:</strong></td><td>${aprendizajeData.proposito || 'N/A'}</td></tr>
              <tr><td><strong>Competencia:</strong></td><td>${aprendizajeData.competencia || 'N/A'}</td></tr>
              <tr><td><strong>Capacidades:</strong></td><td>${aprendizajeData.capacidades || 'N/A'}</td></tr>
              <tr><td><strong>Estándares:</strong></td><td>${aprendizajeData.estandares || 'N/A'}</td></tr>
              <tr><td><strong>Criterios de Evaluación:</strong></td><td>${aprendizajeData.criterios || 'N/A'}</td></tr>
              <tr><td><strong>Evidencias de Aprendizaje:</strong></td><td>${aprendizajeData.evidencias || 'N/A'}</td></tr>
              <tr><td><strong>Instrumentos de Evaluación:</strong></td><td>${aprendizajeData.instrumentos || 'N/A'}</td></tr>
            </table>
          </div>

          <div class="section">
            <h2>I. Inicio</h2>
            <div>${inicioHTML}</div>
          </div>

          <div class="section">
            <h2>II. Desarrollo</h2>
            <div>${desarrolloHTML}</div>
          </div>

          <div class="section">
            <h2>III. Cierre</h2>
            <div>${conclusionHTML}</div>
          </div>
        </body>
      </html>
    `;
  }

  async exportToWord(fichaController, aprendizajeController) {
    const fichaData = {
      inicio: fichaController.inicio,
      desarrollo: fichaController.desarrollo,
      conclusion: fichaController.conclusion
    };

    const aprendizajeData = aprendizajeController.obtenerAprendizajes()[0] || {};

    const html = this.generateFichaHTML(fichaData, aprendizajeData);

    const docx = htmlDocx.asBlob(html);
    const url = URL.createObjectURL(docx);

    const a = document.createElement('a');
    a.href = url;
    a.download = 'ficha-aprendizaje.docx';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }
}