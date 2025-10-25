<?php
namespace App\Filament\Docente\Resources\SesionResource\Schemas;

use Filament\Forms;

class MomentosSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Repeater::make('momentos')
                ->label('Momentos de la sesión')
                ->createItemButtonLabel('+ Agregar momento')
                ->schema([
                    Forms\Components\TextInput::make('nombre_momento')
                        ->label('Nombre del momento')
                        ->required()
                        ->placeholder('Ej: Inicio, Desarrollo, Cierre')
                        ->columnSpan('full'),

                    Forms\Components\Textarea::make('descripcion')
                        ->label('Descripción')
                        ->rows(3)
                        ->placeholder('¿Qué harás en este momento?')
                        ->columnSpan('full'),

                    Forms\Components\TextInput::make('duracion')
                        ->label('Duración (minutos)')
                        ->type('number')
                        ->minValue(1)
                        ->columnSpan('full'),

                    Forms\Components\Textarea::make('actividades')
                        ->label('Actividades')
                        ->rows(3)
                        ->placeholder('Describe las actividades específicas')
                        ->columnSpan('full'),

                    Forms\Components\TextInput::make('recursos')
                        ->label('Recursos necesarios')
                        ->placeholder('Ej: Pizarra, proyector, fichas')
                        ->columnSpan('full'),
                ])
                ->columns(1)
                ->columnSpan('full')
                ->defaultItems(3),
        ];
    }
}