<?php

namespace App\Filament\Resources\AulaResource\RelationManagers;

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

    protected static ?string $title = 'Docente';
    protected static ?string $modelLabel = 'Docente';
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
                
                Action::make('exportarPorAula')
                    ->label('Exportar usuarios por Aula')
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
                            ->title('Docente desvinculado correctamente.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
