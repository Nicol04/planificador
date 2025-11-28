<?php

namespace App\Filament\Docente\Resources;

use App\Filament\Docente\Resources\UnidadResource\Schemas\ContenidoCurricularSchema;
use App\Filament\Docente\Resources\UnidadResource\Schemas\DatosUnidadSchema;
use App\Filament\Docente\Resources\UnidadResource\Schemas\EnfoquesSchema;
use App\Filament\Docente\Resources\UnidadResource\Schemas\MaterialesSchema;
use App\Filament\Docente\Resources\UnidadResource\Pages;
use App\Filament\Docente\Resources\UnidadResource\RelationManagers;
use App\Filament\Docente\Resources\UnidadResource\Schemas\CronogramaSchema;
use App\Models\Unidad;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class UnidadResource extends Resource
{
    protected static ?string $model = Unidad::class;
    protected static ?string $label = 'Unidades';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([
                Step::make('Datos Generales')
                    ->schema(DatosUnidadSchema::schema())
                    ->description('📋 Información básica de la unidad')
                    ->icon('heroicon-o-document-text')
                    ->completedIcon('heroicon-o-check-circle'),

                Step::make('Contenido Curricular')
                    ->schema(ContenidoCurricularSchema::schema())
                    ->description('📚 Cursos, competencias y desempeños')
                    ->icon('heroicon-o-academic-cap')
                    ->completedIcon('heroicon-o-check-circle'),

                Step::make('Enfoques Transversales')
                    ->schema(EnfoquesSchema::schema())
                    ->description('🌟 Valores y actitudes a promover')
                    ->icon('heroicon-o-light-bulb')
                    ->completedIcon('heroicon-o-check-circle'),

                Step::make('Cronograma de Sesiones')
                    ->schema(CronogramaSchema::schema())
                    ->description('🗓️ Programación de sesiones de la unidad')
                    ->icon('heroicon-o-calendar')
                    ->completedIcon('heroicon-o-check-circle'),
                
                Step::make('Materiales y Recursos')
                    ->schema(MaterialesSchema::schema())
                    ->description('🎨 Recursos necesarios para la unidad')
                    ->icon('heroicon-o-cube')
                    ->completedIcon('heroicon-o-check-circle'),
            ])
            ->columnSpanFull()
            ->persistStepInQueryString()
            ->startOnStep(1)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre de la Unidad')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grado')
                    ->label('Grado')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Fecha Fin')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // ACCIÓN DE PREVISUALIZACIÓN CON MODAL NATIVO
                Action::make('previsualizar')
                    ->label('Previsualizar')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('📄 Vista Previa del Documento')
                    ->modalDescription('Seleccione el formato para previsualizar:')
                    ->modalSubmitActionLabel('Vista Previa Vertical')
                    ->modalCancelActionLabel('Vista Previa Horizontal')
                    ->action(function ($record) {
                        return redirect()->to(route('unidades.vista.previa', ['id' => $record->id, 'orientacion' => 'vertical']));
                    })
                    ->cancelParentActions()
                    ->extraModalFooterActions([
                        Action::make('horizontal')
                            ->label('Vista Previa Horizontal')
                            ->icon('heroicon-o-document')
                            ->color('primary')
                            ->action(function ($record) {
                                return redirect()->to(route('unidades.vista.previa', ['id' => $record->id, 'orientacion' => 'horizontal']));
                            })
                            ->close(),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Elimina la acción de borrar masivo
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canEdit($record): bool
    {
        return Auth::check();
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnidads::route('/'),
            'create' => Pages\CreateUnidad::route('/create'),
            'edit' => Pages\EditUnidad::route('/{record}/edit'),
        ];
    }
}
