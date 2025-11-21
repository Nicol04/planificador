<?php

namespace App\Filament\Docente\Resources;

use App\Filament\Docente\Resources\AsistenciaResource\Pages;
use App\Filament\Docente\Resources\AsistenciaResource\RelationManagers;
use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\UsuarioAula;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class AsistenciaResource extends Resource
{
    protected static ?string $model = Asistencia::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('docente_id')
                    ->default(fn() => Auth::id()) // Asigna el ID del usuario autenticado
                    ->dehydrated(true)
                    ->required(),
                Forms\Components\Hidden::make('nombre_aula')
                    ->default(function () {
                        $user = Auth::user();

                        if (! $user) return null;

                        // Si el docente tiene varios, tomamos el primero
                        $ua = $user->usuario_aulas()->with('aula')->first();

                        if ($ua && $ua->aula) {
                            return $ua->aula->nombre;
                        }

                        return null;
                    })
                    ->dehydrated(true) // Para que se guarde en la BD
                    ->required(),
                Forms\Components\Hidden::make('plantilla_id')
                    ->default(fn() => ($id = request()->query('plantilla_id')) ? (int) $id : (request()->route('plantilla_id') ?? null))
                    ->dehydrated(true)
                    ->required(),
                // Selector de mes y año
                Forms\Components\Select::make('mes')
                    ->label('Mes')
                    ->options([
                        1 => 'Enero',
                        2 => 'Febrero',
                        3 => 'Marzo',
                        4 => 'Abril',
                        5 => 'Mayo',
                        6 => 'Junio',
                        7 => 'Julio',
                        8 => 'Agosto',
                        9 => 'Septiembre',
                        10 => 'Octubre',
                        11 => 'Noviembre',
                        12 => 'Diciembre',
                    ])
                    ->reactive()
                    ->required(),

                Forms\Components\Select::make('anio')
                    ->label('Año')
                    ->options(function () {
                        $current = (int) date('Y');
                        // rango año -1 .. +1 (ajustar si se desea)
                        return [
                            $current - 1 => (string)($current - 1),
                            $current => (string)$current,
                            $current + 1 => (string)($current + 1),
                        ];
                    })
                    ->reactive()
                    ->required(),
                Forms\Components\Hidden::make('dias_no_clase')
                    ->default(fn(?Asistencia $record) => $record?->dias_no_clase ?? [])
                    ->dehydrated(true)
                    ->id('dias_no_clase_input'),

                // Renderiza la vista Blade como HTML seguro usando HtmlString
                Forms\Components\Placeholder::make('preview_calendar')
                    ->label('Calendario y Estudiantes')
                    ->columnSpan('full') // Ocupa todo el ancho del formulario
                    ->content(function ($get) {
                        $mes = $get('mes');
                        $anio = $get('anio');

                        if (! $mes || ! $anio) {
                            return new HtmlString('<div>Seleccione mes y año para ver el calendario y los estudiantes.</div>');
                        }

                        $matrix = Asistencia::generateWeeksMatrix((int)$mes, (int)$anio);
                        $weeksCount = count($matrix);

                        $user = Auth::user();
                        $students = collect();
                        if ($user) {
                            if (method_exists($user, 'usuario_aulas')) {
                                $aulaIds = $user->usuario_aulas()->pluck('aula_id')->toArray();
                                if (! empty($aulaIds)) {
                                    $students = Estudiante::whereIn('aula_id', $aulaIds)->get();
                                }
                            } elseif (isset($user->aula_id)) {
                                $students = Estudiante::where('aula_id', $user->aula_id)->get();
                            }
                        }

                        $existingDias = $get('dias_no_clase') ?? [];

                        $html = view('filament.docente.asistencia.asistencia', [
                            'matrix' => $matrix,
                            'students' => $students,
                            'weeksCount' => $weeksCount,
                            'existingDias' => $existingDias,
                        ])->render();
                        $wrapped = '<div class="w-full overflow-auto">' . $html . '</div>';
                        return new HtmlString($wrapped);
                    }),
            ]);
    }


    public static function canCreate(): bool
    {
        return true;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsistencias::route('/'),
            'create' => Pages\CreateAsistencia::route('/create'),
            'edit' => Pages\EditAsistencia::route('/{record}/edit'),

        ];
    }
}
