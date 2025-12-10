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
                Forms\Components\TextInput::make('titulo')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(4)
                    ->nullable(),

                Forms\Components\Select::make('categoria')
                    ->label('Categoría')
                    ->required()
                    ->options([
                        'Unidad' => 'Unidad',
                        'Sesión' => 'Sesión',
                        'Perfil IA' => 'Perfil IA',
                        'Ficha de aprendizaje' => 'Ficha de aprendizaje',
                        'Asistencias' => 'Asistencias',
                        'Registro de estudiantes' => 'Registro de estudiantes',
                        'Listas de cotejo' => 'Listas de cotejo',
                        'Publicaciones' => 'Publicaciones',
                    ])
                    ->nullable(),

                Forms\Components\TextInput::make('video_url')
                    ->label('URL del Video')
                    ->required()
                    ->nullable(),

                Forms\Components\Select::make('public')
                    ->label('¿Es público?')
                    ->options([
                        0 => 'Para administrativos',
                        1 => 'Para docentes',
                    ])
                    ->required()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('categoria')
                    ->searchable(),
                Tables\Columns\TextColumn::make('video_url')
                    ->searchable(),
                Tables\Columns\IconColumn::make('public')
                    ->boolean(),
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
            'index' => Pages\ListTutorials::route('/'),
            'create' => Pages\CreateTutorial::route('/create'),
            'edit' => Pages\EditTutorial::route('/{record}/edit'),
        ];
    }
}
