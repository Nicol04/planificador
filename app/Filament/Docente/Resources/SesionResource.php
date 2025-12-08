<?php

namespace App\Filament\Docente\Resources;

use App\Filament\Docente\Resources\SesionResource\Pages;
use App\Filament\Docente\Resources\SesionResource\Schemas\DatosSesionSchema;
use App\Filament\Docente\Resources\SesionResource\Schemas\EnfoquesSchema;
use App\Filament\Docente\Resources\SesionResource\Schemas\ProposAprSchema;
use App\Models\Sesion;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SesionResource extends Resource
{
    protected static ?string $model = Sesion::class;
    protected static ?string $label = '📅 Mis Sesiones';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Planificación de sesiones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Datos de la Sesión')
                        ->schema([
                            DatosSesionSchema::schema(),
                            ProposAprSchema::schema(),
                            Forms\Components\Section::make('Enfoques Transversales')
                                ->schema(EnfoquesSchema::schema())
                                ->description('Enfoques transversales aplicados')
                                ->icon('heroicon-o-light-bulb')
                                ->collapsible(),
                        ])
                        ->description('Información básica de la sesión')
                        ->icon('heroicon-o-document-text'),
                    Step::make('Momentos de la Sesión')
                        ->schema(function (callable $get) {
                            return [
                                ViewField::make('momentos')
                                    ->view('filament.docente.sesion.momentos')
                                    ->viewData([
                                        'datosSesion' => $get('data'),
                                    ]),
                            ];
                        })
                        ->description('Información de los momentos')
                ])
                    ->statePath('data')
                    ->columnSpanFull()
                    ->persistStepInQueryString()
                    ->skippable(fn() => $form->getOperation() === 'edit')

                    ->submitAction(
                        Action::make('create')
                            ->label(fn() => $form->getOperation() === 'create' ? 'Crear Sesión' : 'Guardar Cambios')
                            ->icon('heroicon-o-check')
                            ->submit('create')
                            ->keyBindings(['mod+s'])
                    )
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
