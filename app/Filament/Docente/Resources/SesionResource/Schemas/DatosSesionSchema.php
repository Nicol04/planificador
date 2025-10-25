<?php

namespace App\Filament\Docente\Resources\SesionResource\Schemas;

use Filament\Forms;

class DatosSesionSchema
{
    public static function schema(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Datos de la Sesión')
            ->headerActions([
                Forms\Components\Actions\Action::make('usarHoy')
                    ->label('📅 Usar hoy')
                    ->action(function ($livewire) {
                        $hoy = now()->toDateString();
                        $dia = \Carbon\Carbon::parse($hoy)->locale('es')->isoFormat('dddd');
                        
                        $livewire->form->fill([
                            'fecha' => $hoy,
                            'dia' => ucfirst($dia),
                        ]);
                    }),
            ])
            ->description(fn($get) => $get('dia') ? '📅 ' . ucfirst($get('dia')) : '')
            ->schema([
                // Fila 1: Fecha y Tiempo Estimado
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('fecha')
                            ->label('Fecha')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $dia = \Carbon\Carbon::parse($state)->locale('es')->isoFormat('dddd');
                                    $set('dia', ucfirst($dia));
                                } else {
                                    $set('dia', null);
                                }
                            })
                            ->columnSpan(1),

                        Forms\Components\Select::make('tiempo_estimado')
                            ->label('Tiempo estimado')
                            ->options([
                                '30' => '30 minutos',
                                '60' => '60 minutos',
                                '90' => '90 minutos',
                                'custom' => 'Personalizado',
                            ])
                            ->required()
                            ->reactive()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('tiempo_custom')
                            ->label('Tiempo personalizado (min)')
                            ->type('number')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(300)
                            ->visible(fn($get) => $get('tiempo_estimado') === 'custom')
                            ->columnSpan(1),
                    ])
                    ->columnSpan('full'),

                // Fila 2: Título
                Forms\Components\TextInput::make('titulo')
                    ->label('Título de la sesión')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej: Fracciones equivalentes')
                    ->columnSpan('full'),

                // Fila 3: Propósito
                Forms\Components\Textarea::make('proposito_sesion')
                    ->label('Propósito de la sesión')
                    ->required()
                    ->rows(3)
                    ->placeholder('¿Qué aprenderán los estudiantes?')
                    ->columnSpan('full'),

                // Campo oculto para guardar el día
                Forms\Components\Hidden::make('dia')
                    ->dehydrated(),
            ])
            ->columns(1)
            ->columnSpan('full');
    }
}