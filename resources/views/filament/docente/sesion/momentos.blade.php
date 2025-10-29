<style>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Text:wght@400;600;700&display=swap');
body {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 14px;
}
.titulo-documento {
  font-family: 'Crimson Text', serif;
  letter-spacing: 0.5px;
}
.campo-editable {
  border: none;
  border-bottom: 1px solid #e5e7eb;
  background: transparent;
  transition: all 0.2s;
  font-family: Arial, Helvetica, sans-serif;
  line-height: 1.8;
  resize: vertical;
}
.campo-editable:focus {
  outline: none;
  border-bottom: 2px solid #3b82f6;
  background: #f9fafb;
}
.campo-editable:hover {
  background: #f9fafb;
}
.generar-btn,
.generar-btn span {
    color: #fff !important;
}
.generar-btn {
    background: linear-gradient(to right, #f97316, #c2410c) !important;
    border: none !important;
}
.generar-btn:hover {
    background: linear-gradient(to right, #ea580c, #9a3412) !important;
}
#modal-generar-momentos {
    background: rgba(30,41,59,0.7) !important;
}
#modal-generar-momentos .bg-white {
    box-shadow: 0 8px 32px rgba(30,41,59,0.25), 0 1.5px 8px rgba(30,41,59,0.10);
}
.bg-blue-600 {
    background-color: #2563eb !important;
}
.bg-blue-600:hover {
    background-color: #1e40af !important;
}
.bg-blue-700 {
    background-color: #1e40af !important;
}
.bg-green-500 {
    background-color: #22c55e !important;
}
.bg-green-600 {
    background-color: #16a34a !important;
}
.bg-green-700 {
    background-color: #15803d !important;
}
.bg-green-800 {
    background-color: #166534 !important;
}
.bg-orange-500 {
    background-color: #f97316 !important;
}
.bg-orange-600 {
    background-color: #ea580c !important;
}
.bg-orange-700 {
    background-color: #c2410c !important;
}
.bg-orange-800 {
    background-color: #9a3412 !important;
}
.text-white, .bg-blue-600 span, .bg-blue-600 {
    color: #fff !important;
}
@media print {
  .no-imprimir {
    display: none;
  }
  body {
    background: white;
  }
}
@media (max-width: 768px) {
    .p-8, .md\:p-16 { padding: 1.5rem !important; }
    .px-4, .md\:px-12, .lg\:px-32 { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
}
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-100 py-8 flex justify-center items-start">
    <div class="w-full px-4 md:px-12 lg:px-32" style="max-width: 1200px;">
        <div class="bg-white rounded-lg shadow-xl p-8 md:p-16" style="min-height: 800px;">
            <h2 class="titulo-documento text-3xl font-bold text-slate-800 text-center mb-8">
                Momentos de Sesión de Aprendizaje
            </h2>
            <form>
                <div class="mb-10">
                    <label for="inicio" class="block text-xl font-bold text-blue-800 mb-2">I. Inicio</label>
                    <textarea id="inicio" name="inicio" class="campo-editable w-full border border-slate-300 rounded-lg px-4 py-6 text-base" rows="8" placeholder="Describe el momento de Inicio..."></textarea>
                </div>
                <div class="mb-10">
                    <label for="desarrollo" class="block text-xl font-bold text-blue-800 mb-2">II. Desarrollo</label>
                    <textarea id="desarrollo" name="desarrollo" class="campo-editable w-full border border-slate-300 rounded-lg px-4 py-6 text-base" rows="8" placeholder="Describe el momento de Desarrollo..."></textarea>
                </div>
                <div class="mb-10">
                    <label for="cierre" class="block text-xl font-bold text-blue-800 mb-2">III. Cierre</label>
                    <textarea id="cierre" name="cierre" class="campo-editable w-full border border-slate-300 rounded-lg px-4 py-6 text-base" rows="8" placeholder="Describe el momento de Cierre..."></textarea>
                </div>
                <button type="button" id="btn-generar-momentos"
    class="generar-btn w-full px-6 py-4 rounded-lg transition-all shadow-md hover:shadow-lg text-base font-semibold flex items-center justify-center gap-2 mt-8"
    style="background: linear-gradient(to right, #f97316, #c2410c); color: #fff; border: none;">
                    <span style="color: #fff;">🤖</span>
                    <span style="color: #fff;">Generar Momentos</span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal para tema de generación de momentos -->
<div id="modal-generar-momentos" class="fixed inset-0 flex items-center justify-center z-50 hidden px-4 md:px-12 lg:px-32">
    <div class="bg-white rounded-xl p-6 md:p-12 w-full max-w-2xl shadow-2xl border-t-4 border-blue-600 relative">
        <h3 class="text-xl font-bold mb-4 text-blue-800 flex items-center gap-2"><span>🤖</span> Generar Momentos con IA</h3>
        <label class="block mb-2 text-sm font-semibold text-slate-700">Tema:</label>
        <input type="text" id="tema-momentos" class="w-full border border-slate-300 rounded-lg px-4 py-2 mb-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="Ingrese el tema..." />
        <!-- Contenedor para mostrar los datos -->
        <div id="datos-sesion-modal" class="mb-4 text-sm text-slate-700"></div>
        <div id="momentos-error" class="text-red-600 text-sm mb-2 hidden"></div>
        <div class="flex justify-end gap-2">
            <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-blue-700 transition shadow" onclick="generarMomentos()">Generar</button>
            <button type="button" class="bg-slate-300 px-4 py-2 rounded-md font-semibold hover:bg-slate-400 transition shadow" onclick="cerrarModalGenerarMomentos()">Cancelar</button>
        </div>
    </div>
</div>
<pre>
    {{ var_dump($get('titulo')) }}
    {{ var_dump($get('tiempo_estimado')) }}
    {{ var_dump($get('proposito_sesion')) }}
    {{ var_dump($get('aula_curso_id')) }}
    {{ var_dump($get('grado')) }}
    {{ var_dump($get('nivel')) }}
    {{ var_dump($get('curso')) }}
</pre>
<script>
    window.datosSesion = @json($formData);
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('btn-generar-momentos').addEventListener('click', function() {
            document.getElementById('modal-generar-momentos').classList.remove('hidden');
            // Mostrar los datos en el modal
            const datos = window.datosSesion;
            document.getElementById('datos-sesion-modal').innerHTML = `
                <strong>Título:</strong> ${datos.titulo}<br>
                <strong>Tiempo estimado:</strong> ${datos.tiempo_estimado}<br>
                <strong>Propósito:</strong> ${datos.proposito_sesion}<br>
                <strong>Aula Curso ID:</strong> ${datos.aula_curso_id}<br>
                <strong>Grado:</strong> ${datos.grado}<br>
                <strong>Nivel:</strong> ${datos.nivel}<br>
                <strong>Curso:</strong> ${datos.curso}
            `;
        });
    });
</script>
<script>
window.datosSesion = {
        titulo: @json($get('titulo')),
        tiempo_estimado: @json($get('tiempo_estimado')),
        proposito_sesion: @json($get('proposito_sesion')),
        aula_curso_id: @json($get('aula_curso_id')),
        grado: @json($get('grado')),
        nivel: @json($get('nivel')),
        curso: @json($get('curso')),
    };
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('btn-generar-momentos').addEventListener('click', function() {
            document.getElementById('modal-generar-momentos').classList.remove('hidden');
            // Mostrar los datos en el modal
            const datos = window.datosSesion;
            document.getElementById('datos-sesion-modal').innerHTML = `
                <strong>Título:</strong> ${datos.titulo}<br>
                <strong>Tiempo estimado:</strong> ${datos.tiempo_estimado}<br>
                <strong>Propósito:</strong> ${datos.proposito_sesion}<br>
                <strong>Aula Curso ID:</strong> ${datos.aula_curso_id}<br>
                <strong>Grado:</strong> ${datos.grado}<br>
                <strong>Nivel:</strong> ${datos.nivel}<br>
                <strong>Curso:</strong> ${datos.curso}
            `;
        });
    });

    function cerrarModalGenerarMomentos() {
        document.getElementById('modal-generar-momentos').classList.add('hidden');
        document.getElementById('momentos-error').classList.add('hidden');
    }
    function generarMomentos() {
        const tema = document.getElementById('tema-momentos').value.trim();
        if (!tema) {
            document.getElementById('momentos-error').textContent = 'Por favor ingrese el tema.';
            document.getElementById('momentos-error').classList.remove('hidden');
            return;
        }
        // Aquí deberías hacer una petición AJAX a tu backend para generar los momentos con el tema
        cerrarModalGenerarMomentos();
    }
function generarMomentos() {
    const tema = document.getElementById('tema-momentos').value.trim();
    if (!tema) {
        document.getElementById('momentos-error').textContent = 'Por favor ingrese el tema.';
        document.getElementById('momentos-error').classList.remove('hidden');
        return;
    }
    // Aquí deberías hacer una petición AJAX a tu backend para generar los momentos con el tema
    cerrarModalGenerarMomentos();
}
</script>