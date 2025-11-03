<?php

namespace App\Filament\Docente\Resources;

use App\Filament\Docente\Resources\AsistenciaResource\Pages;
use App\Filament\Docente\Resources\AsistenciaResource\RelationManagers;
use App\Models\Asistencia;
use App\Models\Año;
use App\Models\Estudiante;
use App\Models\usuario_aula;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class AsistenciaResource extends Resource
{
    protected static ?string $model = Asistencia::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Hidden::make('docente_id')
                ->default(fn() => Auth::id()),

            Hidden::make('nombre_aula')
                ->default(function () {
                    $año = Año::whereDate('fecha_inicio', '<=', now())
                        ->whereDate('fecha_fin', '>=', now())
                        ->first();

                    $ua = usuario_aula::where('user_id', Auth::id())
                        ->when($año, fn($q) => $q->where('año_id', $año->id))
                        ->first();

                    return $ua?->aula?->nombre ?? null;
                }),

            Select::make('mes')
                ->label('Mes')
                ->options([
                    'Enero' => 'Enero','Febrero' => 'Febrero','Marzo' => 'Marzo','Abril' => 'Abril',
                    'Mayo' => 'Mayo','Junio' => 'Junio','Julio' => 'Julio','Agosto' => 'Agosto',
                    'Septiembre' => 'Septiembre','Octubre' => 'Octubre','Noviembre' => 'Noviembre','Diciembre' => 'Diciembre',
                ])
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    // Determinar fechas del mes seleccionado
                    if (!$state) return;
                    $year = now()->year;
                    $map = ['Enero'=>1,'Febrero'=>2,'Marzo'=>3,'Abril'=>4,'Mayo'=>5,'Junio'=>6,'Julio'=>7,'Agosto'=>8,'Septiembre'=>9,'Octubre'=>10,'Noviembre'=>11,'Diciembre'=>12];
                    $monthNumber = $map[$state] ?? null;

                    if (!$monthNumber) return;

                    $start = Carbon::create($year, $monthNumber, 1)->startOfDay();
                    $end = $start->copy()->endOfMonth()->endOfDay();

                    $set('fecha_inicio', $start->toDateString());
                    $set('fecha_fin', $end->toDateString());

                    // Cargar estudiantes según el aula del docente
                    $año = Año::whereDate('fecha_inicio', '<=', now())
                        ->whereDate('fecha_fin', '>=', now())
                        ->first();

                    $ua = usuario_aula::where('user_id', Auth::id())
                        ->when($año, fn($q) => $q->where('año_id', $año->id))
                        ->first();

                    if ($ua?->aula_id) {
                        $estudiantes = Estudiante::where('aula_id', $ua->aula_id)
                            ->orderBy('apellidos')
                            ->orderBy('nombres')
                            ->get()
                            ->map(fn($e) => [
                                'id' => $e->id,
                                'nombre' => trim(($e->apellidos ?? '') . ' ' . ($e->nombres ?? ''))
                            ])
                            ->values()
                            ->toArray();

                        $set('estudiantes_list', $estudiantes);
                    }
                }),

            DatePicker::make('fecha_inicio')->label('Fecha inicio')->required(),
            DatePicker::make('fecha_fin')->label('Fecha fin')->nullable(),

            Hidden::make('estudiantes_list')
                ->default(function () {
                    $año = Año::whereDate('fecha_inicio', '<=', now())
                        ->whereDate('fecha_fin', '>=', now())
                        ->first();

                    $ua = usuario_aula::where('user_id', Auth::id())
                        ->when($año, fn($q) => $q->where('año_id', $año->id))
                        ->first();

                    if ($ua?->aula_id) {
                        return Estudiante::where('aula_id', $ua->aula_id)
                            ->orderBy('apellidos')
                            ->orderBy('nombres')
                            ->get()
                            ->map(fn($e) => [
                                'id' => $e->id,
                                'nombre' => trim(($e->apellidos ?? '') . ' ' . ($e->nombres ?? ''))
                            ])
                            ->values()
                            ->toArray();
                    }

                    return [];
                })
                ->reactive(),

            // === Vista previa en la misma página ===
            Placeholder::make('asistencia_preview')
                ->label('Vista previa de lista de asistencia')
                ->live() // <--- Importante para refrescar automáticamente
                ->reactive()
                ->content(function ($get) {
                    $estudiantes = $get('estudiantes_list') ?? [];
                    $mes = $get('mes') ?? '—';

                    // Cabeceras dinámicas
                    $weekHeader = '';
                    for ($w = 1; $w <= 4; $w++) {
                        $weekHeader .= "<th colspan='5' class='text-center bg-gray-100'>Semana {$w}</th>";
                    }

                    $daysHeader = '';
                    for ($w = 1; $w <= 4; $w++) {
                        foreach (['L','M','M','J','V'] as $d) {
                            $daysHeader .= "<th class='text-center small'>{$d}</th>";
                        }
                    }

                    // Filas de estudiantes
                    $rows = '';
                    foreach ($estudiantes as $i => $e) {
                        $nombre = $e['nombre'] ?? '';
                        $cells = str_repeat("<td style='height:28px;'></td>", 20);
                        $rows .= "<tr><td>" . ($i+1) . "</td><td>{$nombre}</td>{$cells}<td></td></tr>";
                    }

                    if (!$rows) {
                        $rows = "<tr><td colspan='23' class='text-center text-sm text-gray-500'>No hay estudiantes registrados.</td></tr>";
                    }

                    $previewUrl = url('/documentos/asistencias/vista-previa-html') . '?mes=' . urlencode($mes);

                    return <<<HTML
<div class="mb-2">
    <a href="{$previewUrl}" target="_blank" class="filament-button filament-button-size-sm filament-button-primary">
        Ver / Imprimir (vista previa)
    </a>
</div>

<div class="overflow-auto">
    <table class="table-auto w-full border border-gray-200 text-sm">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="width:40px;">N°</th>
                <th style="width:220px;">Apellidos y nombres</th>
                {$weekHeader}
                <th style="width:100px;">Observaciones</th>
            </tr>
            <tr style="background:#fafafa;">
                <th></th><th></th>
                {$daysHeader}
                <th></th>
            </tr>
        </thead>
        <tbody>{$rows}</tbody>
    </table>
</div>
HTML;
                })
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsistencias::route('/'),
            'create' => Pages\CreateAsistencia::route('/create'),
            'edit' => Pages\EditAsistencia::route('/{record}/edit'),
        ];
    }
}
