<div class="space-y-3">
    <div class="mb-3">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" id="mostrarEnfoques" name="mostrarEnfoques" class="rounded">
            <span class="text-sm">¿Agregar enfoques transversales?</span>
        </label>
    </div>

    <div id="camposEnfoques" style="display:none;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="enfoque_transversal" class="block text-sm font-medium mb-1">Enfoque transversal</label>
                <select name="enfoque_transversal[]" id="enfoque_transversal" class="w-full" multiple="multiple"></select>
            </div>

            <div>
                <label for="competencias_transversales" class="block text-sm font-medium mb-1">Competencias transversales</label>
                <select name="competencias_transversales[]" id="competencias_transversales" class="w-full" multiple="multiple"></select>
            </div>

            <div>
                <label for="capacidades_transversales" class="block text-sm font-medium mb-1">Capacidades transversales</label>
                <select name="capacidades_transversales[]" id="capacidades_transversales" class="w-full" multiple="multiple"></select>
            </div>

            <div>
                <label for="desempeno_transversal" class="block text-sm font-medium mb-1">Desempeños transversales</label>
                <select name="desempeno_transversal[]" id="desempeno_transversal" class="w-full" multiple="multiple"></select>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Requerir jQuery/Select2; si no existen, las partes con $ no correrán.
    const $exists = typeof $ !== 'undefined';
    const mostrarEnfoques = $exists ? $('#mostrarEnfoques') : document.getElementById('mostrarEnfoques');
    const camposEnfoques = $exists ? $('#camposEnfoques') : document.getElementById('camposEnfoques');

    const enfoqueSelect = $exists ? $('#enfoque_transversal') : document.getElementById('enfoque_transversal');
    const competenciasSelect = $exists ? $('#competencias_transversales') : document.getElementById('competencias_transversales');
    const capacidadesSelect = $exists ? $('#capacidades_transversales') : document.getElementById('capacidades_transversales');
    const desempenoTransversalSelect = $exists ? $('#desempeno_transversal') : document.getElementById('desempeno_transversal');

    // Helpers para show/hide sin jQuery
    function showEl(el) { if (!el) return; el.style.display = ''; }
    function hideEl(el) { if (!el) return; el.style.display = 'none'; }

    // inicializar Select2 si existe
    if ($exists) {
        enfoqueSelect.select2({ placeholder: "Seleccione o agregue enfoques transversales", tags: true, tokenSeparators: [',',' '], allowClear: true, width: '100%' });
        competenciasSelect.select2({ placeholder: "Seleccione o agregue competencias transversales", tags: true, tokenSeparators: [',',' '], allowClear: true, width: '100%' });
        capacidadesSelect.select2({ placeholder: "Seleccione o agregue capacidades transversales", tags: true, tokenSeparators: [',',' '], allowClear: true, width: '100%' });
        desempenoTransversalSelect.select2({ placeholder: "Seleccione o agregue desempeños transversales", tags: true, tokenSeparators: [',',' '], allowClear: true, width: '100%' });
    }

    // Ocultar inicialmente
    if ($exists) { camposEnfoques.hide(); } else { hideEl(camposEnfoques); }

    // Toggle mostrar/ocultar
    if ($exists) {
        mostrarEnfoques.on('change', function () {
            if (this.checked) {
                camposEnfoques.show();
                // cargar opciones via AJAX
                enfoqueSelect.empty();
                competenciasSelect.empty();
                capacidadesSelect.empty();
                desempenoTransversalSelect.empty();

                $.get('/enfoques-transversales').done(function(data){
                    data.forEach(e => enfoqueSelect.append(new Option(e.nombre, e.id)));
                    enfoqueSelect.trigger('change');
                }).fail(console.error);

                $.get('/competencias-transversales').done(function(data){
                    data.forEach(c => competenciasSelect.append(new Option(c.nombre, c.id)));
                    competenciasSelect.trigger('change');
                }).fail(console.error);
            } else {
                camposEnfoques.hide();
                enfoqueSelect.val(null).trigger && enfoqueSelect.val(null).trigger('change');
                competenciasSelect.val(null).trigger && competenciasSelect.val(null).trigger('change');
                capacidadesSelect.val(null).trigger && capacidadesSelect.val(null).trigger('change');
                desempenoTransversalSelect.val(null).trigger && desempenoTransversalSelect.val(null).trigger('change');
            }
        });
    } else {
        mostrarEnfoques.addEventListener('change', function () {
            if (this.checked) {
                showEl(camposEnfoques);
                // Cargar opciones sin jQuery (fetch)
                fetch('/enfoques-transversales').then(r=>r.json()).then(data=>{
                    const sel = document.getElementById('enfoque_transversal');
                    data.forEach(e => { const o = new Option(e.nombre, e.id); sel.add(o); });
                }).catch(console.error);

                fetch('/competencias-transversales').then(r=>r.json()).then(data=>{
                    const sel = document.getElementById('competencias_transversales');
                    data.forEach(c => { const o = new Option(c.nombre, c.id); sel.add(o); });
                }).catch(console.error);
            } else {
                hideEl(camposEnfoques);
                document.getElementById('enfoque_transversal').value = null;
                document.getElementById('competencias_transversales').value = null;
                document.getElementById('capacidades_transversales').value = null;
                document.getElementById('desempeno_transversal').value = null;
            }
        });
    }

    // Cargar capacidades al cambiar competencias (con jQuery)
    if ($exists) {
        competenciasSelect.on('change', function () {
            const competenciasSeleccionadas = $(this).val() || [];
            capacidadesSelect.empty().trigger('change');

            if (!competenciasSeleccionadas.length) return;
            const capacidadesCargadas = new Set();

            competenciasSeleccionadas.forEach(function(competenciaId) {
                $.get(`/competencias-transversales/${competenciaId}/capacidades`)
                 .done(function(data){
                     data.forEach(function(capacidad) {
                         if (!capacidadesCargadas.has(capacidad.id)) {
                             capacidadesCargadas.add(capacidad.id);
                             capacidadesSelect.append(new Option(capacidad.nombre, capacidad.id));
                         }
                     });
                     capacidadesSelect.trigger('change');
                 }).fail(console.error);
            });
        });

        capacidadesSelect.on('change', function () {
            const capacidadesSeleccionadas = $(this).val() || [];
            desempenoTransversalSelect.empty().trigger('change');
            if (!capacidadesSeleccionadas.length) return;

            $.post('/desempenos/por-capacidad-transversal', { capacidades_transversales: capacidadesSeleccionadas, _token: '{{ csrf_token() }}' })
             .done(function(data){
                 if (Array.isArray(data)) {
                     data.forEach(function(d){
                         desempenoTransversalSelect.append(new Option(d.descripcion, d.id));
                     });
                     desempenoTransversalSelect.trigger('change');
                 }
             }).fail(console.error);
        });

        competenciasSelect.on('select2:clear', function(){
            capacidadesSelect.empty().trigger('change');
            desempenoTransversalSelect.empty().trigger('change');
        });
    } else {
        // Sin jQuery: manejo simple para capacidades -> desempeños (puedes extender si necesitas)
        document.getElementById('competencias_transversales').addEventListener('change', function() {
            const competenciasSeleccionadas = Array.from(this.selectedOptions).map(o=>o.value);
            const capacidadesEl = document.getElementById('capacidades_transversales');
            capacidadesEl.innerHTML = '';
            if (!competenciasSeleccionadas.length) return;
            const capacidadesCargadas = new Set();
            competenciasSeleccionadas.forEach(id => {
                fetch(`/competencias-transversales/${id}/capacidades`)
                    .then(r=>r.json())
                    .then(data=>{
                        data.forEach(cap => {
                            if (!capacidadesCargadas.has(cap.id)) {
                                capacidadesCargadas.add(cap.id);
                                capacidadesEl.add(new Option(cap.nombre, cap.id));
                            }
                        });
                    }).catch(console.error);
            });
        });
    }
});
</script>