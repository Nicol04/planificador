<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EstudianteResource\Pages;
use App\Filament\Resources\EstudianteResource\RelationManagers;
use App\Models\Aula;
use App\Models\Estudiante;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EstudianteResource extends Resource
{
    protected static ?string $model = Estudiante::class;
    protected static ?string $navigationLabel = 'Administrar estudiantes';
    protected static ?string $navigationGroup = 'Gestión de usuarios';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Personal')
                    ->schema([
                        Forms\Components\TextInput::make('nombres')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('apellidos')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Asignación de Aula')
                    ->schema([
                        // SELECT DE GRADO (Virtual)
                        Forms\Components\Select::make('grado_temp')
                            ->label('Grado')
                            ->options(fn() => \App\Models\Aula::query()
                                ->distinct()
                                ->pluck('grado', 'grado'))
                            ->live() // Hace que se recargue el formulario al cambiar
                            ->dehydrated(false) // No intenta guardar 'grado_temp' en la tabla estudiantes
                            ->required()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                // Al entrar a EDITAR, llenamos este campo basado en la relación
                                if ($record && $record->aula) {
                                    $component->state($record->aula->grado);
                                }
                            })
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Si cambia el grado, limpiamos la sección y el ID
                                $set('seccion_temp', null);
                                $set('aula_id', null);
                            }),

                        // SELECT DE SECCIÓN (Virtual)
                        Forms\Components\Select::make('seccion_temp')
                            ->label('Sección')
                            ->options(function (Forms\Get $get) {
                                $grado = $get('grado_temp');
                                if (!$grado) return [];

                                return \App\Models\Aula::where('grado', $grado)
                                    ->pluck('seccion', 'seccion');
                            })
                            ->live()
                            ->dehydrated(false) // No se guarda en BD
                            ->required()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                // Al entrar a EDITAR, llenamos este campo
                                if ($record && $record->aula) {
                                    $component->state($record->aula->seccion);
                                }
                            })
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                // Lógica principal: Buscar el ID del aula
                                $grado = $get('grado_temp');
                                $seccion = $state;

                                if ($grado && $seccion) {
                                    $aula = \App\Models\Aula::where('grado', $grado)
                                        ->where('seccion', $seccion)
                                        ->first();

                                    if ($aula) {
                                        $set('aula_id', $aula->id);
                                    }
                                }
                            }),

                        // CAMPO REAL (El que se guarda en BD)
                        Forms\Components\TextInput::make('aula_id')
                            ->label('ID Aula (Automático)')
                            ->required()
                            ->readOnly() // O ->disabled()
                            ->dehydrated() // Asegura que se envíe al guardar aunque esté disabled
                            ->helperText('Se asigna automáticamente al seleccionar Grado y Sección'),

                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombres')
                    ->searchable(),
                Tables\Columns\TextColumn::make('apellidos')
                    ->searchable(),
                Tables\Columns\TextColumn::make('aula.grado_seccion')
                    ->label('Aula')
                    ->sortable(query: function ($query, string $direction) {
                        // Lógica personalizada para ordenar por relación si fuera necesario
                        return $query->orderByPowerJoins('aula.grado', $direction)
                            ->orderByPowerJoins('aula.seccion', $direction);
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('aula_id')
                    ->label('Filtrar por Aula')
                    ->searchable() // Permite escribir "5" y ver las de 5to
                    ->options(fn() => \App\Models\Aula::all()->mapWithKeys(function ($aula) {
                        // Esto crea opciones tipo: [ID => "Grado - Sección"]
                        return [$aula->id => "{$aula->grado} - {$aula->seccion} ({$aula->nivel})"];
                    })->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            // AQUÍ AGREGAMOS LA ACCIÓN MASIVA EN LA CABECERA DE LA TABLA
            ->headerActions([
                Action::make('crear_masivo')
                    ->label('Registro Masivo por Aula')
                    ->icon('heroicon-o-users')
                    ->color('primary')
                    ->form([
                        // 1. SELECCIÓN DE AULA (Lógica similar a la que hicimos antes)
                        Forms\Components\Section::make('Seleccionar Aula')
                            ->schema([
                                Forms\Components\Select::make('grado')
                                    ->options(fn() => Aula::query()->distinct()->pluck('grado', 'grado'))
                                    ->live()
                                    ->required()
                                    ->afterStateUpdated(fn(Forms\Set $set) => $set('seccion', null)),

                                Forms\Components\Select::make('seccion')
                                    ->options(function (Forms\Get $get) {
                                        $grado = $get('grado');
                                        if (!$grado) return [];
                                        return Aula::where('grado', $grado)->pluck('seccion', 'seccion');
                                    })
                                    ->required()
                                    ->live(),
                            ])->columns(2),

                        // 2. LISTA DE ESTUDIANTES (Repeater)
                        Forms\Components\Repeater::make('estudiantes')
                            ->label('Lista de Estudiantes')
                            ->schema([
                                Forms\Components\TextInput::make('nombres')
                                    ->required(),
                                Forms\Components\TextInput::make('apellidos')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar otro estudiante'),
                    ])
                    ->action(function (array $data) {
                        // 1. Buscar el ID del Aula una sola vez
                        $aula = Aula::where('grado', $data['grado'])
                            ->where('seccion', $data['seccion'])
                            ->first();

                        if (!$aula) {
                            Notification::make()
                                ->title('Error')
                                ->body('No se encontró el aula seleccionada.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // 2. Recorrer el repeater y crear estudiantes
                        $count = 0;
                        foreach ($data['estudiantes'] as $estudianteData) {
                            Estudiante::create([
                                'nombres' => $estudianteData['nombres'],
                                'apellidos' => $estudianteData['apellidos'],
                                'aula_id' => $aula->id, // Usamos el ID del aula encontrada
                            ]);
                            $count++;
                        }

                        // 3. Actualizar contador del aula (si usas ese método)
                        $aula->actualizarCantidadUsuarios();

                        // 4. Notificar éxito
                        Notification::make()
                            ->title('Éxito')
                            ->body("Se han registrado {$count} estudiantes en el aula {$aula->grado} - {$aula->seccion}.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('desvincular')
                        ->label('Desvincular Aula')
                        ->icon('heroicon-o-link-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Desvincular estudiantes')
                        ->modalDescription('Se eliminará la asignación de aula de los estudiantes seleccionados.')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {

                            \App\Models\Estudiante::whereIn('id', $records->pluck('id'))
                                ->update(['aula_id' => null]);

                            \Filament\Notifications\Notification::make()
                                ->title('Estudiantes desvinculados correctamente')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListEstudiantes::route('/'),
            'create' => Pages\CreateEstudiante::route('/create'),
            //'edit' => Pages\EditEstudiante::route('/{record}/edit'),
        ];
    }
}
