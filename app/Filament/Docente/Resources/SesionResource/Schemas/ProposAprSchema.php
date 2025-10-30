<?php

namespace App\Filament\Docente\Resources\SesionResource\Schemas;

use App\Models\Capacidad;
use App\Models\Competencia;
use App\Models\Desempeno;
use Filament\Forms;
use Filament\Forms\Components\TagsInput;
use Illuminate\Support\Facades\Auth;

class ProposAprSchema
{
    public static function schema(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Propósitos de Aprendizaje')
            ->description('Define las competencias, capacidades y desempeños que trabajarás en esta sesión')
            ->schema([
                // Paso 1: Seleccionar Curso
                Forms\Components\Select::make('curso_id')
                    ->label('1️⃣ Selecciona el curso')
                    ->reactive()
                    ->options(self::getCursos())
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('competencias', [
                            ['competencia_id' => null, 'capacidades' => [], 'desempenos' => [], 'criterios' => [], 'instrumentos_predefinidos' => [], 'instrumentos_personalizados' => []],
                        ]);
                        $set('aula_curso_id', self::getAulaCursoId($state));
                    })
                    ->searchable()
                    ->placeholder('Seleccione curso...')
                    ->columnSpan('full')
                    ->required(),

                // Paso 2: Añadir Competencias
                Forms\Components\Repeater::make('competencias')
                    ->label('2️⃣ Competencias')
                    ->createItemButtonLabel('+ Agregar competencia')
                    ->schema(self::getCompetenciasSchema())
                    ->columns(1)
                    ->columnSpan('full')
                    ->visible(fn($get) => (bool) $get('curso_id'))
                    ->itemLabel(function (array $state): ?string {
                        $competencia = $state['competencia_id'] ? self::getCompetenciaName($state['competencia_id']) : 'Nueva competencia';
                        $tituloLista = $state['lista_cotejo_titulo'] ?? null;
                        $generaLista = !empty($state['generar_lista_cotejo']);
                        // Si se genera lista, mostrar título junto a la competencia
                        if ($generaLista && $tituloLista) {
                            return "{$competencia} — «{$tituloLista}»";
                        }
                        return $competencia;
                    }),
                // Paso 3: Evidencias Generales
                Forms\Components\Textarea::make('evidencias')
                    ->label('3️⃣ Evidencias de la sesión')
                    ->rows(3)
                    ->placeholder('¿Cómo verificarás que los estudiantes aprendieron?')
                    ->helperText('Describe las evidencias observables del aprendizaje')
                    ->columnSpan('full'),
            ])
            ->columnSpan('full');
    }

    private static function getCursos(): array
    {
        $user = Auth::user();
        if (!$user) return [];

        $aulaIds = $user->usuario_aulas()->pluck('aula_id')->toArray();
        if (empty($aulaIds)) return [];

        $cursoIds = \App\Models\AulaCurso::whereIn('aula_id', $aulaIds)
            ->pluck('curso_id')
            ->unique()
            ->toArray();

        return \App\Models\Curso::whereIn('id', $cursoIds)
            ->orderBy('curso')
            ->pluck('curso', 'id')
            ->toArray();
    }

    private static function getAulaCursoId($cursoId): ?int
    {
        $user = Auth::user();
        if (!$user) return null;

        $usuarioAula = $user->usuario_aulas()->latest()->first();
        if (!$usuarioAula) return null;

        $aulaCurso = \App\Models\AulaCurso::where('aula_id', $usuarioAula->aula_id)
            ->where('curso_id', $cursoId)
            ->first();

        return $aulaCurso?->id;
    }

    private static function getCompetenciaName($competenciaId): string
    {
        return Competencia::find($competenciaId)?->nombre ?? 'Competencia';
    }

    private static function getCompetenciasSchema(): array
    {
        return [
            // Fila 1: Competencia
            Forms\Components\Select::make('competencia_id')
                ->label('Competencia')
                ->required()
                ->reactive()
                ->searchable()
                ->options(function (callable $get) {
                    $cursoId = $get('../../curso_id');
                    if (!$cursoId) return [];
                    return Competencia::where('curso_id', $cursoId)
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id')
                        ->toArray();
                })
                ->afterStateUpdated(function (callable $set) {
                    $set('capacidades', []);
                    $set('desempenos', []);
                })
                ->columnSpan('full'),

            // Fila 2: Capacidades y Desempeños
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('capacidades')
                        ->label('Capacidades')
                        ->multiple()
                        ->reactive()
                        ->preload()
                        ->searchable()
                        ->options(function (callable $get) {
                            $competenciaId = $get('competencia_id');
                            if (!$competenciaId) return [];
                            return Capacidad::where('competencia_id', $competenciaId)
                                ->orderBy('nombre')
                                ->pluck('nombre', 'id')
                                ->toArray();
                        })
                        ->placeholder('Seleccione capacidades')
                        ->columnSpan(1),

                    Forms\Components\Select::make('desempenos')
                        ->label('Desempeños esperados')
                        ->multiple()
                        ->options(function (callable $get) {
                            $competenciaId = $get('competencia_id');
                            if (!$competenciaId) return [];

                            $user = Auth::user();
                            $grado = null;
                            if ($user) {
                                $usuarioAula = $user->usuario_aulas()->with('aula')->latest()->first();
                                $grado = $usuarioAula?->aula?->grado;
                            }
                            if (!$grado) return [];

                            $gradoLimpio = preg_replace('/[^0-9]/', '', $grado);
                            $capIds = Capacidad::where('competencia_id', $competenciaId)->pluck('id')->toArray();
                            if (empty($capIds)) return [];

                            return Desempeno::whereIn('capacidad_id', $capIds)
                                ->where('grado', 'LIKE', "%{$gradoLimpio}%")
                                ->orderBy('descripcion')
                                ->pluck('descripcion', 'id')
                                ->toArray();
                        })
                        ->reactive()
                        ->searchable()
                        ->placeholder('Se filtran por grado')
                        ->columnSpan(1),
                ])
                ->columnSpan('full'),

            // Fila 3: Criterios de Evaluación
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\TextInput::make('criterio_input')
                        ->label('Añadir criterio')
                        ->placeholder('Escribe un criterio y pulsa +')
                        ->columnSpan(2)
                        ->reactive()
                        ->suffixAction(
                            \Filament\Forms\Components\Actions\Action::make('agregar_criterio')
                                ->label('+')
                                ->icon('heroicon-o-plus')
                                ->action(function (callable $get, callable $set) {
                                    $input = trim($get('criterio_input') ?? '');
                                    if ($input === '') return;
                                    $current = (array) ($get('criterios') ?? []);
                                    // asegurar que current es array incluso si venía como string
                                    if (!is_array($current)) {
                                        $current = $current === '' ? [] : array_map('trim', explode("\n", (string) $current));
                                    }
                                    if (!in_array($input, $current, true)) {
                                        $current[] = $input;
                                    }
                                    $set('criterios', array_values($current));
                                    $set('criterios_edit', implode("\n", array_map(fn($s) => '- ' . $s, $current)));
                                    $set('criterio_input', '');
                                })
                        ),
                ])->columnSpan('full'),

            // TagsInput: inicializar y forzar estado array al hidratar
            Forms\Components\TagsInput::make('criterios')
                ->label('Criterios (presiona Enter para añadir; eliminar con X)')
                ->placeholder('Escribe y presiona Enter o usa el campo "Añadir criterio"')
                ->reactive()
                ->default([]) // asegurar array por defecto
                ->visible(fn($get) => !empty($get('criterios'))) // <-- oculto hasta que haya al menos un criterio
                ->afterStateHydrated(function ($state, callable $set) {
                    if ($state === null || $state === '') {
                        $set('criterios', []);
                    } elseif (!is_array($state)) {
                        $arr = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $state)), fn($v) => $v !== ''));
                        $set('criterios', $arr);
                    }
                })
                ->afterStateUpdated(function ($state, callable $set) {
                    $items = array_values(array_filter(array_map('trim', (array) $state), fn($v) => $v !== ''));
                    $set('criterios', $items);
                    $set('criterios_edit', implode("\n", array_map(fn($s) => '- ' . $s, $items)));
                })
                ->columnSpan('full'),

            // Fila 4: Instrumentos
            Forms\Components\Section::make('Instrumentos de evaluación')
                ->description('¿Con qué herramientas evaluarás?')
                ->schema([
                    Forms\Components\Select::make('instrumentos_predefinidos')
                        ->label('Selecciona instrumento')
                        ->options([
                            'Rúbrica' => 'Rúbrica',
                            'Lista de cotejo' => 'Lista de cotejo',
                            'Guía de observación' => 'Guía de observación',
                            'Portafolio' => 'Portafolio',
                            'Escala valorativa' => 'Escala valorativa',
                            'Personalizado' => 'Personalizado',
                        ])
                        ->searchable()
                        ->reactive()
                        ->placeholder('Selecciona un instrumento...')
                        ->columnSpan('full'),

                    TagsInput::make('instrumentos_personalizados')
                        ->label('Añade tus propios instrumentos')
                        ->placeholder('Escribe y presiona Enter')
                        ->reactive()
                        ->visible(fn($get) => $get('instrumentos_predefinidos') === 'Personalizado')
                        ->columnSpan('full'),

                    // Checkbox visible sólo si se seleccionó 'Lista de cotejo'
                    Forms\Components\Checkbox::make('generar_lista_cotejo')
                        ->label('¿Deseas generar la lista de cotejo?')
                        ->reactive()
                        ->default(false)
                        ->visible(fn($get) => $get('instrumentos_predefinidos') === 'Lista de cotejo')
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if ($state) {
                                // si se activa, asegurar que título y niveles existen
                                if (trim((string) ($get('lista_cotejo_niveles') ?? '')) === '') {
                                    $set('lista_cotejo_niveles', 'Logrado, En proceso, Destacado');
                                }
                            } else {
                                // al desactivar borramos título y niveles para evitar valores sobrantes
                                $set('lista_cotejo_titulo', null);
                                // si quieres conservar niveles aunque se desactive, comenta la siguiente línea
                                $set('lista_cotejo_niveles', null);
                            }
                        })
                        ->columnSpan('full'),

                    // Campo de título: aparece cuando se seleccionó "Lista de cotejo"
                    Forms\Components\TextInput::make('lista_cotejo_titulo')
                        ->label('Título para la Lista de cotejo')
                        ->placeholder('Ej: Lista de cotejo - Actividad 1')
                        ->reactive()
                        ->visible(fn($get) => $get('instrumentos_predefinidos') === 'Lista de cotejo')
                        ->disabled(fn($get) => ! (bool) ($get('generar_lista_cotejo') ?? false))
                        ->default(null)
                        ->columnSpan('full'),

                    // Niveles: precargados desde el inicio y no editables
                    Forms\Components\TextInput::make('lista_cotejo_niveles')
                        ->label('Niveles (separados por coma)')
                        ->placeholder('Ej: Logrado, En proceso, No logrado')
                        ->visible(fn($get) => $get('instrumentos_predefinidos') === 'Lista de cotejo')
                        ->helperText('Valores por defecto: Logrado, En proceso, Destacado')
                        ->default('Logrado, En proceso, Destacado')
                        ->reactive()
                        ->disabled() // no editable desde el inicio
                        ->required(fn($get) => (bool) ($get('generar_lista_cotejo') ?? false))
                        ->columnSpan('full'),
                ])
                ->columns(1)
                ->columnSpan('full')
                ->collapsible(),
        ];
    }
}
