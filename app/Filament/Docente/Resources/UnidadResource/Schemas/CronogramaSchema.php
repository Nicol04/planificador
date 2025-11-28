<?php

namespace App\Filament\Docente\Resources\UnidadResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class CronogramaSchema
{
    public static function schema(): array
    {
        return [
            Section::make('Programación Semanal')
                ->heading('Cronograma de Sesiones')
                ->description('Genera las semanas. Puedes agregar múltiples sesiones por día.')
                ->headerActions([
                    Action::make('generar_semanas')
                        ->label('Generar Semanas')
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('¿Generar estructura?')
                        ->modalDescription('Se crearán las semanas y días automáticamente. ⚠️ Se borrará el contenido actual.')
                        ->action(function (Get $get, Set $set) {
                            $fechaInicio = $get('fecha_inicio');
                            $fechaFin = $get('fecha_fin');

                            if (!$fechaInicio || !$fechaFin) {
                                Notification::make()->title('Faltan fechas')->warning()->send();
                                return;
                            }

                            $inicio = Carbon::parse($fechaInicio);
                            $fin = Carbon::parse($fechaFin);

                            if ($inicio->gt($fin)) {
                                Notification::make()->title('Fechas incorrectas')->danger()->send();
                                return;
                            }

                            $periodo = CarbonPeriod::create($inicio, $fin);
                            $semanas = [];
                            $diasBuffer = [];
                            $numSemana = 1;

                            foreach ($periodo as $date) {
                                if ($date->isWeekend()) continue;

                                if ($date->dayOfWeek === Carbon::MONDAY && count($diasBuffer) > 0) {
                                    $semanas[] = [
                                        'titulo_semana' => 'SEMANA ' . $numSemana,
                                        'semana_id' => $numSemana,
                                        'dias' => $diasBuffer
                                    ];
                                    $diasBuffer = [];
                                    $numSemana++;
                                }

                                $diasBuffer[] = [
                                    'fecha' => $date->format('Y-m-d'),
                                    'sesiones' => [ // Ahora es un array de sesiones
                                        ['titulo' => null] 
                                    ],
                                ];
                            }

                            if (count($diasBuffer) > 0) {
                                $semanas[] = [
                                    'titulo_semana' => 'SEMANA ' . $numSemana,
                                    'semana_id' => $numSemana,
                                    'dias' => $diasBuffer
                                ];
                            }

                            $set('cronograma', $semanas);
                            Notification::make()->title('Estructura generada exitosamente')->success()->send();
                        })
                ])
                ->schema([
                    // 🔴 NIVEL 1: LAS SEMANAS
                    Repeater::make('cronograma')
                        ->label('Lista de Semanas')
                        ->hiddenLabel()
                        ->schema([
                            TextInput::make('titulo_semana')
                                ->hiddenLabel()
                                ->required()
                                ->columnSpanFull(),
                            
                            TextInput::make('semana_id')->hidden(),

                            // 🟡 NIVEL 2: LOS DÍAS
                            Repeater::make('dias')
                                ->hiddenLabel()
                                ->schema([
                                    Grid::make(12)->schema([
                                        
                                        // CAJA IZQUIERDA: EL DÍA (Estilo Etiqueta)
                                        Placeholder::make('dia_label')
                                            ->hiddenLabel()
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 3, 
                                            ])
                                            ->content(function (Get $get) {
                                                $fecha = $get('fecha');
                                                if (!$fecha) return 'DÍA';
                                                
                                                $date = Carbon::parse($fecha)->locale('es');
                                                $diaNombre = mb_strtoupper($date->dayName);
                                                
                                                return new HtmlString("
                                                    <div class='flex flex-row md:flex-col items-center justify-between md:justify-center h-full p-3 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600'>
                                                        <span class='font-bold text-gray-700 dark:text-gray-200 text-lg'>$diaNombre</span>
                                                        <span class='text-sm text-primary-600 font-medium'>{$date->format('d/m')}</span>
                                                    </div>
                                                ");
                                            }),

                                        // CAJA DERECHA: LISTA DE SESIONES (Permite agregar más)
                                        Repeater::make('sesiones')
                                            ->hiddenLabel()
                                            ->schema([
                                                TextInput::make('titulo')
                                                    ->hiddenLabel()
                                                    ->placeholder('Título de la sesión...')
                                                    ->required(),
                                            ])
                                            ->columnSpan([
                                                'default' => 12,
                                                'md' => 9,
                                            ])
                                            ->defaultItems(1)
                                            ->addActionLabel('+ Agregar otra sesión a este día')
                                            ->reorderableWithButtons()
                                            ->grid(1) // Lista vertical
                                            // 3️⃣ Normalizar sesiones
                                            ->mutateDehydratedStateUsing(fn (array $state): array => array_values($state)), 

                                        DatePicker::make('fecha')->hidden(),
                                    ])
                                ])
                                ->addable(true)
                                ->reorderable(false) // Los días no deberían reordenarse libremente si están ordenados por fecha
                                ->deletable(false)
                                ->collapsible(false)
                                ->itemLabel(null)
                                // 2️⃣ Normalizar días
                                ->mutateDehydratedStateUsing(fn (array $state): array => array_values($state)), 
                        ])
                        // ESTILOS DE BARRA CON CSS EN LÍNEA (Solución para colores)
                        ->itemLabel(function (array $state) {
                            $titulo = $state['titulo_semana'] ?? 'NUEVA SEMANA';
                            $id = $state['semana_id'] ?? 1;

                            // Colores definidos con HEX para evitar problemas de compilación
                            $colors = [
                                1 => ['bg' => '#ef4444', 'border' => '#dc2626', 'text' => 'white'], // Rojo
                                2 => ['bg' => '#facc15', 'border' => '#eab308', 'text' => 'black'], // Amarillo
                                3 => ['bg' => '#3b82f6', 'border' => '#2563eb', 'text' => 'white'], // Azul
                                4 => ['bg' => '#4ade80', 'border' => '#16a34a', 'text' => 'white'], // Verde
                                0 => ['bg' => '#fb923c', 'border' => '#ea580c', 'text' => 'white'], // Naranja
                            ];
                            
                            $c = $colors[$id % 5 ?: 5] ?? $colors[1];

                            return new HtmlString("
                                <div style='display: flex; justify-content: center; width: 100%;'>
                                    <span style='
                                        background-color: {$c['bg']}; 
                                        color: {$c['text']}; 
                                        border: 2px solid {$c['border']};
                                        padding: 4px 16px; 
                                        border-radius: 9999px; 
                                        font-weight: bold; 
                                        text-transform: uppercase; 
                                        letter-spacing: 0.1em;
                                        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                                    '>
                                        $titulo
                                    </span>
                                </div>
                            ");
                        })
                        ->collapsible()
                        ->collapsed()
                        ->addActionLabel('Agregar Semana')
                        ->reorderable()
                        ->cloneable()
                        ->mutateDehydratedStateUsing(fn (array $state): array => array_values($state)), 
                ]),
        ];
    }
}