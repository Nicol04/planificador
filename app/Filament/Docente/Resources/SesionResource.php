<?php

namespace App\Filament\Docente\Resources;

use App\Filament\Docente\Resources\SesionResource\Pages;
use App\Filament\Docente\Resources\SesionResource\RelationManagers;
use App\Filament\Docente\Resources\SesionResource\Schemas\DatosSesionSchema;
use App\Filament\Docente\Resources\SesionResource\Schemas\EnfoquesSchema;
use App\Filament\Docente\Resources\SesionResource\Schemas\ProposAprSchema;
use App\Models\Capacidad;
use App\Models\CapacidadTransversal;
use App\Models\Competencia;
use App\Models\CompetenciaTransversal;
use App\Models\Desempeno;
use App\Models\EnfoqueTransversal;
use App\Models\Sesion;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\View;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Hidden;
class SesionResource extends Resource
{
    protected static ?string $model = Sesion::class;
    protected static ?string $label = 'Sesiones';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatosSesionSchema::schema(),
                ProposAprSchema::schema(),
                Forms\Components\Section::make('Enfoques Transversales')
                    ->schema(EnfoquesSchema::schema()),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('titulo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tema')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tiempo_estimado')
                    ->searchable(),
                Tables\Columns\TextColumn::make('aula_curso_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('docente_id')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListSesions::route('/'),
            'create' => Pages\CreateSesion::route('/create'),
            'edit' => Pages\EditSesion::route('/{record}/edit'),
        ];
    }
}
