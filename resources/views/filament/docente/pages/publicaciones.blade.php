<div>
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" href="{{ asset('assets/style/publicacion.css') }}">
    </head>

    <div class="container container--full">
        <!-- Info -->
        <div class="info">
            <h1>Publicaciones</h1>
            <span>
                Plantillas públicas de sesiones — puedes revisar, descargar o usar como plantilla.
            </span>
        </div>

        <!-- Panel de botones -->
        <nav class="panel">
            <button class="panel-btn active" data-filter="all">Todos</button>
            <button class="panel-btn" data-filter="unidades">Unidades</button>
            <button class="panel-btn" data-filter="sesiones">Sesiones</button>
            <button class="panel-btn" data-filter="plantillas">Plantillas</button>
            <button class="panel-btn" data-filter="otros">Otros</button>
        </nav>

        <!-- UN SOLO GRID que contiene sesiones y unidades -->
        <div class="grid">
            @foreach($sesiones as $sesion)
            <article class="card" data-id="{{ $sesion->id }}" data-type="sesiones">
                <div class="card-thumb">
                    <img src="{{ $sesion->imagen_url }}" alt="{{ $sesion->titulo ?? 'Sesión' }}">
                    <!-- Badge tipo -->
                    <div class="badge badge--sesion">Sesión</div>
                    <!-- Círculo de fecha para sesiones (día / mes) -->
                    <div class="date date--sesion">
                        <div class="day">{{ $sesion->fecha ? $sesion->fecha->format('d') : '' }}</div>
                        <div class="month">{{ $sesion->fecha ? $sesion->fecha->format('M') : '' }}</div>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Modificado: mostrar grado y sección del aula en lugar de "tema" --}}
                    <div class="meta">
                        <span class="tema">
                            @php
                                $aula = optional(optional($sesion->aulaCurso)->aula);
                                $gradoAula = $aula->grado ?? '—';
                                $seccionAula = $aula->seccion ?? '—';
                            @endphp
                            {{ $gradoAula }} · {{ $seccionAula }}
                        </span>
                        @php
                            // Nombre del docente y comprobación si es el usuario autenticado
                            $docentePersona = optional(optional($sesion->docente)->persona);
                            $docenteName = trim(($docentePersona->nombre ?? '') . ' ' . ($docentePersona->apellido ?? ''));
                            $isYou = auth()->check() && auth()->id() == $sesion->docente_id;

                            // Calcular URL de edición robusta (preferir /docente/sesions/{id}/edit)
                            $sesionEditUrl = null;
                            if (\Illuminate\Support\Facades\Route::has('docente.sesions.edit')) {
                                $sesionEditUrl = route('docente.sesions.edit', $sesion->id);
                            } elseif (\Illuminate\Support\Facades\Route::has('sesiones.edit')) {
                                $sesionEditUrl = route('sesiones.edit', $sesion->id);
                            } elseif (\Illuminate\Support\Facades\Route::has('filament.resources.sesions.edit')) {
                                $sesionEditUrl = route('filament.resources.sesions.edit', $sesion->id);
                            } else {
                                // Fallback directo usando el prefijo /docente (según tu ejemplo)
                                $sesionEditUrl = url('/docente/sesions/'.$sesion->id.'/edit');
                            }
                        @endphp

                        <span class="docente">
                            @if($isYou)
                                <span class="you-label">(Tú)</span>
                            @else
                                {{ $docenteName ?: 'Docente' }}
                            @endif
                        </span>
                    </div>

                    <h3 class="title">{{ $sesion->titulo ?? 'Sin título' }}</h3>
                    <p class="subtitle">{{ $sesion->proposito_sesion ?? '' }}</p>


                    <!-- NUEVO: mostrar fecha y nombre del curso -->
                    @php
                        $cursoName = optional(optional($sesion->aulaCurso)->curso)->curso
                            ?: optional(optional($sesion->aulaCurso)->curso)->titulo
                            ?: optional($sesion->curso)->nombre
                            ?: optional($sesion->curso)->titulo
                            ?: 'Curso desconocido';
                    @endphp

                    <div class="card-meta-info" style="font-size:13px;color:#6b7280;margin-bottom:10px;">
                        <div style="margin-bottom:6px;"><strong>Curso:</strong> {{ $cursoName }}</div>
                        <div style="margin-bottom:6px;"><strong>Duración:</strong> {{ $sesion->tiempo_estimado ?? '' }} minutos</div>
                    </div>

                    <div class="card-actions">
                        @if($isYou)
                            <a class="btn btn-edit" href="{{ $sesionEditUrl }}" title="Editar publicación">Editar publicación</a>
                        @else
                            <button class="btn btn-duplicate" data-id="{{ $sesion->id }}" title="Usar plantilla">Usar plantilla</button>
                        @endif

                        <a class="btn btn-download" href="{{ $sesion->download_url }}" data-id="{{ $sesion->id }}" target="_blank" title="Descargar">
                            Descargar
                        </a>

                    </div>
                </div>
            </article>
            @endforeach

            @foreach($unidades as $unidad)
            <article class="card" data-id="{{ $unidad->id }}" data-type="unidades">
                <div class="card-thumb">
                    <img src="{{ $unidad->imagen_url ?? 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200&auto=format&fit=crop' }}" alt="{{ $unidad->titulo ?? 'Unidad' }}">
                    <!-- Badge tipo -->
                    <div class="badge badge--unidad">Unidad</div>
                    <!-- Círculo de identificación para unidades (letra U) -->
                    <div class="date date--unidad">
                        <div class="label">U</div>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Modificado: mostrar grado y secciones de la unidad en lugar de "tema" --}}
                    <div class="meta">
                        <span class="tema">
                            @php
                                $gradoUnidad = $unidad->grado ?? '—';
                                $seccionesUnidad = is_array($unidad->secciones) ? implode(', ', $unidad->secciones) : ($unidad->secciones ?? '—');
                            @endphp
                            {{ $gradoUnidad }} · {{ $seccionesUnidad }}
                        </span>
                    </div>

                    <h3 class="title">{{ $unidad->nombre ?? 'Sin título' }}</h3>

                    <!-- Mostrar sólo nombre y profesores responsables (sin situacion_significativa) -->
                    <div class="card-meta-info" style="font-size:13px;color:#6b7280;margin-bottom:10px;">
                        <p class="subtitle">
                            <strong>Fechas:</strong>
                            {{
                                (optional($unidad->fecha_inicio)?->format('d M Y') ?? '—')
                                . ' — ' .
                                (optional($unidad->fecha_fin)?->format('d M Y') ?? '—')
                            }}
                        </p>

                        <!-- Modificado: listar profesores responsables en líneas separadas -->
                        <div>
                            <strong>Profesores responsables:</strong>
                            @if(!empty($unidad->profesores) && $unidad->profesores->isNotEmpty())
                                <div style="margin-top:6px;">
                                    @foreach($unidad->profesores as $prof)
                                        @php
                                            $profName = trim(($prof->persona?->nombre ?? '') . ' ' . ($prof->persona?->apellido ?? ''));
                                            $isProfYou = auth()->check() && auth()->id() == $prof->id;
                                        @endphp
                                        <div>- 
                                            @if($isProfYou)
                                                <span class="you-label">(Tú)</span>
                                            @else
                                                {{ $profName ?: 'Docente' }}
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(!empty($unidad->nombres_profesores))
                                <div style="margin-top:6px;">
                                    @foreach(explode(',', $unidad->nombres_profesores) as $name)
                                        <div>- {{ trim($name) }}</div>
                                    @endforeach
                                </div>
                            @else
                                <div style="margin-top:6px;">—</div>
                            @endif
                        </div>
                    </div>

                    <div class="card-actions">
                        @php
                            // Calcular si el usuario es propietario de la unidad (ya existía lógica)
                            $isOwnerUnit = false;
                            if(auth()->check()) {
                                if(!empty($unidad->profesores) && $unidad->profesores->isNotEmpty()) {
                                    $isOwnerUnit = $unidad->profesores->pluck('id')->contains(auth()->id());
                                } elseif(!empty($unidad->profesores_responsables) && is_array($unidad->profesores_responsables)) {
                                    $isOwnerUnit = in_array(auth()->id(), array_map('intval', $unidad->profesores_responsables));
                                }
                            }

                            // Calcular URL de edición robusta para unidades (preferir /docente/unidads/{id}/edit)
                            $unidadEditUrl = null;
                            if (\Illuminate\Support\Facades\Route::has('unidades.edit')) {
                                $unidadEditUrl = route('unidades.edit', $unidad->id);
                            } elseif (\Illuminate\Support\Facades\Route::has('docente.unidads.edit')) {
                                $unidadEditUrl = route('docente.unidads.edit', $unidad->id);
                            } elseif (\Illuminate\Support\Facades\Route::has('filament.resources.unidads.edit')) {
                                $unidadEditUrl = route('filament.resources.unidads.edit', $unidad->id);
                            } else {
                                // Usar la ruta con prefijo /docente si existe en tu setup Filament
                                $unidadEditUrl = url('/docente/unidads/'.$unidad->id.'/edit') ?: url('/unidades/'.$unidad->id.'/edit');
                            }
                        @endphp

                        @if($isOwnerUnit)
                            <a class="btn btn-edit" href="{{ $unidadEditUrl }}" title="Editar archivo">Editar archivo</a>
                        @else
                            <button class="btn btn-duplicate" data-id="{{ $unidad->id }}" title="Usar plantilla">Usar plantilla</button>
                        @endif

                        <a class="btn btn-download" href="{{ $unidad->download_url ?? '#' }}" data-id="{{ $unidad->id }}" target="_blank" title="Descargar">
                            Descargar
                        </a>

                        <span class="time">{{ $unidad->duracion ?? '' }}</span>
                    </div>
                </div>
            </article>
            @endforeach
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Duplicar plantilla (usar como plantilla)
            document.querySelectorAll('.btn-duplicate').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const id = this.dataset.id;
                    if (!confirm('¿Deseas usar esta publicación como plantilla y duplicarla en tu cuenta?')) return;

                    try {
                        const res = await fetch(`/sesiones/${id}/duplicate`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({})
                        });
                        if (!res.ok) throw new Error('Error en la petición');
                        const json = await res.json();
                        alert(json.message || 'Plantilla duplicada correctamente.');
                        // Opcional: redirigir a edición de la nueva sesión:
                        if (json.redirect) window.location = json.redirect;
                    } catch (e) {
                        console.error(e);
                        alert('No se pudo duplicar la plantilla. Revisa la consola.');
                    }
                });
            });

            // Descarga: si el enlace no está disponible, podemos intentar llamar al endpoint de descarga
            document.querySelectorAll('.btn-download').forEach(a => {
                a.addEventListener('click', function (e) {
                    // si el href es '#' o está vacío, evitar comportamiento por defecto y llamar fetch
                    const href = this.getAttribute('href');
                    const id = this.dataset.id;
                    if (!href || href === '#') {
                        e.preventDefault();
                        fetch(`/sesiones/${id}/download`, {
                            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                        })
                        .then(r => r.blob())
                        .then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = url;
                            link.download = `sesion-${id}.pdf`;
                            document.body.appendChild(link);
                            link.click();
                            link.remove();
                            window.URL.revokeObjectURL(url);
                        })
                        .catch(err => { console.error(err); alert('Error al descargar.'); });
                    }
                });
            });

            // Panel buttons: filtrar tarjetas por data-type
            document.querySelectorAll('.panel-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.panel-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const filter = this.dataset.filter;
                    document.querySelectorAll('.grid .card').forEach(card => {
                        if (filter === 'all') {
                            card.style.display = '';
                        } else {
                            const type = card.dataset.type || '';
                            card.style.display = (type === filter) ? '' : 'none';
                        }
                    });
                });
            });
        });
    </script>
</div>

