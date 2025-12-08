<?php

namespace App\Filament\Docente\Resources;

use App\Filament\Docente\Resources\FichaAprendizajeResource\Pages;
use App\Filament\Docente\Resources\FichaAprendizajeResource\RelationManagers;
use App\Models\FichaAprendizaje;
use Filament\Forms;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FichaAprendizajeResource extends Resource
{
    protected static ?string $model = FichaAprendizaje::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Planificación de sesiones';
    protected static ?string $label = 'Mis fichas de aprendizaje';
    protected static ?string $navigationLabel = 'Mis fichas de aprendizaje';
    protected static ?string $pluralLabel = 'Mis fichas de aprendizaje';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                View::make('filament.docente.pages.ficha')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sesion_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('titulo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->searchable(),
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
                Tables\Actions\Action::make('preview')
                    ->label('Vista Previa')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (FichaAprendizaje $record): string => route('fichas.preview', ['fichaId' => $record->id]))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListFichaAprendizajes::route('/'),
            'create' => Pages\CreateFichaAprendizaje::route('/create'),
            'edit' => Pages\EditFichaAprendizaje::route('/{record}/edit'),
        ];
    }
}