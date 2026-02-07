<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TutorialResource\Pages;
use App\Filament\Resources\TutorialResource\RelationManagers;
use App\Models\Tutorial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TutorialResource extends Resource
{
    protected static ?string $model = Tutorial::class;
    protected static ?string $navigationLabel = 'Tutoriales';
    protected static ?string $navigationIcon = 'heroicon-o-play-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->description('Datos básicos del tutorial')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('titulo')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Cómo crear una sesión')
                            ->helperText('Título claro y descriptivo')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(4)
                            ->maxLength(500)
                            ->placeholder('Describe el contenido del tutorial...')
                            ->helperText('Máximo 500 caracteres')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Configuración')
                    ->description('Categoria y enlace del video')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Select::make('categoria')
                            ->label('Categoría')
                            ->required()
                            ->options([
                                'Unidad' => '📚 Unidad',
                                'Sesión' => '📝 Sesión',
                                'Perfil IA' => '🤖 Perfil IA',
                                'Ficha de aprendizaje' => '📋 Ficha de aprendizaje',
                                'Asistencias' => '✅ Asistencias',
                                'Registro de estudiantes' => '👥 Registro de estudiantes',
                                'Listas de cotejo' => '☑️ Listas de cotejo',
                                'Publicaciones' => '📢 Publicaciones',
                            ])
                            ->searchable()
                            ->preload()
                            ->helperText('Selecciona la categoría apropiada'),

                        Forms\Components\TextInput::make('video_url')
                            ->label('URL del Video')
                            ->required()
                            ->url()
                            ->placeholder('https://www.youtube.com/watch?v=...')
                            ->helperText('YouTube u otra plataforma de video')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('public')
                            ->label('Visibilidad')
                            ->options([
                                0 => '🔒 Solo Administrativos',
                                1 => '🌐 Visible para Docentes',
                            ])
                            ->required()
                            ->default(0)
                            ->helperText('¿Quién puede ver este tutorial?'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->description(fn ($record) => $record->descripcion ? substr($record->descripcion, 0, 60) . '...' : ''),

                Tables\Columns\BadgeColumn::make('categoria')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->colors([
                        'primary' => 'Unidad',
                        'success' => 'Sesión',
                        'warning' => 'Perfil IA',
                        'info' => 'Ficha de aprendizaje',
                    ]),

                Tables\Columns\TextColumn::make('public')
                    ->label('Audiencia')
                    ->formatStateUsing(fn ($state) => $state ? '👥 Docentes' : '🔒 Administrativo')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria')
                    ->label('Categoría')
                    ->options([
                        'Unidad' => 'Unidad',
                        'Sesión' => 'Sesión',
                        'Perfil IA' => 'Perfil IA',
                        'Ficha de aprendizaje' => 'Ficha de aprendizaje',
                        'Asistencias' => 'Asistencias',
                        'Registro de estudiantes' => 'Registro de estudiantes',
                        'Listas de cotejo' => 'Listas de cotejo',
                        'Publicaciones' => 'Publicaciones',
                    ]),

                Tables\Filters\TernaryFilter::make('public')
                    ->label('Visibilidad')
                    ->placeholder('Todas')
                    ->trueLabel('Docentes')
                    ->falseLabel('Administrativos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListTutorials::route('/'),
            'create' => Pages\CreateTutorial::route('/create'),
            'edit' => Pages\EditTutorial::route('/{record}/edit'),
        ];
    }
}
