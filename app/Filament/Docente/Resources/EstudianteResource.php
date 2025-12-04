<?php

namespace App\Filament\Docente\Resources;

use App\Filament\Docente\Resources\EstudianteResource\Pages;
use App\Filament\Docente\Resources\EstudianteResource\RelationManagers;
use App\Models\Estudiante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class EstudianteResource extends Resource
{
    protected static ?string $model = Estudiante::class;
    protected static ?string $label = 'Mis estudiantes';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombres')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('apellidos')
                    ->required()
                    ->maxLength(255),
            ]);
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user) {
            $aulaIds = $user->usuario_aulas()->pluck('aula_id')->toArray();
            $query->whereIn('aula_id', $aulaIds);
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Apellidos y nombres')
                    ->getStateUsing(fn($record) => "{$record->apellidos} {$record->nombres}")
                    ->searchable(['apellidos', 'nombres'])
                    ->sortable(['apellidos', 'nombres'])
                    ->wrap()
                    ->limit(40),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // 1. DESCARGAR PLANTILLA
                Action::make('descargarPlantilla')
                    ->label('Descargar Plantilla')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('gray')
                    ->action(function () {
                        return response()->streamDownload(function () {
                            echo "\xEF\xBB\xBF"; // BOM para Excel
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, ['nombres', 'apellidos'], ';');
                            fputcsv($handle, ['Juan', 'Perez'], ';');
                            fputcsv($handle, ['Maria', 'Gomez'], ';');
                            fclose($handle);
                        }, 'plantilla_estudiantes.csv');
                    }),

                // 2. IMPORTAR DESDE EXCEL
                Action::make('importarEstudiantes')
                    ->label('Importar Excel')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->color('success')
                    ->modalWidth('4xl')
                    ->modalSubmitActionLabel('Confirmar e Importar')
                    ->form([
                        Forms\Components\FileUpload::make('archivo_csv')
                            ->label('Subir archivo Excel (CSV)')
                            ->helperText('Usa la plantilla. El sistema detecta comas o punto y coma automáticamente.')
                            ->disk('local')
                            ->directory('temp-imports')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                            ->required()
                            ->live()
                            ->validationMessages([
                                'required' => 'Sube un archivo para ver la vista previa.',
                            ]),

                        // VISTA PREVIA MEJORADA
                        Forms\Components\Placeholder::make('preview')
                            ->label('Vista Previa')
                            ->content(function (Forms\Get $get) {
                                $archivo = $get('archivo_csv');
                                if (!$archivo) return 'Esperando archivo...';

                                // Obtener Aula del Usuario para comparar
                                $user = Auth::user();
                                $usuarioAula = $user->usuario_aulas()->first();
                                $miAulaId = $usuarioAula ? $usuarioAula->aula_id : null;

                                if (is_array($archivo)) $archivo = reset($archivo);

                                $path = null;
                                if (is_object($archivo) && method_exists($archivo, 'getRealPath')) {
                                    $path = $archivo->getRealPath();
                                } elseif (is_string($archivo)) {
                                    $path = Storage::disk('local')->path($archivo);
                                }

                                if (!$path || !file_exists($path)) return 'Cargando archivo...';

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

                                // Tabla con estilos inline para evitar problemas de visualización
                                $html = '<div style="overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 0.5rem;">';
                                $html .= '<table style="width: 100%; border-collapse: collapse; font-size: 0.875rem; text-align: left;">';
                                $html .= '<thead style="background-color: #f9fafb; color: #374151; text-transform: uppercase;"><tr>';
                                $html .= '<th style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">Nombres</th>';
                                $html .= '<th style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">Apellidos</th>';
                                $html .= '<th style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">Estado Detectado</th>';
                                $html .= '</tr></thead><tbody>';

                                $count = 0;
                                while (($row = fgetcsv($handle, 1000, $delimiter)) !== false && $count < 20) {
                                    if (count($row) < 2) continue;
                                    $nom = trim($row[$idxNombre] ?? '');
                                    $ape = trim($row[$idxApellido] ?? '');
                                    if (!$nom || !$ape) continue;

                                    $existe = \App\Models\Estudiante::where('nombres', $nom)->where('apellidos', $ape)->first();

                                    $estadoTexto = 'Crear Nuevo';
                                    $estiloBadge = 'background-color: #dbeafe; color: #1e40af;'; // Azul

                                    if ($existe) {
                                        if (is_null($existe->aula_id)) {
                                            $estadoTexto = 'Vincular (Libre)';
                                            $estiloBadge = 'background-color: #dcfce7; color: #166534;'; // Verde
                                        } elseif ($existe->aula_id == $miAulaId) {
                                            $estadoTexto = 'Ya está en tu aula';
                                            $estiloBadge = 'background-color: #f3f4f6; color: #374151;'; // Gris
                                        } else {
                                            $estadoTexto = 'En otra aula (Omitir)';
                                            $estiloBadge = 'background-color: #fee2e2; color: #991b1b;'; // Rojo
                                        }
                                    }

                                    $html .= "<tr style='border-bottom: 1px solid #f3f4f6;'>";
                                    $html .= "<td style='padding: 12px 16px;'>$nom</td>";
                                    $html .= "<td style='padding: 12px 16px;'>$ape</td>";
                                    $html .= "<td style='padding: 12px 16px;'><span style='padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.75rem; $estiloBadge'>$estadoTexto</span></td>";
                                    $html .= "</tr>";
                                    $count++;
                                }
                                $html .= '</tbody></table></div>';
                                fclose($handle);
                                return new HtmlString($html);
                            })
                            ->hidden(fn(Forms\Get $get) => !$get('archivo_csv')),
                    ])
                    ->action(function (array $data) {
                        $user = Auth::user();
                        $usuarioAula = $user->usuario_aulas()->first();

                        if (!$usuarioAula) {
                            Notification::make()->title('Error: No tienes un aula asignada.')->danger()->send();
                            return;
                        }

                        $aula = \App\Models\Aula::find($usuarioAula->aula_id);

                        if (!$aula) {
                            Notification::make()->title('Error: El aula asignada no existe.')->danger()->send();
                            return;
                        }

                        $archivo = $data['archivo_csv'];
                        if (is_array($archivo)) $archivo = reset($archivo);

                        $path = Storage::disk('local')->path($archivo);
                        if (is_object($archivo) && method_exists($archivo, 'getRealPath')) $path = $archivo->getRealPath();

                        if (!file_exists($path)) {
                            Notification::make()->title('Error al leer archivo')->danger()->send();
                            return;
                        }

                        $handle = fopen($path, 'r');
                        $firstLine = fgets($handle);
                        rewind($handle);
                        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

                        $header = fgetcsv($handle, 1000, $delimiter);
                        $header = array_map(fn($h) => strtolower(trim(preg_replace('/[\x{FEFF}]+/u', '', $h))), $header);

                        $idxNombre = array_search('nombres', $header);
                        $idxApellido = array_search('apellidos', $header);

                        $creados = 0;
                        $vinculados = 0;

                        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                            if (count($row) < 2) continue;
                            $nom = trim($row[$idxNombre] ?? '');
                            $ape = trim($row[$idxApellido] ?? '');
                            if (!$nom || !$ape) continue;

                            $estudiante = \App\Models\Estudiante::where('nombres', $nom)->where('apellidos', $ape)->first();

                            if ($estudiante) {
                                // Solo vinculamos si NO tiene aula asignada
                                if (is_null($estudiante->aula_id)) {
                                    $estudiante->update(['aula_id' => $aula->id]);
                                    $vinculados++;
                                }
                                // Si ya tiene aula (aunque sea la mía), no hacemos nada
                            } else {
                                \App\Models\Estudiante::create([
                                    'nombres' => $nom,
                                    'apellidos' => $ape,
                                    'aula_id' => $aula->id
                                ]);
                                $creados++;
                            }
                        }

                        fclose($handle);
                        if (is_string($archivo)) Storage::disk('local')->delete($archivo);

                        $aula->actualizarCantidadUsuarios();

                        Notification::make()
                            ->title("Proceso completado")
                            ->body("Nuevos: $creados | Vinculados: $vinculados")
                            ->success()
                            ->send();
                    }),

                // 3. AGREGAR MANUAL O VINCULAR (MEJORADO con BÚSQUEDA LIMITADA)
                Tables\Actions\Action::make('agregar_estudiante')
                    ->label('Agregar Manual')
                    ->icon('heroicon-o-user-plus')
                    ->button()
                    ->modalHeading('Agregar o Vincular Estudiante')
                    ->modalWidth('2xl')
                    ->form([
                        // Opción 1: Buscar existentes (Optimizado con limit)
                        Forms\Components\Select::make('estudiante_existente_id')
                            ->label('Buscar estudiante existente (Sin aula)')
                            ->searchable() // Habilitar búsqueda AJAX
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\Estudiante::whereNull('aula_id')
                                    ->where(function ($query) use ($search) {
                                        $query->where('nombres', 'like', "%{$search}%")
                                            ->orWhere('apellidos', 'like', "%{$search}%");
                                    })
                                    ->limit(10) // <-- AQUÍ LIMITAMOS A 10 RESULTADOS
                                    ->orderBy('apellidos')
                                    ->get()
                                    ->mapWithKeys(fn($estudiante) => [$estudiante->id => "{$estudiante->apellidos} {$estudiante->nombres}"]);
                            })
                            ->getOptionLabelUsing(function ($value) {
                                // Esto es necesario para que muestre el nombre si el valor ya está seleccionado pero no está en los primeros 10
                                $e = \App\Models\Estudiante::find($value);
                                return $e ? "{$e->apellidos} {$e->nombres}" : null;
                            })
                            ->live()
                            ->helperText('Escribe el nombre para buscar. Se mostrarán máximo 10 resultados.'),

                        // Placeholder separador
                        Forms\Components\Placeholder::make('separator')
                            ->hiddenLabel()
                            ->content(new HtmlString('<div class="h-px bg-gray-200 dark:bg-gray-700 my-4"></div>')),

                        // Opción 2: Crear nuevo
                        Forms\Components\Fieldset::make('O Crear Nuevo Estudiante')
                            ->schema([
                                Forms\Components\TextInput::make('nombres')
                                    ->label('Nombres')
                                    ->required(fn(Forms\Get $get) => blank($get('estudiante_existente_id')))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('apellidos')
                                    ->label('Apellidos')
                                    ->required(fn(Forms\Get $get) => blank($get('estudiante_existente_id')))
                                    ->maxLength(255),
                            ])
                            ->visible(fn(Forms\Get $get) => blank($get('estudiante_existente_id'))),
                    ])
                    ->action(function (array $data) {
                        $user = Auth::user();
                        $usuarioAula = $user->usuario_aulas()->first();

                        if (!$usuarioAula) {
                            Notification::make()->title('No tienes aula asignada')->danger()->send();
                            return;
                        }

                        $aula = \App\Models\Aula::find($usuarioAula->aula_id);

                        if (!$aula) {
                            Notification::make()->title('Aula no encontrada')->danger()->send();
                            return;
                        }

                        // Lógica para decidir si vincular o crear
                        if (!empty($data['estudiante_existente_id'])) {
                            // CASO 1: VINCULAR
                            $estudiante = \App\Models\Estudiante::find($data['estudiante_existente_id']);
                            if ($estudiante) {
                                $estudiante->update(['aula_id' => $aula->id]);
                                Notification::make()->success()->title('Estudiante vinculado correctamente')->send();
                            }
                        } else {
                            // CASO 2: CREAR
                            \App\Models\Estudiante::create([
                                'nombres' => $data['nombres'],
                                'apellidos' => $data['apellidos'],
                                'aula_id' => $aula->id,
                            ]);
                            Notification::make()->success()->title('Estudiante creado y agregado')->send();
                        }

                        $aula->actualizarCantidadUsuarios();
                    })
                    ->color('info'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('desvincular')
                    ->label('Desvincular')
                    ->icon('heroicon-o-user-minus')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $aula = $record->aula;
                        $record->update(['aula_id' => null]);
                        if ($aula) $aula->actualizarCantidadUsuarios();
                    })
                    ->visible(fn($record) => !is_null($record->aula_id)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('desvincular')
                        ->label('Desvincular del aula')
                        ->action(function (Collection $records) {
                            $aula = $records->first()->aula;
                            \App\Models\Estudiante::whereIn('id', $records->pluck('id'))->update(['aula_id' => null]);
                            if ($aula) $aula->actualizarCantidadUsuarios();
                        })
                        ->requiresConfirmation()
                        ->icon('heroicon-o-user-minus'),
                ]),
            ]);
    }
    public static function canCreate(): bool
    {
        return false;
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
            'index' => Pages\ListEstudiantes::route('/'),
            //'create' => Pages\CreateEstudiante::route('/create'),
            //'edit' => Pages\EditEstudiante::route('/{record}/edit'),
        ];
    }
}
