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
                            ['competencia_id' => null, 'capacidades' => [], 'desempenos' => [], 'criterios' => '', 'instrumentos_predefinidos' => [], 'instrumentos_personalizados' => []],
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
                    ->itemLabel(fn(array $state): ?string => $state['competencia_id'] ? self::getCompetenciaName($state['competencia_id']) : 'Nueva competencia'),

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
            Forms\Components\Textarea::make('criterios')
                ->label('Criterios de evaluación')
                ->rows(2)
                ->placeholder('Ej: Resuelve correctamente problemas con fracciones...')
                ->helperText('¿Cómo sabrás si el estudiante logró el desempeño?')
                ->columnSpan('full'),

            // Fila 4: Instrumentos
            Forms\Components\Section::make('Instrumentos de evaluación')
                ->description('¿Con qué herramientas evaluarás?')
                ->schema([
                    Forms\Components\Select::make('instrumentos_predefinidos')
                        ->label('Selecciona instrumentos')
                        ->multiple()
                        ->options([
                            'Rúbrica' => 'Rúbrica',
                            'Lista de cotejo' => 'Lista de cotejo',
                            'Guía de observación' => 'Guía de observación',
                            'Portafolio' => 'Portafolio',
                            'Registro anecdótico' => 'Registro anecdótico',
                            'Escala valorativa' => 'Escala valorativa',
                            'Personalizado' => 'Personalizado',
                        ])
                        ->searchable()
                        ->reactive()
                        ->columnSpan('full'),

                    TagsInput::make('instrumentos_personalizados')
                        ->label('Añade tus propios instrumentos')
                        ->placeholder('Escribe y presiona Enter')
                        ->reactive()
                        ->visible(fn($get) => in_array('Personalizado', (array) ($get('instrumentos_predefinidos') ?? [])))
                        ->columnSpan('full'),
                ])
                ->columns(1)
                ->columnSpan('full')
                ->collapsible(),
        ];
    }
}