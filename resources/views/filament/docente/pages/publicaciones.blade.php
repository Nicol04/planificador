<div>

    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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

        <!-- Panel de botones + buscador y selector de grados en la misma fila -->
        <div style="display: flex; align-items: center; gap: 18px; margin-bottom: 22px; flex-wrap: wrap;">
            <nav class="panel" style="flex-shrink:0;">
                <button class="panel-btn active" data-filter="all"><i class="fas fa-th-large"></i> Todos</button>
                <button class="panel-btn" data-filter="unidades"><i class="fas fa-layer-group"></i> Unidades</button>
                <button class="panel-btn" data-filter="sesiones"><i class="fas fa-chalkboard-teacher"></i> Sesiones</button>
                <button class="panel-btn" data-filter="fichas"><i class="fas fa-file-alt"></i> Fichas</button>
                <button class="panel-btn" data-filter="plantillas"><i class="fas fa-puzzle-piece"></i> Plantillas</button>
            </nav>
            <div style="display: flex; gap: 12px; align-items: center; flex: 1; min-width: 320px;">
                <div style="position: relative; flex: 1; max-width: 320px;">
                    <input
                        type="text"
                        id="search-docente"
                        placeholder="Buscar por docente..."
                        style="width: 100%; padding: 10px 38px 10px 14px; border-radius: 8px; border: 1.5px solid #bfc9d1; background: #f8fafc; font-size: 15px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); transition: border 0.2s;"
                        onfocus="this.style.borderColor='#2563eb'"
                        onblur="this.style.borderColor='#bfc9d1'">
                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
                <div style="position: relative;">
                    <select
                        id="filter-grado"
                        style="padding: 10px 36px 10px 14px; border-radius: 8px; border: 1.5px solid #bfc9d1; background: #f8fafc; font-size: 15px; min-width: 170px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); transition: border 0.2s;"
                        onfocus="this.style.borderColor='#2563eb'"
                        onblur="this.style.borderColor='#bfc9d1'">
                        <option value="">Todos los grados</option>
                        @php
                        // Listar todos los grados únicos de la tabla aulas
                        $grados = \App\Models\Aula::query()
                        ->select('grado')
                        ->distinct()
                        ->orderBy('grado')
                        ->pluck('grado')
                        ->filter()
                        ->values();
                        @endphp
                        @foreach($grados as $grado)
                        <option value="{{ $grado }}">{{ $grado }} Primaria</option>
                        @endforeach
                    </select>
                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 16px; pointer-events: none;">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </div>
            </div>
        </div>

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
                            {{ $sesion->tiempo_estimado ?? '' }} minutos
                        </div>
                    </div>

                    <div class="card-actions-rows">
                        <div class="card-actions">
                            @if ($isYou)
                            <a class="btn btn-edit" href="{{ $sesionEditUrl }}" title="Editar publicación">
                                <i class="fas fa-pencil-alt"></i> Editar
                            </a>
                            @else
                            <button class="btn btn-duplicate" data-id="{{ $sesion->id }}"
                                data-titulo="{{ $sesion->titulo }}" title="Usar plantilla">
                                <i class="fas fa-copy"></i> Usar plantilla
                            </button>
                            @endif

                            
                        </div>

                        <div class="card-actions second-row">
                            <button class="btn btn-preview" onclick="abrirModalPreviaSesion({{ $sesion->id }})" title="Previsualizar">
                                <i class="fas fa-eye"></i> Previsualizar
                            </button>
                        </div>

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
                        <a class="btn btn-edit" href="{{ $unidadEditUrl }}" title="Editar archivo">
                            <i class="fas fa-pencil-alt"></i> Editar
                        </a>
                        @else
                        <button class="btn btn-duplicate" data-id="{{ $unidad->id }}"
                            title="Usar plantilla">
                            <i class="fas fa-copy"></i> Usar plantilla
                        </button>
                        @endif

                        <a class="btn btn-download" href="{{ $unidad->download_url ?? '#' }}"
                            data-id="{{ $unidad->id }}" target="_blank" title="Descargar">
                            <i class="fas fa-download"></i> Descargar
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
                        <a class="btn btn-edit" href="{{ $fichaEditUrl }}" title="Editar publicación">
                            <i class="fas fa-pencil-alt"></i> Editar
                        </a>
                        @else
                        <a class="btn btn-view" href="/fichas/{{ $ficha->id }}/preview"
                            target="_blank" title="Vista Previa">
                            <i class="fas fa-eye"></i> Vista Previa
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
                        <button class="btn btn-primary btn-use-template"
                            data-id="{{ $plantilla->id }}"
                            title="Usar plantilla">
                            <i class="fas fa-magic"></i> Usar
                        </button>
                        <button class="btn btn-download btn-view-template"
                            data-image="{{ $img }}"
                            data-name="{{ $plantilla->nombre ?? 'Plantilla' }}"
                            title="Ver plantilla">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                        
                    </div>
                </div>
            </article>
            @endforeach
        </div>

    </div>

    {{-- Modal personalizado para vista previa de sesión --}}
    <div id="modalPreviaSesion"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 10px; padding: 30px; max-width: 500px; text-align: center;">
            <h3 style="margin-bottom: 20px; color: #0066cc;">📄 Vista Previa de la Sesión</h3>
            <p style="margin-bottom: 30px; color: #666;">Seleccione el formato para previsualizar:</p>
            <div style="display: flex; gap: 15px; justify-content: center; margin-bottom: 20px;">
                <button onclick="abrirVistaPreviaSesion('vertical')"
                    style="background: #0066cc; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    📄 Vista Previa Vertical
                </button>
                <button onclick="abrirVistaPreviaSesion('horizontal')"
                    style="background: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    📄 Vista Previa Horizontal
                </button>
            </div>
            <button onclick="cerrarModalPreviaSesion()"
                style="background: #6c757d; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer;">
                ❌ Cancelar
            </button>
        </div>
    </div>

    @push('scripts')
    <script>
        // Variable global para almacenar el ID de la sesión actual
        let sesionIdActual = null;

        // Función para abrir el modal de vista previa
        function abrirModalPreviaSesion(sesionId) {
            sesionIdActual = sesionId;
            document.getElementById('modalPreviaSesion').style.display = 'flex';
        }

        // Función para cerrar el modal de vista previa
        function cerrarModalPreviaSesion() {
            document.getElementById('modalPreviaSesion').style.display = 'none';
            sesionIdActual = null;
        }

        // Función para abrir la vista previa con orientación seleccionada
        function abrirVistaPreviaSesion(orientacion) {
            if (sesionIdActual) {
                const url = `/sesiones/${sesionIdActual}/vista-previa?orientacion=${orientacion}`;
                window.open(url, 'vistaPreviaSesion', 'width=1200,height=800,scrollbars=yes,resizable=yes');
                cerrarModalPreviaSesion();
            }
        }

        // Función para descargar Word de sesión
        function descargarWordSesion(sesionId, orientacion) {
            const url = `/sesiones/${sesionId}/previsualizar?orientacion=${orientacion}`;
            const link = document.createElement('a');
            link.href = url;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '📥 Descarga iniciada',
                    text: 'El documento se está generando...',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }

        // Define la URL de creación de asistencia para usar en la función
        const createUrl = @json(\App\Filament\Docente\Resources\AsistenciaResource::getUrl('create'));

        document.querySelectorAll('.btn-use-template').forEach(btn => {
            btn.addEventListener('click', function() {
                const plantillaId = this.dataset.id;
                const nombre = this.closest('div').querySelector('h3')?.innerText ?? `#${plantillaId}`;
                confirmarUsoPlantilla(plantillaId, nombre);
            });
        });

        function confirmarUsoPlantilla(plantillaId, nombrePlantilla) {
            let targetUrl;
            try {
                const urlObj = new URL(createUrl, window.location.origin);
                urlObj.searchParams.set('plantilla_id', plantillaId);
                targetUrl = urlObj.toString();
            } catch (e) {
                targetUrl = createUrl + (createUrl.includes('?') ? '&' : '?') + 'plantilla_id=' + encodeURIComponent(plantillaId);
            }

            if (typeof Swal !== 'undefined') {
                const title = '🌐 Usar plantilla';
                const htmlMessage = `<p>¿Deseas usar la plantilla <strong>${nombrePlantilla}</strong> para crear una nueva asistencia?</p>
                        <p class="text-muted">Se abrirá el formulario de creación con la plantilla seleccionada.</p>`;

                Swal.fire({
                    title: title,
                    html: htmlMessage,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check-circle"></i> Sí, usar plantilla',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#0066cc',
                    cancelButtonColor: '#6c757d',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve) => {
                            setTimeout(() => {
                                window.location.href = targetUrl;
                                resolve();
                            }, 400);
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                });
            } else {
                if (confirm(`Usar plantilla "${nombrePlantilla}" para crear asistencia?`)) {
                    window.location.href = targetUrl;
                }
            }
        }

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

            // NUEVO: Filtro por grado y búsqueda por docente
            const searchInput = document.getElementById('search-docente');
            const gradoSelect = document.getElementById('filter-grado');

            function normalize(str) {
                return (str || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }

            function filterCards() {
                const search = normalize(searchInput.value);
                const grado = gradoSelect.value;
                // Obtener filtro de tipo activo
                const activeType = document.querySelector('.panel-btn.active')?.dataset.filter || 'all';

                document.querySelectorAll('.grid .card').forEach(card => {
                    let show = true;

                    // Filtrar por tipo (panel)
                    if (activeType !== 'all' && (card.dataset.type !== activeType)) {
                        show = false;
                    }

                    // Filtrar por grado
                    if (show && grado) {
                        // Buscar grado en los elementos visibles de la tarjeta
                        let cardGrado = '';
                        // Sesiones
                        if (card.dataset.type === 'sesiones') {
                            const tema = card.querySelector('.tema')?.innerText || '';
                            cardGrado = tema.split('·')[0].trim();
                        }
                        // Unidades
                        else if (card.dataset.type === 'unidades') {
                            const tema = card.querySelector('.tema')?.innerText || '';
                            cardGrado = tema.split('·')[0].trim();
                        }
                        // Fichas
                        else if (card.dataset.type === 'fichas') {
                            cardGrado = card.querySelector('.tema')?.innerText.trim() || '';
                        }
                        // Plantillas: no filtrar por grado
                        if (card.dataset.type !== 'plantillas' && normalize(cardGrado) !== normalize(grado)) {
                            show = false;
                        }
                    }

                    // Filtrar por búsqueda de docente
                    if (show && search) {
                        let docente = '';
                        // Sesiones y fichas
                        if (card.querySelector('.docente')) {
                            docente = card.querySelector('.docente').innerText || '';
                        }
                        // Unidades: buscar en profesores responsables
                        else if (card.dataset.type === 'unidades') {
                            docente = Array.from(card.querySelectorAll('.card-meta-info div'))
                                .map(d => d.innerText).join(' ');
                        }
                        // Plantillas: buscar en "Por:"
                        else if (card.dataset.type === 'plantillas') {
                            docente = card.querySelector('.card-meta-info')?.innerText || '';
                        }
                        if (!normalize(docente).includes(search)) {
                            show = false;
                        }
                    }

                    card.style.display = show ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterCards);
            gradoSelect.addEventListener('change', filterCards);

            // Integrar con filtro de tipo (panel)
            document.querySelectorAll('.panel-btn').forEach(btn => {
                btn.addEventListener('click', filterCards);
            });

            // Handler para botón Ver plantilla
            document.querySelectorAll('.btn-view-template').forEach(btn => {
                btn.addEventListener('click', function() {
                    const src = this.dataset.image || '';
                    const name = this.dataset.name || '';
                    const modal = document.getElementById('previewModalPlantilla');
                    const img = document.getElementById('previewImagePlantilla');
                    const title = document.getElementById('previewTitlePlantilla');
                    const backdrop = modal ? modal.querySelector('.preview-modal-backdrop') : null;
                    
                    if (img) {
                        img.src = src;
                        img.alt = name;
                    }
                    if (title) title.textContent = name;
                    if (modal) {
                        modal.classList.remove('hidden');
                        modal.setAttribute('aria-hidden', 'false');
                    }
                    if (backdrop) backdrop.style.display = 'flex';
                });
            });

            // Handler para cerrar modal de preview
            const closePreviewBtn = document.getElementById('previewClosePlantilla');
            if (closePreviewBtn) {
                closePreviewBtn.addEventListener('click', function() {
                    const modal = document.getElementById('previewModalPlantilla');
                    if (!modal) return;
                    const backdrop = modal.querySelector('.preview-modal-backdrop');
                    const img = document.getElementById('previewImagePlantilla');
                    modal.classList.add('hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    if (backdrop) backdrop.style.display = 'none';
                    if (img) img.src = '';
                });
            }

            // Click fuera del contenido -> cerrar
            const previewModal = document.getElementById('previewModalPlantilla');
            if (previewModal) {
                const backdrop = previewModal.querySelector('.preview-modal-backdrop');
                if (backdrop) {
                    backdrop.addEventListener('click', function(e) {
                        if (e.target === backdrop) {
                            const img = document.getElementById('previewImagePlantilla');
                            previewModal.classList.add('hidden');
                            previewModal.setAttribute('aria-hidden', 'true');
                            backdrop.style.display = 'none';
                            if (img) img.src = '';
                        }
                    });
                }
            }
        });
    </script>
    @endpush

    {{-- Modal para previsualización de plantilla --}}
    <div id="previewModalPlantilla" class="hidden" aria-hidden="true">
        <div class="preview-modal-backdrop" role="dialog" aria-modal="true" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:10000; align-items:center; justify-content:center; padding:20px;">
            <div style="max-width:1100px; width:100%; max-height:90vh; overflow:auto; border-radius:12px; background:#fff; padding:16px; position:relative; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <button id="previewClosePlantilla" style="position:absolute; right:12px; top:12px; background:#111; color:#fff; border:none; padding:8px 12px; border-radius:8px; cursor:pointer; font-weight:600; z-index:10;" aria-label="Cerrar preview">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                <div style="padding:8px;">
                    <h3 id="previewTitlePlantilla" style="margin-bottom:12px; font-weight:700; font-size:1.1rem; color:#111827;"></h3>
                    <img id="previewImagePlantilla" style="width:100%; height:auto; display:block; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15);" src="" alt="Previsualización">
                </div>
            </div>
        </div>
    </div>

</div>