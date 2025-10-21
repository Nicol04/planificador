<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnoResource\Pages;
use App\Filament\Resources\AnoResource\RelationManagers;
use App\Models\Ano;
use App\Models\Año;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnoResource extends Resource
{
    protected static ?string $model = Año::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    use Translatable;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->minLength(4)
                    ->maxLength(4)
                    ->rules(['regex:/^[0-9]{8}$/']) // solo 8 dígitos numéricos
                    ->validationMessages([
                        'regex' => 'Escribe el año Ejm. 2023, 2024.',
                    ]),
                Forms\Components\DatePicker::make('fecha_inicio')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_fin')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->date()
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
            'index' => Pages\ListAnos::route('/'),
            'create' => Pages\CreateAno::route('/create'),
            'edit' => Pages\EditAno::route('/{record}/edit'),
        ];
    }
}
