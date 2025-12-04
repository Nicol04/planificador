<?php

namespace App\Filament\Resources\AulaResource\RelationManagers;

use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class EstudiantesRelationManager extends RelationManager
{
    protected static string $relationship = 'estudiantes';
    protected ?string $pendingActionTitle = 'Agregar / Vincular Estudiantes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombres')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombres'),
                Forms\Components\TextInput::make('apellidos')
                    ->required()
                    ->maxLength(255)
                    ->label('Apellidos'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombres')
            ->columns([
                Tables\Columns\TextColumn::make('apellidos')
                    ->label('Apellidos')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombres')
                    ->label('Nombres')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // 1. DESCARGAR PLANTILLA (Mejorada para Excel)
                Action::make('descargarPlantilla')
                    ->label('Descargar Plantilla CSV')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('gray')
                    ->action(function () {
                        return response()->streamDownload(function () {
                            echo "\xEF\xBB\xBF"; // BOM para UTF-8 en Excel
                            $handle = fopen('php://output', 'w');
                            // Usamos punto y coma para Excel en español
                            fputcsv($handle, ['nombres', 'apellidos'], ';');
                            fputcsv($handle, ['Juan', 'Perez'], ';');
                            fputcsv($handle, ['Maria', 'Gomez'], ';');
                            fclose($handle);
                        }, 'plantilla_estudiantes.csv');
                    }),

                // 2. IMPORTAR DESDE EXCEL (Con Vista Previa Mejorada)
                Action::make('importarEstudiantes')
                    ->label('Importar desde Excel (CSV)')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->color('success')
                    ->modalWidth('4xl')
                    ->modalSubmitActionLabel('Confirmar e Importar')
                    ->form([
                        Forms\Components\FileUpload::make('archivo_csv')
                            ->label('Archivo CSV (Guardado desde Excel)')
                            ->helperText('Usa la plantilla. El sistema detecta comas o punto y coma automáticamente.')
                            ->disk('local') 
                            ->directory('temp-imports')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                            ->required()
                            ->live()
                            ->validationMessages([
                                'required' => 'Debes subir un archivo para continuar.',
                            ]),

                        // VISTA PREVIA MEJORADA
                        Forms\Components\Placeholder::make('preview')
                            ->label('Vista Previa de Importación')
                            ->content(function (Forms\Get $get, RelationManager $livewire) {
                                $archivo = $get('archivo_csv');
                                if (!$archivo) return 'Sube un archivo para ver los datos.';

                                // Obtener ID del Aula actual para comparar
                                $aulaActualId = $livewire->getOwnerRecord()->id;

                                if (is_array($archivo)) $archivo = reset($archivo);

                                $path = null;
                                if (is_object($archivo) && method_exists($archivo, 'getRealPath')) {
                                    $path = $archivo->getRealPath();
                                } elseif (is_string($archivo)) {
                                    $path = Storage::disk('local')->path($archivo);
                                }

                                if (!$path || !file_exists($path)) return 'Cargando previsualización...';

                                $handle = fopen($path, 'r');
                                $firstLine = fgets($handle);
                                rewind($handle);
                                $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
                                
                                $header = fgetcsv($handle, 1000, $delimiter);
                                $header = array_map(fn($h) => strtolower(trim(preg_replace('/[\x{FEFF}]+/u', '', $h))), $header);

                                if (!$header || !in_array('nombres', $header) || !in_array('apellidos', $header)) {
                                    fclose($handle);
                                    return new HtmlString('<div style="color: red; font-weight: bold;">Error: El archivo no tiene las columnas "nombres" y "apellidos".</div>');
                                }

                                $idxNombre = array_search('nombres', $header);
                                $idxApellido = array_search('apellidos', $header);
                                
                                // Tabla con estilos inline para asegurar visualización correcta
                                $html = '<div style="overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 0.5rem;">';
                                $html .= '<table style="width: 100%; border-collapse: collapse; font-size: 0.875rem; text-align: left;">';
                                $html .= '<thead style="background-color: #f9fafb; color: #374151; text-transform: uppercase;"><tr>';
                                $html .= '<th style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">Nombres</th>';
                                $html .= '<th style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">Apellidos</th>';
                                $html .= '<th style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">Estado Detectado</th>';
                                $html .= '</tr></thead><tbody>';

                                $count = 0;
                                while (($row = fgetcsv($handle, 1000, $delimiter)) !== false && $count < 50) {
                                    if (count($row) < 2) continue;
                                    $nombres = trim($row[$idxNombre] ?? '');
                                    $apellidos = trim($row[$idxApellido] ?? '');
                                    if (empty($nombres) || empty($apellidos)) continue;

                                    $estudiante = \App\Models\Estudiante::where('nombres', $nombres)
                                        ->where('apellidos', $apellidos)
                                        ->first();
                                    
                                    $estadoTexto = 'Crear Nuevo';
                                    $estiloBadge = 'background-color: #dbeafe; color: #1e40af;'; // Azul
                                    
                                    if ($estudiante) {
                                        if (is_null($estudiante->aula_id)) {
                                            $estadoTexto = 'Vincular (Libre)';
                                            $estiloBadge = 'background-color: #dcfce7; color: #166534;'; // Verde
                                        } elseif ($estudiante->aula_id == $aulaActualId) {
                                            $estadoTexto = 'Ya está en tu aula';
                                            $estiloBadge = 'background-color: #f3f4f6; color: #374151;'; // Gris
                                        } else {
                                            $estadoTexto = 'En otra aula (Omitir)';
                                            $estiloBadge = 'background-color: #fee2e2; color: #991b1b;'; // Rojo
                                        }
                                    }

                                    $html .= "<tr style='border-bottom: 1px solid #f3f4f6;'>";
                                    $html .= "<td style='padding: 12px 16px;'>$nombres</td>";
                                    $html .= "<td style='padding: 12px 16px;'>$apellidos</td>";
                                    $html .= "<td style='padding: 12px 16px;'><span style='padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.75rem; $estiloBadge'>$estadoTexto</span></td>";
                                    $html .= "</tr>";
                                    $count++;
                                }
                                
                                if (!feof($handle)) {
                                    $html .= "<tr><td colspan='3' style='padding: 12px 16px; text-align: center; color: #6b7280; font-style: italic;'>... y más registros ...</td></tr>";
                                }
                                
                                $html .= '</tbody></table></div>';
                                fclose($handle);

                                return new HtmlString($html);
                            })
                            ->hidden(fn (Forms\Get $get) => !$get('archivo_csv')),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $aula = $livewire->getOwnerRecord();
                        $archivo = $data['archivo_csv'];
                        
                        if (is_array($archivo)) $archivo = reset($archivo);
                        $path = Storage::disk('local')->path($archivo);
                        if (is_object($archivo) && method_exists($archivo, 'getRealPath')) $path = $archivo->getRealPath();
                        
                        if (!file_exists($path)) {
                            Notification::make()->title('Error al leer el archivo')->danger()->send();
                            return;
                        }

                        $handle = fopen($path, 'r');
                        $firstLine = fgets($handle); 
                        rewind($handle); 
                        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

                        $header = fgetcsv($handle, 1000, $delimiter); 
                        $header = array_map(fn($h) => strtolower(trim(preg_replace('/[\x{FEFF}]+/u', '', $h))), $header);
                        
                        if (!$header || !in_array('nombres', $header) || !in_array('apellidos', $header)) {
                            Notification::make()->title('Formato incorrecto')->danger()->send();
                            fclose($handle);
                            return;
                        }

                        $creados = 0; $vinculados = 0; $omitidos = 0;
                        $idxNombre = array_search('nombres', $header);
                        $idxApellido = array_search('apellidos', $header);

                        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                            if (count($row) < 2) continue;
                            $nombres = trim($row[$idxNombre] ?? '');
                            $apellidos = trim($row[$idxApellido] ?? '');
                            if (empty($nombres) || empty($apellidos)) continue;

                            $estudiante = \App\Models\Estudiante::where('nombres', $nombres)
                                ->where('apellidos', $apellidos)
                                ->first();

                            if ($estudiante) {
                                if (is_null($estudiante->aula_id)) {
                                    $estudiante->update(['aula_id' => $aula->id]);
                                    $vinculados++;
                                } else {
                                    $omitidos++; // Ya tiene aula (sea esta u otra)
                                }
                            } else {
                                \App\Models\Estudiante::create([
                                    'nombres' => $nombres,
                                    'apellidos' => $apellidos,
                                    'aula_id' => $aula->id,
                                ]);
                                $creados++;
                            }
                        }

                        fclose($handle);
                        if (is_string($archivo)) Storage::disk('local')->delete($archivo);
                        
                        $aula->actualizarCantidadUsuarios();

                        Notification::make()
                            ->title('Importación completada')
                            ->body("Nuevos: {$creados} | Vinculados: {$vinculados} | Omitidos: {$omitidos}")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\CreateAction::make()
                    ->label('Crear Nuevo')
                    ->after(fn ($livewire) => $livewire->getOwnerRecord()->actualizarCantidadUsuarios()),

                // 3. AGREGAR MANUAL O VINCULAR (MEJORADO con BÚSQUEDA LIMITADA)
                Action::make('agregarEstudiantes')
                    ->label('Vincular / Agregar Manual')
                    ->icon('heroicon-m-user-plus')
                    ->modalWidth('2xl')
                    ->form([
                        // Opción 1: Buscar existentes (Optimizado con limit)
                        Forms\Components\Select::make('estudiante_existente_id')
                            ->label('Buscar estudiante existente (Sin aula)')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\Estudiante::whereNull('aula_id')
                                    ->where(function ($query) use ($search) {
                                        $query->where('nombres', 'like', "%{$search}%")
                                              ->orWhere('apellidos', 'like', "%{$search}%");
                                    })
                                    ->limit(10) // Limitamos a 10 resultados para rendimiento
                                    ->orderBy('apellidos')
                                    ->get()
                                    ->mapWithKeys(fn ($estudiante) => [$estudiante->id => "{$estudiante->apellidos} {$estudiante->nombres}"]);
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $e = \App\Models\Estudiante::find($value);
                                return $e ? "{$e->apellidos} {$e->nombres}" : null;
                            })
                            ->live()
                            ->helperText('Escribe para buscar. Se mostrarán máximo 10 resultados.'),

                        // Separador visual usando Placeholder
                        Forms\Components\Placeholder::make('separator')
                            ->hiddenLabel()
                            ->content(new HtmlString('<div class="h-px bg-gray-200 dark:bg-gray-700 my-4"></div>')),
                        
                        // Opción 2: Crear nuevos
                        Forms\Components\Fieldset::make('O Crear Nuevos')
                            ->schema([
                                Forms\Components\TextInput::make('cantidad')
                                    ->label('Cantidad nuevos')
                                    ->numeric()
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, callable $set) => $set('registros', array_fill(0, max(0, (int) $state), ['nombres' => '', 'apellidos' => '']))),
                                    
                                Forms\Components\Repeater::make('registros')
                                    ->label('Detalles')
                                    ->schema([
                                        Forms\Components\TextInput::make('nombres')->required(),
                                        Forms\Components\TextInput::make('apellidos')->required(),
                                    ])
                                    ->columns(2)
                                    ->rules(['required_without:estudiante_existente_id']),
                            ])
                            // Se oculta si eliges uno existente
                            ->hidden(fn(Forms\Get $get) => filled($get('estudiante_existente_id'))),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $aula = $livewire->getOwnerRecord();
                        $count = 0;

                        // 1. Vincular Uno Existente
                        if (!empty($data['estudiante_existente_id'])) {
                            $estudiante = \App\Models\Estudiante::find($data['estudiante_existente_id']);
                            if ($estudiante) {
                                $estudiante->update(['aula_id' => $aula->id]);
                                $count++;
                            }
                        } 
                        // 2. O Crear Nuevos (Repeater)
                        else {
                            foreach ($data['registros'] ?? [] as $item) {
                                if (empty($item['nombres']) && empty($item['apellidos'])) continue;
                                \App\Models\Estudiante::create([
                                    'nombres' => $item['nombres'], 
                                    'apellidos' => $item['apellidos'], 
                                    'aula_id' => $aula->id
                                ]);
                                $count++;
                            }
                        }
                        
                        if ($count === 0) return;

                        $aula->actualizarCantidadUsuarios(); 

                        Notification::make()->title("{$count} estudiantes procesados.")->success()->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('desvincular_individual')
                    ->label('Desvincular')
                    ->icon('heroicon-m-link-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Desvincular estudiante')
                    ->action(function (\App\Models\Estudiante $record, RelationManager $livewire) {
                        $record->update(['aula_id' => null]);
                        $livewire->getOwnerRecord()->actualizarCantidadUsuarios();
                        Notification::make()->title('Estudiante desvinculado')->success()->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->after(fn ($livewire) => $livewire->getOwnerRecord()->actualizarCantidadUsuarios()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('desvincular')
                        ->label('Desvincular Aula')
                        ->icon('heroicon-o-link-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Desvincular estudiantes')
                        ->action(function (Collection $records, RelationManager $livewire) {
                            \App\Models\Estudiante::whereIn('id', $records->pluck('id'))
                                ->update(['aula_id' => null]);
                            $livewire->getOwnerRecord()->actualizarCantidadUsuarios();
                            Notification::make()->title('Estudiantes desvinculados correctamente')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function ($livewire) {
                            $livewire->getOwnerRecord()->actualizarCantidadUsuarios();
                        }),
                ]),
            ]);
    }
}