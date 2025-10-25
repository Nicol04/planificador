<?php

namespace App\Filament\Docente\Resources;

use App\Filament\Docente\Resources\UnidadResource\Pages;
use App\Filament\Docente\Resources\UnidadResource\RelationManagers;
use App\Models\Unidad;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class UnidadResource extends Resource
{
    protected static ?string $model = Unidad::class;
    protected static ?string $label = 'Unidades';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // 🟦 SECCIÓN 1: DATOS GENERALES DE LA UNIDAD
            Forms\Components\Section::make('Datos Generales de la Unidad')
                ->schema([
                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre de la Unidad')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\DatePicker::make('fecha_inicio')
                            ->label('Fecha de inicio')
                            ->required(),
                        Forms\Components\DatePicker::make('fecha_fin')
                            ->label('Fecha de fin')
                            ->required(),
                    ]),

                    Forms\Components\Select::make('grado')
                        ->label('Grado')
                        ->options(function () {
                            // Obtener el grado del usuario autenticado
                            $user = Auth::user();
                            $gradoUsuario = $user->aulas()
                                ->whereHas('users', function ($q) use ($user) {
                                    $q->where('user_id', $user->id)
                                        ->whereHas('roles', fn($r) => $r->where('name', 'docente'));
                                })
                                ->first()?->grado;

                            // Si el usuario tiene un grado asignado, mostrar solo ese grado
                            if ($gradoUsuario) {
                                return [$gradoUsuario => $gradoUsuario];
                            }

                            // Si no tiene grado asignado, mostrar todos los grados (fallback)
                            return \App\Models\Aula::query()
                                ->select('grado')
                                ->distinct()
                                ->pluck('grado', 'grado');
                        })
                        ->default(function () {
                            // Establecer el grado del usuario autenticado como valor por defecto
                            $user = Auth::user();
                            return $user->aulas()
                                ->whereHas('users', function ($q) use ($user) {
                                    $q->where('user_id', $user->id)
                                        ->whereHas('roles', fn($r) => $r->where('name', 'docente'));
                                })
                                ->first()?->grado;
                        })
                        ->searchable()
                        ->reactive()
                        ->required()
                        ->disabled() // Hacer que no se pueda cambiar
                        ->dehydrated() // Asegurar que el valor se guarde aunque esté disabled
                        ->helperText('Grado asignado a tu usuario (no se puede modificar).'),

                    Forms\Components\Select::make('profesores_responsables')
                        ->label('Profesores responsables')
                        ->multiple()
                        ->options(function (callable $get) {
                            $user = Auth::user();

                            // Obtener el grado del usuario autenticado
                            $gradoUsuario = $user->aulas()
                                ->whereHas('users', function ($q) use ($user) {
                                    $q->where('user_id', $user->id)
                                        ->whereHas('roles', fn($r) => $r->where('name', 'docente'));
                                })
                                ->first()?->grado;

                            // Si no hay grado, intentar obtenerlo del formulario
                            $grado = $gradoUsuario ?? $get('grado');

                            if (!$grado) {
                                return [];
                            }

                            // Buscar TODAS las aulas del grado (todas las secciones)
                            $aulasIds = \App\Models\Aula::where('grado', $grado)->pluck('id');

                            // Buscar TODOS los docentes asignados a ese grado (de todas las secciones)
                            return \App\Models\User::whereHas('usuario_aulas', function ($q) use ($aulasIds) {
                                $q->whereIn('aula_id', $aulasIds);
                            })
                                ->whereHas('roles', fn($r) => $r->where('name', 'docente'))
                                ->with('persona')
                                ->get()
                                ->mapWithKeys(function ($user) {
                                    $persona = $user->persona;
                                    $nombreCompleto = trim(($persona?->nombre ?? '') . ' ' . ($persona?->apellido ?? ''));

                                    // Obtener las secciones donde enseña este docente
                                    $secciones = $user->aulas()
                                        ->where('grado', $user->aulas()->first()?->grado)
                                        ->pluck('seccion')
                                        ->join(', ');

                                    $nombre = $nombreCompleto ?: 'Docente sin nombre';
                                    $nombre .= $secciones ? " (Sección: {$secciones})" : '';

                                    return [$user->id => $nombre];
                                });
                        })
                        ->default(function () {
                            // Establecer el usuario autenticado como seleccionado por defecto
                            return [Auth::id()];
                        })
                        ->reactive()
                        ->searchable()
                        ->preload()
                        ->helperText('Docentes asignados a tu grado (no puedes eliminarte como responsable).')
                        ->columnSpanFull()
                        ->afterStateUpdated(function ($state, callable $set) {
                            // Asegurar que el usuario autenticado siempre esté incluido
                            $currentUserId = Auth::id();
                            $state = is_array($state) ? $state : [];

                            if (!in_array($currentUserId, $state)) {
                                $state[] = $currentUserId;
                                $set('profesores_responsables', $state);
                            }
                        })
                        ->live()
                        ->mutateDehydratedStateUsing(function ($state) {
                            // Convertir array de IDs a array de strings para JSON
                            return is_array($state) ? array_map('strval', $state) : [];
                        }),

                    Forms\Components\Textarea::make('situacion_significativa')
                        ->label('Situación significativa')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('productos')
                        ->label('Productos esperados')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsible(),

            Forms\Components\Section::make('Contenido Curricular')
                ->schema([
                    Forms\Components\Builder::make('contenido')
                        ->label('Cursos y Competencias')
                        ->blocks([
                            Forms\Components\Builder\Block::make('curso')
                                ->label('Curso')
                                ->schema([
                                    // Selecciona curso según el grado elegido
                                    Forms\Components\Select::make('curso_id')
                                        ->label('Curso')
                                        ->options(
                                            \App\Models\Curso::query()
                                                ->orderBy('curso')
                                                ->pluck('curso', 'id')
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->helperText('Selecciona un curso.'),

                                    // Competencias del curso
                                    Forms\Components\Repeater::make('competencias')
                                        ->label('Competencias del curso')
                                        ->schema([
                                            Forms\Components\Select::make('competencia_id')
                                                ->label('Competencia')
                                                ->options(function (callable $get) {
                                                    // Obtener el curso actual
                                                    $cursoId = $get('../../curso_id');
                                                    if (!$cursoId) return [];

                                                    // Obtener todas las competencias del curso
                                                    $competencias = \App\Models\Competencia::where('curso_id', $cursoId)
                                                        ->pluck('nombre', 'id');

                                                    // Obtener las competencias ya seleccionadas dentro del mismo Repeater
                                                    $todasCompetencias = $get('../../competencias') ?? [];

                                                    $competenciasSeleccionadas = collect($todasCompetencias)
                                                        ->pluck('competencia_id')
                                                        ->filter()
                                                        ->toArray();

                                                    // Filtrar para mostrar solo las no seleccionadas
                                                    return $competencias->reject(fn($_, $id) => in_array($id, $competenciasSeleccionadas));
                                                })
                                                ->reactive()
                                                ->required()
                                                
                                                ->searchable()
                                                ->afterStateUpdated(function (callable $set) {
                                                    $set('capacidades', []);
                                                    $set('desempenos', []);
                                                }),

                                            // Capacidades dependientes de la competencia
                                            Forms\Components\Select::make('capacidades')
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
                                                ->placeholder('Seleccione una o más capacidades')
                                                ->preload()
                                                ->afterStateUpdated(function (callable $set) {
                                                    $set('desempenos', []);
                                                }),

                                            Forms\Components\Select::make('desempenos')
                                                ->label('Desempeños')
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
                                                ->placeholder('Selecciona una competencia para cargar los desempeños relacionados')
                                                ->preload(),

                                            Forms\Components\Textarea::make('criterios')
                                                ->label('Criterios de Evaluación')
                                                ->rows(3),

                                            Forms\Components\Textarea::make('evidencias')
                                                ->label('Evidencias')
                                                ->rows(2),

                                            Forms\Components\Select::make('instrumentos_predefinidos')
                                                ->label('Instrumentos (predefinidos)')
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
                                                ->columnSpanFull(),

                                            TagsInput::make('instrumentos_personalizados')
                                                ->label('Instrumentos personalizados')
                                                ->placeholder('Escribe un instrumento y presiona Enter')
                                                ->columnSpanFull()
                                                ->hidden(fn(callable $get) => !in_array('Personalizado', $get('instrumentos_predefinidos') ?? [])), // Se muestra solo si "Personalizado" está seleccionado

                                            Hidden::make('instrumentos')
                                                ->dehydrated()
                                                ->default([]),
                                        ])
                                        ->collapsible()
                                        ->createItemButtonLabel('Agregar Competencia'),
                                ])
                                ->columns(1),
                        ])
                        ->collapsible()
                        ->columnSpanFull(),
                ])
                ->collapsible(),
            // SECCIÓN 3: ENFOQUES TRANSVERSALES
            Forms\Components\Section::make('Enfoques Transversales')
                ->schema([
                    Forms\Components\Repeater::make('enfoques')
                        ->label('Enfoques Transversales')
                        ->schema([
                            Forms\Components\Select::make('enfoque_id')
                                ->label('Seleccionar Enfoque')
                                ->options(function (callable $get) {
                                    // Todas las opciones disponibles
                                    $todas = \App\Models\EnfoqueTransversal::pluck('nombre', 'id')->toArray();

                                    // Obtener los enfoques ya seleccionados en el mismo repeater (si existen)
                                    // Intentamos varias rutas por compatibilidad con la estructura del form
                                    $enfoquesEnRepeater = $get('../../enfoques') ?? $get('../enfoques') ?? $get('enfoques') ?? [];

                                    $seleccionados = collect($enfoquesEnRepeater)
                                        ->pluck('enfoque_id')
                                        ->filter()
                                        ->map(fn($v) => (string) $v) // normalizar a string para comparar claves
                                        ->values()
                                        ->toArray();

                                    // Permitir que el valor actualmente seleccionado en este ítem siga apareciendo
                                    $valorActual = $get('enfoque_id');
                                    if ($valorActual) {
                                        $seleccionados = array_filter($seleccionados, fn($id) => (string) $id !== (string) $valorActual);
                                    }

                                    // Filtrar las opciones para quitar las ya seleccionadas en otros ítems
                                    if (!empty($seleccionados)) {
                                        $todas = array_filter($todas, fn($v, $k) => !in_array((string) $k, $seleccionados), ARRAY_FILTER_USE_BOTH);
                                    }

                                    return $todas;
                                })
                                ->searchable()
                                ->placeholder('Seleccione un enfoque')
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $valores = $get('valores') ?? [];

                                    if (empty($valores) && $state) {
                                        $set('valores', [
                                            ['valor' => null, 'actitud' => '']
                                        ]);
                                    }
                                }),

                            Forms\Components\Repeater::make('valores')
                                ->label('Valores y Actitudes')
                                ->schema([
                                    Forms\Components\Select::make('valor')
                                        ->label('Valor')
                                        ->options(function (callable $get) {
                                            $enfoqueId = $get('../../enfoque_id');
                                            if (!$enfoqueId) return [];

                                            $enfoque = \App\Models\EnfoqueTransversal::find($enfoqueId);
                                            if (!$enfoque || empty($enfoque->valores_actitudes)) return [];

                                            // Opciones disponibles del enfoque
                                            $opciones = collect($enfoque->valores_actitudes)
                                                ->pluck('data.Valores', 'data.Valores')
                                                ->toArray();

                                            // Obtener los valores ya seleccionados en este mismo repeater "valores"
                                            $valoresEnRepeater = $get('../../valores') ?? [];
                                            $seleccionados = collect($valoresEnRepeater)
                                                ->pluck('valor')
                                                ->filter()
                                                ->values()
                                                ->toArray();

                                            if (!empty($seleccionados)) {
                                                // Filtrar las opciones para no mostrar las ya seleccionadas
                                                $opciones = array_filter($opciones, fn($v, $k) => !in_array($k, $seleccionados), ARRAY_FILTER_USE_BOTH);
                                            }

                                            return $opciones;
                                        })
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $enfoqueId = $get('../../enfoque_id');
                                            if (!$enfoqueId || !$state) return;

                                            $enfoque = \App\Models\EnfoqueTransversal::find($enfoqueId);
                                            if ($enfoque && $enfoque->valores_actitudes) {
                                                $valorData = collect($enfoque->valores_actitudes)
                                                    ->firstWhere('data.Valores', $state);

                                                if ($valorData) {
                                                    $set('actitud', $valorData['data']['Actitudes'] ?? '');
                                                }
                                            }
                                        })
                                        ->placeholder('Seleccione un valor')
                                        ->searchable(),

                                    Forms\Components\Textarea::make('actitud')
                                        ->label('Actitud')
                                        ->rows(2)
                                        ->placeholder('Se completará automáticamente.')
                                        ->disabled(),
                                ])
                                ->columns(2)
                                ->collapsed(false)
                                ->addActionLabel('Agregar Valor y Actitud'),
                        ])
                        ->columns(1)
                        ->collapsed(false)
                        ->addActionLabel('Agregar Enfoque'),
                ])
                ->collapsed(false)
                ->collapsible(),
            // SECCIÓN 4: MATERIALES Y RECURSOS
            Forms\Components\Section::make('Materiales y Recursos')
                ->schema([
                    Forms\Components\Textarea::make('materiales_basicos')
                        ->label('Materiales básicos a utilizar en la unidad')
                        ->rows(3)
                        ->placeholder('Ejemplo: Cartulinas, marcadores, papel bond, témperas, etc.')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('recursos')
                        ->label('Recursos a utilizar en la unidad')
                        ->rows(3)
                        ->placeholder('Ejemplo: Aula virtual, videos educativos, material impreso, pizarra digital, etc.')
                        ->columnSpanFull(),
                ])
                ->collapsed(false)
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre de la Unidad')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grado')
                    ->label('Grado')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Fecha Fin')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // ACCIÓN DE PREVISUALIZACIÓN CON MODAL NATIVO
                Action::make('previsualizar')
                    ->label('Previsualizar')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('📄 Vista Previa del Documento')
                    ->modalDescription('Seleccione el formato para previsualizar:')
                    ->modalSubmitActionLabel('Vista Previa Vertical')
                    ->modalCancelActionLabel('Vista Previa Horizontal')
                    ->action(function ($record) {
                        return redirect()->to(route('unidades.vista.previa', ['id' => $record->id, 'orientacion' => 'vertical']));
                    })
                    ->cancelParentActions()
                    ->extraModalFooterActions([
                        Action::make('horizontal')
                            ->label('Vista Previa Horizontal')
                            ->icon('heroicon-o-document')
                            ->color('primary')
                            ->action(function ($record) {
                                return redirect()->to(route('unidades.vista.previa', ['id' => $record->id, 'orientacion' => 'horizontal']));
                            })
                            ->close(),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canEdit($record): bool
    {
        return Auth::check();
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnidads::route('/'),
            'create' => Pages\CreateUnidad::route('/create'),
            'edit' => Pages\EditUnidad::route('/{record}/edit'),
        ];
    }
}
