<?php

namespace App\Filament\Docente\Resources\UnidadResource\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TagsInput;
use Illuminate\Support\Facades\Auth;

class DatosUnidadSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Datos de la Unidad')
                ->description('Configuración básica de tu unidad de aprendizaje')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Forms\Components\Grid::make(1)
                        ->schema([
                            // Nombre de la unidad
                            Forms\Components\TextInput::make('nombre')
                                ->label('📝 Nombre de la Unidad')
                                ->placeholder('Ej: Nos conocemos y organizamos nuestra aula')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Dale un nombre descriptivo')
                                ->columnSpanFull()
                                ->prefixIcon('heroicon-o-bookmark'),

                            // Fechas
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\DatePicker::make('fecha_inicio')
                                    ->label('📅 Fecha de inicio')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->prefixIcon('heroicon-o-calendar'),

                                Forms\Components\DatePicker::make('fecha_fin')
                                    ->label('🏁 Fecha de fin')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->after('fecha_inicio')
                                    ->prefixIcon('heroicon-o-calendar'),
                            ]),

                            // Grado y Profesores en la misma fila
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('grado')
                                    ->label('🎓 Grado')
                                    ->options(function () {
                                        $user = Auth::user();
                                        $gradoUsuario = $user->aulas()
                                            ->whereHas('users', function ($q) use ($user) {
                                                $q->where('user_id', $user->id)
                                                    ->whereHas('roles', fn($r) => $r->where('name', 'docente'));
                                            })
                                            ->first()?->grado;

                                        if ($gradoUsuario) {
                                            return [$gradoUsuario => $gradoUsuario . '° grado'];
                                        }

                                        return \App\Models\Aula::query()
                                            ->select('grado')
                                            ->distinct()
                                            ->pluck('grado', 'grado')
                                            ->map(fn($g) => $g . '° grado');
                                    })
                                    ->default(function () {
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
                                    ->disabled()
                                    ->dehydrated()
                                    ->native(false)
                                    ->helperText('Grado asignado según tu perfil')
                                    ->prefixIcon('heroicon-o-academic-cap'),

                                Forms\Components\Select::make('profesores_responsables')
                                    ->label('👥 Profesores Responsables')
                                    ->multiple()
                                    ->options(function (callable $get) {
                                        $user = Auth::user();
                                        $gradoUsuario = $user->aulas()
                                            ->whereHas('users', function ($q) use ($user) {
                                                $q->where('user_id', $user->id)
                                                    ->whereHas('roles', fn($r) => $r->where('name', 'docente'));
                                            })
                                            ->first()?->grado;

                                        $grado = $gradoUsuario ?? $get('grado');

                                        if (!$grado) {
                                            return [];
                                        }

                                        $aulasIds = \App\Models\Aula::where('grado', $grado)->pluck('id');

                                        return \App\Models\User::whereHas('usuario_aulas', function ($q) use ($aulasIds) {
                                            $q->whereIn('aula_id', $aulasIds);
                                        })
                                            ->whereHas('roles', fn($r) => $r->where('name', 'docente'))
                                            ->with('persona')
                                            ->get()
                                            ->mapWithKeys(function ($user) {
                                                $persona = $user->persona;
                                                $nombreCompleto = trim(($persona?->nombre ?? '') . ' ' . ($persona?->apellido ?? ''));

                                                $secciones = $user->aulas()
                                                    ->where('grado', $user->aulas()->first()?->grado)
                                                    ->pluck('seccion')
                                                    ->join(', ');

                                                $nombre = $nombreCompleto ?: 'Docente sin nombre';

                                                if ($user->id === Auth::id()) {
                                                    $nombre = '⭐ ' . $nombre . ' (Tú)';
                                                }

                                                $nombre .= $secciones ? " 📚 Sección: {$secciones}" : '';

                                                return [$user->id => $nombre];
                                            });
                                    })
                                    ->default(function () {
                                        return [Auth::id()];
                                    })
                                    ->reactive()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->helperText('Selecciona los docentes que trabajarán en esta unidad')
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $currentUserId = Auth::id();
                                        $state = is_array($state) ? $state : [];

                                        if (!in_array($currentUserId, $state)) {
                                            $state[] = $currentUserId;
                                            $set('profesores_responsables', $state);
                                        }
                                    })
                                    ->live()
                                    ->mutateDehydratedStateUsing(function ($state) {
                                        return is_array($state) ? array_map('strval', $state) : [];
                                    })
                                    ->prefixIcon('heroicon-o-user-group'),
                            ]),

                            // Situación significativa
                            Forms\Components\Textarea::make('situacion_significativa')
                                ->label('📖 Situación Significativa')
                                ->required()
                                ->placeholder('Describe el contexto o problemática que abordará esta unidad...')
                                ->rows(4)
                                ->helperText('Plantea una situación de la vida real que motive el aprendizaje'),

                            // Productos esperados
                            Forms\Components\Textarea::make('productos')
                                ->required()
                                ->label('🎯 Productos Esperados')
                                ->placeholder('Ej: Organizador visual, exposición, proyecto colaborativo...')
                                ->rows(3)
                                ->helperText('¿Qué crearán o lograrán los estudiantes?'),
                        ])
                        ->extraAttributes([
                            'style' => 'background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); padding: 1.5rem; border-radius: 0.75rem; border: 2px solid #3b82f6;'
                        ]),
                ])
        ];
    }
}
