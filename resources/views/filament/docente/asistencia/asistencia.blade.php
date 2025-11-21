@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/main.js'])

<div 
    class="as-card"
    {{-- 1. Inicializamos Alpine con los datos existentes --}}
    x-data="{
        selectedDates: @js($existingDias ?? []),

        init() {
            console.log('Alpine iniciado correctamente. Fechas:', this.selectedDates);
            // Forzamos la actualización visual al cargar
            this.$nextTick(() => {
                this.updateVisuals();
            });
        },

        toggleDate(date) {
            // Lógica para añadir o quitar fecha del array
            if (this.selectedDates.includes(date)) {
                this.selectedDates = this.selectedDates.filter(d => d !== date);
            } else {
                this.selectedDates.push(date);
            }

            // 2. Mágia de Filament: Actualizamos el campo del formulario directamente
            // No necesitas buscar IDs ni componentes, $wire sabe dónde está.
            // Usualmente en formularios es 'data.campo'
            this.$wire.set('data.dias_no_clase', this.selectedDates);

            // Actualizamos las clases CSS (rojo/verde)
            this.updateVisuals();
        },

        updateVisuals() {
            // Buscamos todos los checkboxes dentro de este componente
            const checkboxes = this.$el.querySelectorAll('.no-class-checkbox');
            
            checkboxes.forEach(cb => {
                const date = cb.dataset.date;
                const isSelected = this.selectedDates.includes(date);
                
                // Sincronizar el check
                cb.checked = isSelected;

                // Sincronizar la clase de la celda padre
                const cell = cb.closest('td');
                if (cell) {
                    if (isSelected) {
                        cell.classList.add('no-class-cell');
                    } else {
                        cell.classList.remove('no-class-cell');
                    }
                }
            });
        }
    }"
>
    <div class="as-header">
        <div>
            <h2 class="as-title">Calendario de Asistencia</h2>
            <div class="as-sub">Semanas: <span id="weeks-count">{{ $weeksCount }}</span></div>
        </div>

        <div class="as-legend">
            <span class="legend-item"><span class="legend-dot legend-normal"></span> Clase</span>
            <span class="legend-item legend-item-no-class"><span class="legend-dot legend-no-class"></span> No clase</span>
        </div>
    </div>

    <div class="as-wrapper">
        <table class="as-table">
            <thead>
                <tr>
                    <th class="sticky-col">Semana</th>
                    <th>L</th>
                    <th>Ma</th>
                    <th>Mi</th>
                    <th>J</th>
                    <th>V</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($matrix as $i => $week)
                    <tr>
                        <td class="sticky-col week-number">{{ $i + 1 }}</td>

                        @foreach (['L', 'Ma', 'Mi', 'J', 'V'] as $dayKey)
                            @php $cell = $week[$dayKey]; @endphp

                            @if ($cell['date'])
                                <td class="day-cell" data-date="{{ $cell['date'] }}">
                                    <div class="day-box">
                                        <div class="day-num">
                                            {{ \Carbon\Carbon::parse($cell['date'])->format('d') }}
                                        </div>

                                        <label class="toggle">
                                            {{-- 3. Usamos @change de Alpine en lugar de onclick --}}
                                            <input 
                                                type="checkbox" 
                                                class="no-class-checkbox" 
                                                data-date="{{ $cell['date'] }}"
                                                @change="toggleDate('{{ $cell['date'] }}')"
                                            >
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </td>
                            @else
                                <td class="empty-cell">—</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="students-panel">
        @php
            $listaEst = $estudiantes ?? ($students ?? []);
        @endphp

        <div style="display:flex;gap:16px;align-items:center;margin-bottom:8px;">
            <div><strong>Docente:</strong> {{ $docenteNombre ?? (\Illuminate\Support\Facades\Auth::user()->name ?? \Illuminate\Support\Facades\Auth::user()->email ?? '—') }}</div>
            @php
                $aulaInfo = null;
                try {
                    $añoActual = \App\Models\Año::whereDate('fecha_inicio','<=',now())->whereDate('fecha_fin','>=',now())->first();
                    $ua = \App\Models\usuario_aula::where('user_id', \Illuminate\Support\Facades\Auth::id())->when($añoActual, fn($q)=> $q->where('año_id',$añoActual->id))->first();
                    if ($ua?->aula_id) {
                        $a = \App\Models\Aula::find($ua->aula_id);
                        if ($a) $aulaInfo = trim(($a->grado ?? '') . ' / ' . ($a->seccion ?? ''), ' / ');
                    }
                } catch (\Throwable $e) {
                    $aulaInfo = null;
                }
            @endphp
            <div><strong>Aula:</strong> {{ $gradoSeccion ?? $aulaInfo ?? '—' }}</div>
        </div>

        <strong>Estudiantes del aula</strong>

        @if (!empty($listaEst) && is_iterable($listaEst) && count($listaEst) > 0)
            <ol class="students-list-vertical">
                @foreach ($listaEst as $i => $s)
                    @php
                        if (is_array($s)) {
                            $nombre = $s['nombre'] ?? trim((($s['nombres'] ?? '') . ' ' . ($s['apellidos'] ?? '')));
                        } elseif (is_object($s)) {
                            $nombre = trim(($s->nombres ?? $s->nombre ?? '') . ' ' . ($s->apellidos ?? ''));
                        } else {
                            $nombre = (string)$s;
                        }
                    @endphp
                    <li>{{ $nombre }}</li>
                @endforeach
            </ol>
        @else
            <div class="no-students">No se encontraron estudiantes (asegure que el docente tiene aula_id).</div>
        @endif
    </div>
</div>
<style>
    /* CARD PRINCIPAL */
    .as-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        border: 1px solid #eef2f7;
        font-family: Inter, system-ui;
        margin-bottom: 12px;
    }

    .as-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .as-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .as-sub {
        font-size: 0.95rem;
        color: #64748b;
    }

    /* LEYENDA */
    .as-legend {
        display: flex;
        gap: 16px;
        font-size: 0.9rem;
        color: #374151;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }

    .legend-normal {
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
    }

    .legend-no-class {
        background: #e8f1ff;
        border: 1px solid #c9dcff;
    }

    /* destacado para "No clase" */
    .legend-item-no-class {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #0b4dbd; /* texto más visible */
        font-weight: 700;
        text-shadow: 0 1px 0 rgba(255,255,255,0.02);
    }

    .legend-dot.legend-no-class {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: linear-gradient(180deg,#1e40af 0%,#2563eb 100%);
        border: 1px solid rgba(30,64,175,0.9);
        box-shadow: 0 6px 18px rgba(37,99,235,0.12);
    }

    /* mantener contraste en modo oscuro */
    @media (prefers-color-scheme: dark) {
        .legend-item-no-class { color: #dbeafe; }
        .legend-dot.legend-no-class { box-shadow: 0 6px 18px rgba(99,102,241,0.12); border-color: rgba(99,102,241,0.9); }
    }

    /* TABLA */
    .as-wrapper {
        overflow-x: auto;
        border-radius: 12px;
    }

    .as-table {
        width: 100%;
        border-collapse: collapse; /* líneas continuas oscuras */
        border-spacing: 0;
        min-width: 720px;
    }

    /* bordes oscuros para la tabla de calendario */
    .as-table, .as-table th, .as-table td {
        border: 1px solid #0f172a;
    }
    .as-table thead th {
        background: linear-gradient(180deg,#e6f0ff,#eef7ff);
        padding: 12px;
        font-weight: 700;
        color: #071030;
        border-bottom: 2px solid #0b1220;
        text-align: center;
    }
    .as-table td {
        padding: 12px 10px;
        text-align: center;
        background: #ffffff;
        color: #0f172a;
    }
    .as-table tbody tr:nth-child(even) td { background:#fbfdff; }
    .as-table tbody tr:hover td { background:#f3f9ff; }

    .sticky-col {
        position: sticky;
        left: 0;
        background: #ffffff;
        border-right: 1px solid #0f172a;
        z-index: 3;
    }

    /* CELDAS */
    .day-cell {
        transition: background 0.2s;
    }

    .day-cell:hover {
        background: #f8fbff;
    }

    .empty-cell {
        color: #9ca3af;
    }

    /* DÍA */
    .day-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

    .day-num {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #f1f5f9;
        color: #0f172a;
        font-weight: 600;
        border: 1px solid #e2e8f0;
    }

    /* ESTILO CUANDO ES NO CLASE */
    .no-class-cell .day-num {
        background: #fff8dc;
        border-color: #f5d488;
        color: #5a3410;
    }
    /* si quieres estados futuros (Falta/Justificada), puedes añadir clases así:
       td.status-F .day-num { background:#fee2e2; border-color:#fca5a5; color:#7f1d1d; }
       td.status-J .day-num { background:#dcfce7; border-color:#bbf7d0; color:#065f46; }
       td.status-A .day-num { background:#ecfdf5; border-color:#bbf7d0; color:#065f46; }
    */

    /* TOGGLE MODERNO */
    .toggle {
        position: relative;
        width: 46px;
        height: 24px;
        display: inline-block;
    }

    .toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #d1d5db;
        border-radius: 999px;
        transition: .25s;
    }

    .toggle-slider:before {
        content: "";
        position: absolute;
        width: 18px;
        height: 18px;
        background: white;
        border-radius: 50%;
        top: 3px;
        left: 4px;
        transition: .25s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, .15);
    }

    .toggle input:checked+.toggle-slider {
        background: #3b82f6;
    }

    .toggle input:checked+.toggle-slider:before {
        transform: translateX(20px);
    }

    /* LISTA ESTUDIANTES */
    .students-panel {
        margin-top: 14px;
    }

    .students-list {
        list-style: none;
        padding: 0;
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .students-list li {
        background: #f8fafc;
        padding: 6px 12px;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        font-size: 0.95rem;
        color: #0f172a;
    }

    {{-- Nuevo CSS para lista vertical numerada --}}
    .students-list-vertical {
        margin-top:10px;
        padding-left: 1.2rem;
        display: block;
        list-style-position: inside;
        column-count: 1; /* cambiar a 2 para mostrar en dos columnas */
        gap: 12px;
    }
    @media (min-width: 900px) {
        /* si quieres mostrar en dos columnas en pantallas grandes, descomenta: */
        /* .students-list-vertical { column-count: 2; } */
    }

    @media (max-width:780px) {
        .as-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
    }
</style>
