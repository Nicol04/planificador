<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    use Translatable;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Administrar usuarios';
    protected static ?string $navigationGroup = 'Gestión de usuarios';
    protected static ?string $label = 'Usuario';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //ROLES
                Radio::make('role_id')
                    ->label('Rol')
                    ->options(
                        \Spatie\Permission\Models\Role::where('id', '!=', 1)->pluck('name', 'id')
                    )
                    ->required()
                    ->inline()
                    ->reactive()
                    ->afterStateHydrated(function ($set, $state, $record) {
                        if (!$state && $record) {
                            $role = $record->roles()->first();
                            if ($role) {
                                $set('role_id', $role->id);
                            }
                        }
                    }),

                Forms\Components\Section::make('Datos de la Persona')
                    ->description('Datos personales.')
                    ->schema([
                        Fieldset::make()
                            ->relationship('persona')
                            ->columns(3)
                            ->schema([
                                TextInput::make('nombre')
                                    ->required()
                                    ->maxLength(60)
                                    ->live(),
                                TextInput::make('apellido')
                                    ->required()
                                    ->maxLength(60)
                                    ->live(),
                                TextInput::make('dni')
                                    ->required()
                                    ->maxLength(8)
                                    ->minLength(8)
                                    ->rules(['regex:/^[0-9]{8}$/'])
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'regex' => 'El DNI debe contener exactamente 8 dígitos numéricos.',
                                        'unique' => 'El DNI ya está registrado en el sistema.',
                                    ]),
                                Radio::make('genero')
                                    ->options([
                                        'Masculino' => 'Masculino',
                                        'Femenino' => 'Femenino',
                                    ])
                                    ->inline()
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Forms\Components\Section::make('Información General')
                    ->columns(3)
                    ->description('Datos principales de usuario.')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->disabled()
                            ->maxLength(20)
                            ->dehydrated(true)
                            ->label('Nombre de usuario'),

                        \Filament\Forms\Components\Actions::make([
                            \Filament\Forms\Components\Actions\Action::make('Generar nombre')
                                ->icon('heroicon-o-arrow-path')
                                ->action(function ($get, $set) {
                                    $nombre = $get('persona.nombre');
                                    $apellidoCompleto = $get('persona.apellido');

                                    if ($nombre && $apellidoCompleto) {
                                        $nombres = explode(' ', trim($nombre));
                                        $primerNombre = $nombres[0] ?? '';
                                        $inicialNombre = substr($primerNombre, 0, 1);

                                        $apellidos = explode(' ', trim($apellidoCompleto));
                                        $apellidoPaterno = $apellidos[0] ?? '';
                                        $inicialSegundoApellido = substr($apellidos[1] ?? '', 0, 1);

                                        // Concatenar y convertir todo a minúscula
                                        $base = strtolower($apellidoPaterno . $inicialNombre . $inicialSegundoApellido);
                                        $base = substr($base, 0, 10);

                                        // Aplicar solo la primera letra en mayúscula
                                        $username = ucfirst($base);

                                        // Validar duplicados
                                        $contador = 1;
                                        $original = $username;
                                        while (\App\Models\User::where('name', $username)->exists()) {
                                            $username = ucfirst(substr($base . $contador, 0, 10));
                                            $contador++;
                                        }

                                        $set('name', $username);
                                    }
                                }),
                        ]),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(40)
                            ->unique(ignoreRecord: true)
                            ->required(function (callable $get) {
                                $roleId = $get('role_id');
                                if (!$roleId) return false;

                                $role = \Spatie\Permission\Models\Role::find($roleId);
                                return in_array(strtolower($role?->name), ['admin', 'super_admin']);
                            })
                            ->label('Correo electrónico'),

                        Forms\Components\Hidden::make('password_plano')
                            ->dehydrated(true)
                            ->default(fn($get) => $get('password') ? encrypt($get('password')) : null),

                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->maxLength(10)
                            ->required(fn(string $context) => $context === 'create')
                            ->dehydrateStateUsing(fn($state) => $state ? bcrypt($state) : null)
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $set('password_plano', encrypt($state));
                                }
                            })

                            ->dehydrated(fn($state) => filled($state))
                            ->helperText('Deja vacío para mantener la contraseña actual cuando edites.')
                            ->extraAttributes(['x-data' => '{ show: false }'])
                            ->extraInputAttributes([
                                'x-bind:type' => "show ? 'text' : 'password'"
                            ])
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('toggle')
                                    ->label('')
                                    ->icon('heroicon-o-eye')
                                    ->extraAttributes(['x-on:click' => 'show = !show'])
                            ),

                        Select::make('estado')
                            ->required()
                            ->options([
                                'Activo' => 'Activo',
                                'Inactivo' => 'Inactivo',
                            ]),
                    ]),

                Forms\Components\Section::make('Datos de las aulas')
                    ->visible(fn(callable $get) => $get('role_id') && !in_array((int) $get('role_id'), [1, 4]))
                    ->schema([
                        Forms\Components\Repeater::make('usuario_aulas')

                            ->relationship('usuario_aulas')
                            ->columns(3)
                            ->maxItems(1)
                            ->label(false)
                            ->deletable(false)
                            ->schema([
                                Forms\Components\Select::make('grado')
                                    ->label('Grado')
                                    ->options(
                                        \App\Models\Aula::query()->pluck('grado', 'grado')->unique()
                                    )
                                    ->reactive()
                                    ->required()
                                    ->afterStateHydrated(function ($state, $set, $record) {
                                        if (!$state && $record?->aula_id) {
                                            $aula = \App\Models\Aula::find($record->aula_id);
                                            if ($aula) {
                                                $set('grado', $aula->grado);
                                            }
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        $set('seccion', null);
                                        $set('aula_id', null);
                                    }),

                                Forms\Components\Select::make('seccion')
                                    ->label('Sección')
                                    ->options(
                                        fn(callable $get) =>
                                        \App\Models\Aula::where('grado', $get('grado'))
                                            ->pluck('seccion', 'seccion')
                                    )
                                    ->reactive()
                                    ->required()
                                    ->afterStateHydrated(function ($state, $set, $record) {
                                        if (!$state && $record?->aula_id) {
                                            $aula = \App\Models\Aula::find($record->aula_id);
                                            if ($aula) {
                                                $set('seccion', $aula->seccion);
                                            }
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        $grado = $get('grado');
                                        $seccion = $state;
                                        if ($grado && $seccion) {
                                            $aula = \App\Models\Aula::where('grado', $grado)
                                                ->where('seccion', $seccion)
                                                ->first();

                                            if ($aula) {
                                                $set('aula_id', $aula->id);
                                            } else {
                                                $set('aula_id', null);
                                            }
                                        }
                                    }),
                                TextInput::make('aula_id')
                                    ->disabled()
                                    ->dehydrated(true),
                                Forms\Components\Hidden::make('año_id')
                                    ->default(fn() => \App\Models\Año::latest('id')->first()?->id),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                if ($user->hasRole('admin')) {
                    // Si es admin, excluye admin y super_admin
                    $query->whereDoesntHave('roles', function ($q) {
                        $q->whereIn('name', ['admin', 'super_admin']);
                    });
                } elseif ($user->hasRole('super_admin')) {
                    // Si es super_admin, excluye solo a otros super_admin
                    $query->whereDoesntHave('roles', function ($q) {
                        $q->where('name', 'super_admin');
                    });
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre de usuario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('persona.nombre')
                    ->sortable()
                    ->searchable()
                    ->label('Nombres y apellidos'),
                Tables\Columns\TextColumn::make('role.name')
                    ->label('Rol')
                    ->getStateUsing(function ($record) {
                        return $record->roles->first()?->name ?? 'Sin rol';
                    }),
                Tables\Columns\TextColumn::make('password_plano')
                    ->label('Contraseña')
                    ->getStateUsing(function ($record) {
                        try {
                            return $record->password_plano ? decrypt($record->password_plano) : '';
                        } catch (\Exception $e) {
                            return '[Contraseña inválida]';
                        }
                    })
                    ->visible(fn() => Auth::user()?->hasRole(['admin', 'super_admin']))
                    ->copyable()
                    ->copyMessage('Contraseña copiada')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estado'),
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
                SelectFilter::make('rol')
                    ->label('Rol')
                    ->options(
                        Role::where('id', '!=', 1)->pluck('name', 'id')->toArray()
                    )
                    ->modifyQueryUsing(function ($query, $state) {
                        if (filled($state['value'])) {
                            $query->whereHas('roles', function ($q) use ($state) {
                                $q->where('id', $state['value']);
                            });
                        }
                    }),
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'Activo' => 'Activo',
                        'Inactivo' => 'Inactivo',
                    ])
                    ->modifyQueryUsing(function (Builder $query, $state) {
                        if (filled($state['value'])) {
                            $query->where('estado', $state['value']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('cambiar_estado')
                    ->label(fn($record) => $record->estado === 'Activo' ? 'Desactivar' : 'Activar')
                    ->icon(fn($record) => $record->estado === 'Activo' ? 'heroicon-o-user-minus' : 'heroicon-o-check-circle')
                    ->color(fn($record) => $record->estado === 'Activo' ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn($record) => $record->estado === 'Activo' ? '¿Desactivar usuario?' : '¿Activar usuario?')
                    ->modalDescription(fn($record) => $record->estado === 'Activo'
                        ? 'El usuario perderá acceso al sistema.'
                        : 'El usuario recuperará el acceso al sistema.')
                    // Lógica de visibilidad unificada:
                    // Siempre visible EXCEPTO si es el usuario actual O es super_admin y está intentando desactivarse
                    ->visible(function ($record) {
                        // Si está inactivo, siempre se puede activar (asumiendo permisos generales)
                        if ($record->estado === 'Inactivo') return true;

                        // Si está activo, aplicamos las reglas de restricción para desactivar
                        if (Auth::user()->id === $record->id) return false; // No te puedes desactivar a ti mismo
                        if ($record->roles->pluck('name')->contains('super_admin')) return false; // No tocar super admins

                        return true;
                    })
                    ->action(function ($record) {
                        $nuevoEstado = $record->estado === 'Activo' ? 'Inactivo' : 'Activo';
                        $record->update(['estado' => $nuevoEstado]);
                    }),

            ])->headerActions([
                Tables\Actions\Action::make('exportar')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(route('users.exportarUsuarios'))
                    ->openUrlInNewTab(false)
                    ->color('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('Activar seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->estado === 'Inactivo') {
                                    $record->update(['estado' => 'Activo']);
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('Desactivar seleccionados')
                        ->icon('heroicon-o-user-minus')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (
                                    Auth::user()->id !== $record->id &&
                                    !$record->roles->pluck('name')->contains('super_admin')
                                ) {
                                    $record->update(['estado' => 'Inactivo']);
                                }
                            }
                        }),

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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
