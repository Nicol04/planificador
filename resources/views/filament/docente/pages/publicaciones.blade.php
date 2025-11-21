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
                Plantillas públicas de sesiones, compartidas por el personal docente — puedes revisar, descargar o usar como plantilla.
            </span>
        </div>

        <!-- Panel de botones -->
        <nav class="panel">
            <button class="panel-btn active" data-filter="all">Todos</button>
            <button class="panel-btn" data-filter="unidades">Unidades</button>
            <button class="panel-btn" data-filter="sesiones">Sesiones</button>
            <button class="panel-btn" data-filter="fichas">Fichas</button>
            <button class="panel-btn" data-filter="plantillas">Plantillas</button>
            <button class="panel-btn" data-filter="otros">Otros</button>
        </nav>

        <!-- UN SOLO GRID que contiene sesiones, unidades y fichas -->
        <div class="grid">
            @foreach ($sesiones as $sesion)
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
                                $docenteName = trim(
                                    ($docentePersona->nombre ?? '') . ' ' . ($docentePersona->apellido ?? ''),
                                );
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
                                    $sesionEditUrl = url('/docente/sesions/' . $sesion->id . '/edit');
                                }
                            @endphp

                            <span class="docente">
                                @if ($isYou)
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
                            $cursoName =
                                optional(optional($sesion->aulaCurso)->curso)->curso ?:
                                optional(optional($sesion->aulaCurso)->curso)->titulo ?:
                                optional($sesion->curso)->nombre ?:
                                optional($sesion->curso)->titulo ?:
                                'Curso desconocido';
                        @endphp

                        <div class="card-meta-info" style="font-size:13px;color:#6b7280;margin-bottom:10px;">
                            <div style="margin-bottom:6px;"><strong>Curso:</strong> {{ $cursoName }}</div>
                            <div style="margin-bottom:6px;"><strong>Duración:</strong>
                                {{ $sesion->tiempo_estimado ?? '' }} minutos</div>
                        </div>

                        <div class="card-actions">
                            @if ($isYou)
                                <a class="btn btn-edit" href="{{ $sesionEditUrl }}" title="Editar publicación">Editar
                                    publicación</a>
                            @else
                                <button class="btn btn-duplicate" data-id="{{ $sesion->id }}"
                                    data-titulo="{{ $sesion->titulo }}" title="Usar plantilla">
                                    Usar plantilla
                                </button>
                            @endif

                            <a class="btn btn-download" href="{{ $sesion->download_url }}"
                                data-id="{{ $sesion->id }}" target="_blank" title="Descargar">
                                Descargar
                            </a>

                        </div>
                    </div>
                </article>
            @endforeach

            @foreach ($unidades as $unidad)
                <article class="card" data-id="{{ $unidad->id }}" data-type="unidades">
                    <div class="card-thumb">
                        <img src="{{ $unidad->imagen_url ?? 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200&auto=format&fit=crop' }}"
                            alt="{{ $unidad->titulo ?? 'Unidad' }}">
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
                                    $seccionesUnidad = is_array($unidad->secciones)
                                        ? implode(', ', $unidad->secciones)
                                        : $unidad->secciones ?? '—';
                                @endphp
                                {{ $gradoUnidad }} · {{ $seccionesUnidad }}
                            </span>
                        </div>

                        <h3 class="title">{{ $unidad->nombre ?? 'Sin título' }}</h3>

                        <!-- Mostrar sólo nombre y profesores responsables (sin situacion_significativa) -->
                        <div class="card-meta-info" style="font-size:13px;color:#6b7280;margin-bottom:10px;">
                            <p class="subtitle">
                                <strong>Fechas:</strong>
                                {{ (optional($unidad->fecha_inicio)?->format('d M Y') ?? '—') .
                                    ' — ' .
                                    (optional($unidad->fecha_fin)?->format('d M Y') ?? '—') }}
                            </p>

                            <!-- Modificado: listar profesores responsables en líneas separadas -->
                            <div>
                                <strong>Profesores responsables:</strong>
                                @if (!empty($unidad->profesores) && $unidad->profesores->isNotEmpty())
                                    <div style="margin-top:6px;">
                                        @foreach ($unidad->profesores as $prof)
                                            @php
                                                $profName = trim(
                                                    ($prof->persona?->nombre ?? '') .
                                                        ' ' .
                                                        ($prof->persona?->apellido ?? ''),
                                                );
                                                $isProfYou = auth()->check() && auth()->id() == $prof->id;
                                            @endphp
                                            <div>-
                                                @if ($isProfYou)
                                                    <span class="you-label">(Tú)</span>
                                                @else
                                                    {{ $profName ?: 'Docente' }}
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif(!empty($unidad->nombres_profesores))
                                    <div style="margin-top:6px;">
                                        @foreach (explode(',', $unidad->nombres_profesores) as $name)
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
                                if (auth()->check()) {
                                    if (!empty($unidad->profesores) && $unidad->profesores->isNotEmpty()) {
                                        $isOwnerUnit = $unidad->profesores->pluck('id')->contains(auth()->id());
                                    } elseif (
                                        !empty($unidad->profesores_responsables) &&
                                        is_array($unidad->profesores_responsables)
                                    ) {
                                        $isOwnerUnit = in_array(
                                            auth()->id(),
                                            array_map('intval', $unidad->profesores_responsables),
                                        );
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
                                    $unidadEditUrl =
                                        url('/docente/unidads/' . $unidad->id . '/edit') ?:
                                        url('/unidades/' . $unidad->id . '/edit');
                                }
                            @endphp

                            @if ($isOwnerUnit)
                                <a class="btn btn-edit" href="{{ $unidadEditUrl }}" title="Editar archivo">Editar
                                    archivo</a>
                            @else
                                <button class="btn btn-duplicate" data-id="{{ $unidad->id }}"
                                    title="Usar plantilla">Usar plantilla</button>
                            @endif

                            <a class="btn btn-download" href="{{ $unidad->download_url ?? '#' }}"
                                data-id="{{ $unidad->id }}" target="_blank" title="Descargar">
                                Descargar
                            </a>

                            <span class="time">{{ $unidad->duracion ?? '' }}</span>
                        </div>
                    </div>
                </article>
            @endforeach

            {{-- Fichas de Aprendizaje Públicas --}}
            @foreach ($fichas as $ficha)
                <article class="card" data-id="{{ $ficha->id }}" data-type="fichas">
                    <div class="card-thumb">
                        <img src="{{ $ficha->imagen_url ?? 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=1200&auto=format&fit=crop' }}"
                            alt="{{ $ficha->nombre ?? 'Ficha' }}">
                        <!-- Badge tipo -->
                        <div class="badge badge--ficha" style="background: #10b981;">Ficha</div>
                        <!-- Círculo de identificación para fichas (letra F) -->
                        <div class="date date--ficha" style="background: #10b981;">
                            <div class="label">F</div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="meta">
                            <span class="tema">
                                {{ $ficha->grado ?? '—' }}
                            </span>
                            @php
                                $docentePersona = optional(optional($ficha->user)->persona);
                                $docenteName = trim(
                                    ($docentePersona->nombre ?? '') . ' ' . ($docentePersona->apellido ?? ''),
                                );
                                $isYou = auth()->check() && auth()->id() == $ficha->user_id;

                                $fichaEditUrl = route('filament.docente.resources.ficha-aprendizajes.edit', ['record' => $ficha->id]);
                            @endphp

                            <span class="docente">
                                @if ($isYou)
                                    <span class="you-label">(Tú)</span>
                                @else
                                    {{ $docenteName ?: 'Docente' }}
                                @endif
                            </span>
                        </div>

                        <h3 class="title">{{ $ficha->nombre ?? 'Sin título' }}</h3>
                        <p class="subtitle">{{ Str::limit($ficha->descripcion ?? '', 80) }}</p>

                        <div class="card-meta-info" style="font-size:13px;color:#6b7280;margin-bottom:10px;">
                            <div style="margin-bottom:6px;"><strong>Tipo:</strong> {{ $ficha->tipo_ejercicio ?? 'General' }}</div>
                            <div style="margin-bottom:6px;"><strong>Ejercicios:</strong> {{ $ficha->ejercicios->count() ?? 0 }}</div>
                            <div style="margin-bottom:6px;"><strong>Creada:</strong> {{ $ficha->created_at->format('d M Y') }}</div>
                        </div>

                        <div class="card-actions">
                            @if ($isYou)
                                <a class="btn btn-edit" href="{{ $fichaEditUrl }}" title="Editar publicación">Editar publicación</a>
                            @else
                                <a class="btn btn-download" href="/fichas/{{ $ficha->id }}/preview"
                                    target="_blank" title="Vista Previa">
                                    Vista Previa
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach

            {{-- Mostrar plantillas públicas como fichas --}}
            @php
                // Si el controlador no pasó $plantillas, obtener publicas aquí
                $plantillasList = $plantillas ?? \App\Models\Plantilla::where('public', true)->get();
            @endphp

            @foreach ($plantillasList as $plantilla)
                @php
                    // Resolver URL de imagen_preview (si ya es URL o si es path en storage)
                    $raw = $plantilla->imagen_preview ?? null;
                    if ($raw && filter_var($raw, FILTER_VALIDATE_URL)) {
                        $img = $raw;
                    } elseif ($raw) {
                        $img = \Illuminate\Support\Facades\Storage::url($raw);
                    } else {
                        $img = 'https://via.placeholder.com/420x240?text=Plantilla';
                    }

                    $tipo = $plantilla->tipo ?? 'Otros';
                @endphp

                <article class="card" data-id="{{ $plantilla->id }}" data-type="plantillas" data-tipo="{{ $tipo }}">
                    <div class="card-thumb" style="position:relative;">
                        <img src="{{ $img }}" alt="{{ $plantilla->nombre ?? 'Plantilla' }}">
                        <div class="badge badge--plantilla">Plantilla</div>
                    </div>

                    <div class="card-body">
                        <div class="meta">
                            <span class="tema">{{ ucfirst($tipo) }}</span>
                        </div>

                        <h3 class="title">{{ $plantilla->nombre ?? 'Sin título' }}</h3>

                        <div class="card-meta-info" style="font-size:13px;color:#6b7280;margin-bottom:10px;">
                            @if(!empty($plantilla->user))
                                <div><strong>Por:</strong> {{ optional($plantilla->user->persona)->nombre ?? $plantilla->user->name }}</div>
                            @endif
                            <div><strong>Tipo:</strong> {{ ucfirst($tipo) }}</div>
                        </div>

                        <div class="card-actions">
                            <button class="btn btn-duplicate" data-id="{{ $plantilla->id }}"
                                data-titulo="{{ $plantilla->nombre }}" title="Usar plantilla">
                                Usar plantilla
                            </button>

                            <a class="btn btn-download" href="{{ $plantilla->archivo ?? '#' }}"
                                data-id="{{ $plantilla->id }}" target="_blank" title="Descargar">
                                Descargar
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Duplicar plantilla (usar como plantilla)
                document.querySelectorAll('.btn-duplicate').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const sesionId = this.dataset.id;
                        const tituloSesion = this.dataset.titulo || 'Sesión sin título';

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: '📋 Usar como Plantilla',
                                html: `<div style="text-align: left; padding: 20px;">
                        <p><strong>¿Deseas usar esta sesión como plantilla?</strong></p>
                        <br>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #0066cc;">
                            <p><strong>📅 Sesión:</strong> ${tituloSesion}</p>
                            <p><strong>📋 Se creará:</strong> "${tituloSesion} (Plantilla usada)"</p>
                            <p><strong>📊 Se incluirán:</strong> todos los detalles, momentos y listas de cotejo</p>
                        </div>
                        <br>
                        <p style="color: #666; font-size: 14px;">
                            <i class="fas fa-info-circle"></i>
                            La nueva sesión será una copia editable que podrás modificar de inmediato.
                        </p>
                    </div>`,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: '<i class="fas fa-copy"></i> Sí, usar plantilla',
                                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                                confirmButtonColor: '#0066cc',
                                cancelButtonColor: '#6c757d',
                                reverseButtons: true,
                                showLoaderOnConfirm: true,
                                preConfirm: async () => {
                                    try {
                                        const response = await fetch(
                                            `/docente/sesion/${sesionId}/plantilla`, {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': token,
                                                    'Accept': 'application/json'
                                                }
                                            });

                                        const result = await response.json();

                                        if (!result.success) {
                                            throw new Error(result.message ||
                                                'Error al duplicar la sesión');
                                        }

                                        return result;
                                    } catch (error) {
                                        Swal.showValidationMessage(
                                            `⚠️ ${error.message}`);
                                        throw error;
                                    }
                                },
                                allowOutsideClick: () => !Swal.isLoading()
                            }).then((result) => {
                                if (result.isConfirmed && result.value?.redirect) {
                                    Swal.fire({
                                        title: '✅ Sesión creada',
                                        text: 'La nueva sesión se ha generado correctamente. Redirigiendo...',
                                        icon: 'success',
                                        showConfirmButton: false,
                                        timer: 1500,
                                        willClose: () => {
                                            window.location.href = result.value
                                                .redirect;
                                        }
                                    });
                                }
                            });
                        } else {
                            if (confirm(
                                    `¿Estás seguro de que quieres usar "${tituloSesion}" como plantilla?`
                                    )) {
                                const response = await fetch(
                                    `/docente/sesion/${sesionId}/plantilla`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token,
                                            'Accept': 'application/json'
                                        }
                                    });

                                const result = await response.json();
                                if (result.success) {
                                    window.location.href = result.redirect;
                                } else {
                                    alert('Error: ' + result.message);
                                }
                            }
                        }
                    });
                });
                // Descarga: si el enlace no está disponible, podemos intentar llamar al endpoint de descarga
                document.querySelectorAll('.btn-download').forEach(a => {
                    a.addEventListener('click', function(e) {
                        // si el href es '#' o está vacío, evitar comportamiento por defecto y llamar fetch
                        const href = this.getAttribute('href');
                        const id = this.dataset.id;
                        if (!href || href === '#') {
                            e.preventDefault();
                            fetch(`/sesiones/${id}/download`, {
                                    headers: {
                                        'X-CSRF-TOKEN': token,
                                        'Accept': 'application/json'
                                    }
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
                                .catch(err => {
                                    console.error(err);
                                    alert('Error al descargar.');
                                });
                        }
                    });
                });

                // Panel buttons: filtrar tarjetas por data-type
                document.querySelectorAll('.panel-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.panel-btn').forEach(b => b.classList.remove(
                            'active'));
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
    @endpush

</div>
