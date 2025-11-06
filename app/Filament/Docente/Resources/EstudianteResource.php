<?php

namespace App\Filament\Docente\Resources;

use App\Filament\Docente\Resources\EstudianteResource\Pages;
use App\Filament\Docente\Resources\EstudianteResource\RelationManagers;
use App\Models\Estudiante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class EstudianteResource extends Resource
{
    protected static ?string $model = Estudiante::class;
    protected static ?string $label = 'Mis estudiantes';
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

    // Aquí filtramos estudiantes por aula del docente autenticado
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
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            // BOTÓN EN LA CABECERA: agregar estudiante desde la misma página
            ->headerActions([
                Tables\Actions\Action::make('agregar_estudiante')
                    ->label('Agregar estudiante')
                    ->icon('heroicon-o-plus')
                    ->button()
                    ->modalHeading('Agregar estudiante')
                    ->modalSubheading('Completa nombres y apellidos. Se asignará a tu aula automáticamente')
                    ->form([
                        Forms\Components\TextInput::make('nombres')
                            ->label('Nombres')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('apellidos')
                            ->label('Apellidos')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (array $data) {
                        $user = Auth::user();
                        $aulaId = null;
                        if ($user) {
                            $aulas = $user->usuario_aulas()->pluck('aula_id')->toArray();
                            if (count($aulas) === 1) {
                                $aulaId = $aulas[0];
                            }
                        }

                        \App\Models\Estudiante::create([
                            'nombres' => $data['nombres'],
                            'apellidos' => $data['apellidos'],
                            'aula_id' => $aulaId,
                        ]);

                        // Notificación al docente
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Estudiante agregado')
                            ->send();
                    })
                    ->color('success'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('desvincular')
                    ->label('Desvincular')
                    ->icon('heroicon-o-user-minus')
                    ->requiresConfirmation()
                    ->modalHeading('Desvincular estudiante')
                    ->action(fn($record) => $record->update(['aula_id' => null]))
                    ->visible(fn($record) => ! is_null($record->aula_id)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('desvincular')
                        ->label('Desvincular del aula')
                        ->action(function (Collection $records) {
                            // Actualiza en una sola consulta para eficiencia
                            \App\Models\Estudiante::whereIn('id', $records->pluck('id'))->update([
                                'aula_id' => null,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Desvincular estudiantes del aula')
                        ->modalSubheading('Esto quitará la asignación de aula a los estudiantes seleccionados.')
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
            'edit' => Pages\EditEstudiante::route('/{record}/edit'),
        ];
    }
}
