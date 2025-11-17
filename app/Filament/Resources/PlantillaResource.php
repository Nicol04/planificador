<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlantillaResource\Pages;
use App\Models\Plantilla;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PlantillaResource extends Resource
{
    protected static ?string $model = Plantilla::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::id()),
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('tipo')
                    ->required()
                    ->options([
                        'asistencia' => 'Asistencia',
                        'otra' => 'Otra',
                    ])
                    ->label('Tipo de plantilla'),

                Forms\Components\Toggle::make('public')
                    ->label('Público para docentes')
                    ->default(false),

                Forms\Components\FileUpload::make('archivo')
                    ->required()
                    ->label('Archivo (Word)'),

                Forms\Components\FileUpload::make('imagen_preview')
                    ->imageEditor()
                    ->disk('public')
                    ->directory('plantillas')
                    ->label('Imagen de previsualización')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('tipo')->sortable(),
                Tables\Columns\ImageColumn::make('imagen_preview')->label('Preview'),
                Tables\Columns\TextColumn::make('user')
                    ->label('Subido por')
                    ->formatStateUsing(fn($state, $record) => $record->user && $record->user->persona
                        ? ($record->user->persona->nombre . ' ' . $record->user->persona->apellidos)
                        : '—'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'asistencia' => 'Asistencia',
                        'otra' => 'Otra',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlantillas::route('/'),
            'create' => Pages\CreatePlantilla::route('/create'),
            'edit' => Pages\EditPlantilla::route('/{record}/edit'),
        ];
    }
}
