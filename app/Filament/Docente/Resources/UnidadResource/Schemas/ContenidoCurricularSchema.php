<?php

namespace App\Filament\Docente\Resources\UnidadResource\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TagsInput;

class ContenidoCurricularSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('📚 Contenido Curricular')
                ->description('Configura los cursos y competencias de tu unidad')
                ->icon('heroicon-o-academic-cap')
                ->schema([
                    Forms\Components\Builder::make('contenido')
                        ->label('Cursos')
                        ->blocks([
                            Forms\Components\Builder\Block::make('curso')
                                ->label(fn (?array $state = []): string => 
                                    \App\Models\Curso::find($state['curso_id'] ?? null)?->curso ?? 'Curso'
                                )
                                ->icon('heroicon-o-book-open')
                                ->schema([
                                    Forms\Components\Grid::make(1)
                                        ->schema([
                                            // Selector de curso
                                            Forms\Components\Select::make('curso_id')
                                                ->label('📖 Selecciona el Curso')
                                                ->options(
                                                    \App\Models\Curso::query()
                                                        ->orderBy('curso')
                                                        ->pluck('curso', 'id')
                                                )
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->placeholder('Buscar curso...')
                                                ->native(false)
                                                ->prefixIcon('heroicon-o-book-open')
                                                ->helperText('Selecciona el curso para trabajar'),

                                            // Competencias
                                            Forms\Components\Repeater::make('competencias')
                                                ->label('🎯 Competencias del Curso')
                                                ->schema([
                                                    Forms\Components\Select::make('competencia_id')
                                                        ->label('Competencia')
                                                        ->options(function (callable $get) {
                                                            $cursoId = $get('../../curso_id');
                                                            if (!$cursoId) return [];

                                                            $competencias = \App\Models\Competencia::where('curso_id', $cursoId)
                                                                ->pluck('nombre', 'id');

                                                            $todasCompetencias = $get('../../competencias') ?? [];
                                                            $competenciasSeleccionadas = collect($todasCompetencias)
                                                                ->pluck('competencia_id')
                                                                ->filter()
                                                                ->toArray();

                                                            return $competencias->reject(fn($_, $id) => in_array($id, $competenciasSeleccionadas));
                                                        })
                                                        ->reactive()
                                                        ->required()
                                                        ->searchable()
                                                        ->native(false)
                                                        ->placeholder('Seleccionar...')
                                                        ->afterStateUpdated(function (callable $set) {
                                                            $set('capacidades', []);
                                                            $set('desempenos', []);
                                                        })
                                                        ->helperText('Elige la competencia a desarrollar')
                                                        ->columnSpanFull(),

                                                    Forms\Components\Grid::make(2)->schema([
                                                        Forms\Components\Select::make('capacidades')
                                                        ->required()    
                                                        ->label('Capacidades')
                                                            ->multiple()
                                                            ->options(function (callable $get) {
                                                                $competenciaId = $get('competencia_id');
                                                                if (!$competenciaId) return [];

                                                                return \App\Models\Capacidad::where('competencia_id', $competenciaId)
                                                                    ->pluck('nombre', 'id');
                                                            })
                                                            ->reactive()
                                                            ->searchable()
                                                            ->placeholder('Seleccionar...')
                                                            ->preload()
                                                            ->native(false)
                                                            ->afterStateUpdated(function (callable $set) {
                                                                $set('desempenos', []);
                                                            }),

                                                        Forms\Components\Select::make('desempenos')
                                                            ->label('Desempeños')
                                                            ->required()
                                                            ->multiple()
                                                            ->options(function (callable $get) {
                                                                $competenciaId = $get('competencia_id');
                                                                $grado = $get('../../../../../grado');

                                                                if (!$competenciaId || !$grado) {
                                                                    return [];
                                                                }

                                                                $gradoLimpio = preg_replace('/[^0-9]/', '', $grado);

                                                                return \App\Models\Desempeno::whereHas('capacidad', function ($q) use ($competenciaId) {
                                                                    $q->where('competencia_id', $competenciaId);
                                                                })
                                                                    ->where('grado', 'LIKE', "%{$gradoLimpio}%")
                                                                    ->pluck('descripcion', 'id');
                                                            })
                                                            ->reactive()
                                                            ->searchable()
                                                            ->placeholder('Seleccionar...')
                                                            ->preload()
                                                            ->native(false),
                                                    ]),

                                                    Forms\Components\Section::make('📝 Evaluación')
                                                        ->description('Define cómo evaluarás esta competencia')
                                                        ->collapsible()
                                                        ->schema([
                                                            Forms\Components\Grid::make(2)->schema([
                                                                Forms\Components\Textarea::make('criterios')
                                                                    ->label('Criterios')
                                                                    ->required()
                                                                    ->rows(3)
                                                                    ->placeholder('¿Qué evaluarás?'),

                                                                Forms\Components\Textarea::make('evidencias')
                                                                    ->label('Evidencias')
                                                                    ->required()
                                                                    ->rows(3)
                                                                    ->placeholder('¿Qué entregarán?'),
                                                            ]),

                                                            Forms\Components\Select::make('instrumentos_predefinidos')
                                                                ->label('Instrumentos')
                                                                ->required()
                                                                ->multiple()
                                                                ->options([
                                                                    'Rúbrica' => 'Rúbrica',
                                                                    'Lista de cotejo' => 'Lista de cotejo',
                                                                    'Guía de observación' => 'Guía de observación',
                                                                    'Portafolio' => 'Portafolio',
                                                                    'Registro anecdótico' => 'Registro anecdótico',
                                                                    'Escala valorativa' => 'Escala valorativa',
                                                                    'Personalizado' => 'Personalizado'
                                                                ])
                                                                ->searchable()
                                                                ->live()
                                                                ->native(false)
                                                                ->placeholder('Seleccionar...')
                                                                ->columnSpanFull(),

                                                            TagsInput::make('instrumentos_personalizados')
                                                                ->label('Instrumentos Personalizados')
                                                                ->placeholder('Escribe y presiona Enter')
                                                                ->columnSpanFull()
                                                                ->hidden(fn(callable $get) => !in_array('Personalizado', $get('instrumentos_predefinidos') ?? [])),

                                                            Hidden::make('instrumentos')
                                                                ->dehydrated()
                                                                ->default([]),
                                                        ]),
                                                ])
                                                ->collapsible()
                                                ->itemLabel(fn (array $state): ?string => 
                                                    \App\Models\Competencia::find($state['competencia_id'])?->nombre ?? '✨ Nueva competencia'
                                                )
                                                ->cloneable()
                                                ->reorderable()
                                                ->addActionLabel('➕ Agregar Competencia')
                                                ->deleteAction(fn ($action) => $action->color('danger'))
                                                ->defaultItems(1),
                                        ])
                                        ->extraAttributes([
                                            'style' => 'background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); padding: 1.5rem; border-radius: 0.75rem; border: 2px solid #22c55e;'
                                        ]),
                                ])
                                ->columns(1),
                        ])
                        ->collapsible()
                        ->cloneable()
                        ->blockNumbers(false)
                        ->addActionLabel('➕ Agregar Otro Curso')
                        ->minItems(1)
                        ->default([
                            [
                                'type' => 'curso',
                                'data' => [
                                    'curso_id' => null,
                                    'competencias' => [
                                        [
                                            'competencia_id' => null,
                                            'capacidades' => [],
                                            'desempenos' => [],
                                            'criterios' => null,
                                            'evidencias' => null,
                                            'instrumentos_predefinidos' => [],
                                            'instrumentos_personalizados' => [],
                                            'instrumentos' => [],
                                        ]
                                    ]
                                ]
                            ]
                        ])
                        ->deleteAction(
                            fn ($action) => $action
                                ->requiresConfirmation()
                                ->modalHeading('¿Eliminar curso?')
                                ->modalDescription('Se eliminarán todas las competencias asociadas')
                        )
                        ->columnSpanFull(),
                ])
        ];
    }
}
