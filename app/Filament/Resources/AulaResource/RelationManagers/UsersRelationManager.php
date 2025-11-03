<?php

namespace App\Filament\Admin\Resources\AulaResource\RelationManagers;

use App\Models\Año;
use App\Models\Estudiante;
use App\Models\User;
use App\Models\usuario_aula;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Usuarios del Aula';
    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with('persona', 'roles'))
            ->columns([
                Tables\Columns\TextColumn::make('persona.nombre')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('persona.apellido')
                    ->label('Apellido')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')->label('Rol')
                    ->formatStateUsing(fn($state) => ucfirst($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rol')
                    ->label('Filtrar por Rol')
                    ->options([
                        'estudiante' => 'Estudiante',
                        'docente' => 'Docente',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value']) && $data['value'] !== '') {
                            return $query->whereHas('roles', function ($q) use ($data) {
                                $q->where('name', $data['value']);
                            });
                        }
                        return $query;
                    })
            ])
            ->headerActions([
                Action::make('agregarEstudiantes')
                    ->label('Agregar / Vincular Estudiantes')
                    ->icon('heroicon-m-user-plus')
                    ->form([
                        Forms\Components\Select::make('existing_estudiante_ids')
                            ->label('Estudiantes existentes (sin aula)')
                            ->multiple()
                            ->options(fn() => \App\Models\Estudiante::whereNull('aula_id')
                                ->orderBy('nombres')
                                ->get()
                                ->mapWithKeys(fn($e) => [$e->id => "{$e->nombres} {$e->apellidos}"])
                                ->toArray())
                            ->helperText('Selecciona estudiantes ya registrados que no tengan aula asignada.'),
                        Forms\Components\TextInput::make('cantidad')
                            ->label('¿Cuántos quieres agregar?')
                            ->numeric()
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set) => $set('registros', array_fill(0, max(0, (int) $state), ['nombres' => '', 'apellidos' => '']))),
                        Forms\Components\Repeater::make('registros')
                            ->label('Nuevos Estudiantes')
                            ->schema([
                                Forms\Components\TextInput::make('nombres')
                                    ->label('Nombres')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('apellidos')
                                    ->label('Apellidos')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columns(1)
                            ->minItems(0)
                            ->dehydrated(true),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $aula = $livewire->getOwnerRecord();

                        // Vincular estudiantes existentes: asignar aula_id
                        $existingIds = $data['existing_estudiante_ids'] ?? [];
                        if (!empty($existingIds)) {
                            \App\Models\Estudiante::whereIn('id', $existingIds)
                                ->update(['aula_id' => $aula->id]);
                        }

                        // Crear nuevos estudiantes y asignar aula_id
                        foreach ($data['registros'] ?? [] as $item) {
                            $nombres = trim($item['nombres'] ?? '');
                            $apellidos = trim($item['apellidos'] ?? '');
                            if ($nombres === '' && $apellidos === '') {
                                continue;
                            }

                            \App\Models\Estudiante::create([
                                'nombres'  => $nombres,
                                'apellidos'=> $apellidos,
                                'aula_id'  => $aula->id,
                            ]);
                        }

                        // actualizar el conteo en el aula
                        $aula->actualizarCantidadUsuarios();

                        Notification::make()
                            ->title('Estudiantes vinculados/creados correctamente.')
                            ->success()
                            ->send();
                    }),
                Action::make('exportarPorAula')
                    ->label('Exportar Usuarios por Aula')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(RelationManager $livewire) => route('aulas.exportarUsuarios', ['aulaId' => $livewire->getOwnerRecord()->id]))
                    ->color('success'),
            ])
            ->actions([
                Action::make('desvincular')
                    ->label('Desvincular')
                    ->color('danger')
                    ->icon('heroicon-m-trash')
                    ->requiresConfirmation()
                    ->action(function ($record, RelationManager $livewire) {
                        $aula = $livewire->getOwnerRecord();

                        $año = Año::whereDate('fecha_inicio', '<=', now())
                            ->whereDate('fecha_fin', '>=', now())
                            ->first();

                        if (! $año) {
                            Notification::make()
                                ->title('No hay un año activo definido.')
                                ->danger()
                                ->send();
                            return;
                        }

                        usuario_aula::where('user_id', $record->id)
                            ->where('aula_id', $aula->id)
                            ->where('año_id', $año->id)
                            ->delete();

                        $aula->actualizarCantidadUsuarios();

                        Notification::make()
                            ->title('Usuario desvinculado correctamente.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
