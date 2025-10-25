<?php

namespace App\Filament\Docente\Resources\SesionResource\Schemas;

use App\Models\CapacidadTransversal;
use App\Models\CompetenciaTransversal;
use App\Models\Desempeno;
use App\Models\EnfoqueTransversal;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

class EnfoquesSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Checkbox::make('mostrar_enfoques')
                ->label('¿Agregar enfoques transversales?')
                ->reactive()
                ->columnSpan(2)
                ->extraAttributes([
                    'class' => 'rounded-md text-sm text-gray-700'
                ]),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('enfoque_transversal_ids')
                    ->label('Enfoque transversal')
                    ->multiple()
                    ->options(fn() => EnfoqueTransversal::orderBy('nombre')->pluck('nombre', 'id')->toArray())
                    ->visible(fn($get) => (bool) $get('mostrar_enfoques'))
                    ->preload()
                    ->reactive()
                    ->helperText('Seleccione uno o más enfoques transversales')
                    ->extraAttributes([
                        'class' => 'rounded-lg border border-[#81c9fa] bg-white px-3 py-2 shadow-sm'
                    ])
                    ->columnSpan(1),

                Forms\Components\Select::make('competencias_transversales_ids')
                    ->label('Competencias transversales')
                    ->multiple()
                    ->options(fn() => CompetenciaTransversal::orderBy('nombre')->pluck('nombre', 'id')->toArray())
                    ->visible(fn($get) => (bool) $get('mostrar_enfoques'))
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        $set('capacidades_transversales_ids', []);
                        $set('desempeno_transversal_ids', []);
                    })
                    ->helperText('Se cargarán las capacidades relacionadas')
                    ->extraAttributes([
                        'class' => 'rounded-lg border border-[#81c9fa] bg-white px-3 py-2 shadow-sm'
                    ])
                    ->columnSpan(1),

                Forms\Components\Select::make('capacidades_transversales_ids')
                    ->label('Capacidades transversales')
                    ->multiple()
                    ->options(function (callable $get) {
                        $competencias = $get('competencias_transversales_ids') ?? [];
                        if (empty($competencias)) return [];
                        return CapacidadTransversal::whereIn('competencia_transversal_id', (array) $competencias)
                            ->orderBy('nombre')
                            ->pluck('nombre', 'id')
                            ->toArray();
                    })
                    ->visible(fn($get) => (bool) $get('mostrar_enfoques'))
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        $set('desempeno_transversal_ids', []);
                    })
                    ->helperText('Seleccione capacidades vinculadas a las competencias seleccionadas')
                    ->extraAttributes([
                        'class' => 'rounded-lg border border-[#81c9fa] bg-white px-3 py-2 shadow-sm'
                    ])
                    ->columnSpan(1),

                Forms\Components\Select::make('desempeno_transversal_ids')
                    ->label('Desempeños transversales')
                    ->multiple()
                    ->options(function (callable $get) {
                        $capIds = $get('capacidades_transversales_ids') ?? [];
                        if (empty($capIds)) return [];

                        $user = Auth::user();
                        $grado = null;
                        if ($user) {
                            $usuarioAula = $user->usuario_aulas()->with('aula')->latest()->first();
                            $grado = $usuarioAula?->aula?->grado;
                        }
                        if (!$grado) return [];

                        $gradoLimpio = preg_replace('/[^0-9]/', '', $grado);

                        return Desempeno::whereIn('capacidad_transversal_id', (array) $capIds)
                            ->where('grado', 'LIKE', "%{$gradoLimpio}%")
                            ->orderBy('descripcion')
                            ->pluck('descripcion', 'id')
                            ->toArray();
                    })
                    ->visible(fn($get) => (bool) $get('mostrar_enfoques'))
                    ->reactive()
                    ->helperText('Desempeños filtrados por el grado del aula del docente')
                    ->extraAttributes([
                        'class' => 'rounded-lg border border-[#81c9fa] bg-white px-3 py-2 shadow-sm'
                    ])
                    ->columnSpan(1),

                Forms\Components\Textarea::make('criterios_transversales')
                    ->label('Criterios de evaluación')
                    ->rows(4)
                    ->visible(fn($get) => (bool) $get('mostrar_enfoques'))
                    ->helperText('Describa los criterios de evaluación para los enfoques seleccionados')
                    ->extraAttributes([
                        'class' => 'rounded-lg border border-[#81c9fa] bg-white px-3 py-2 shadow-sm',
                        'placeholder' => 'Escriba los criterios de evaluación...'
                    ])
                    ->columnSpan(2),

                Forms\Components\Select::make('instrumentos_transversales_ids')
                    ->label('Instrumentos de evaluación')
                    ->multiple()
                    ->options([
                        'rubrica' => 'Rúbrica',
                        'lista_cotejo' => 'Lista de cotejo',
                        'prueba' => 'Prueba',
                        'observacion' => 'Observación',
                        'portafolio' => 'Portafolio',
                        'proyecto' => 'Proyecto',
                        'entrevista' => 'Entrevista',
                        'otro_personalizado' => 'Personalizado',
                    ])
                    ->visible(fn($get) => (bool) $get('mostrar_enfoques'))
                    ->reactive()
                    ->preload()
                    ->helperText('Seleccione uno o más instrumentos. Elija "Personalizado" para añadir uno propio.')
                    ->extraAttributes([
                        'class' => 'rounded-lg border border-[#81c9fa] bg-white px-3 py-2 shadow-sm'
                    ])
                    ->columnSpan(2),

                Forms\Components\Textarea::make('instrumentos_transversales_personalizados')
                    ->label('Instrumento(s) personalizado(s)')
                    ->rows(3)
                    ->visible(fn($get) => in_array('otro_personalizado', (array) ($get('instrumentos_transversales_ids') ?? [])))
                    ->helperText('Describa el/los instrumento(s) personalizado(s).')
                    ->extraAttributes([
                        'class' => 'rounded-lg border border-[#81c9fa] bg-white px-3 py-2 shadow-sm',
                        'placeholder' => 'Escriba el instrumento personalizado...'
                    ])
                    ->columnSpan(2),
            ])->columnSpan(2),
        ];
    }
}