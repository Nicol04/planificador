<?php

namespace App\Filament\Docente\Resources\UnidadResource\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TagsInput;

class MaterialesSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('🎨 Materiales y Recursos')
                ->description('¿Qué necesitarás para desarrollar esta unidad?')
                ->icon('heroicon-o-cube')
                ->schema([
                    // Materiales físicos
                    Forms\Components\Textarea::make('materiales_basicos')
                        ->label('📦 Materiales Físicos')
                        ->rows(5)
                        ->placeholder(
                            "Escribe cada material en una línea nueva:\n\n" .
                                "Cartulinas\n" .
                                "Marcadores\n" .
                                "Tijeras\n" .
                                "Goma\n" .
                                "Papeles de colores"
                        )
                        ->helperText('Lista los materiales que usarás en clase')
                        ->columnSpanFull(),

                    // Recursos digitales/bibliográficos
                    Forms\Components\Textarea::make('recursos')
                        ->label('💻 Recursos Educativos')
                        ->rows(5)
                        ->placeholder(
                            "Escribe cada recurso en una línea nueva:\n\n" .
                                "Videos de YouTube\n" .
                                "Google Classroom\n" .
                                "Libro de matemáticas pág. 25-30\n" .
                                "Fichas de trabajo\n" .
                                "Proyector"
                        )
                        ->helperText('Agrega recursos digitales, libros o tecnología')
                        ->columnSpanFull(),

                ])
                ->columns(1)
                ->footerActions([
                    Forms\Components\Actions\Action::make('ejemplo')
                        ->label('📋 Ver ejemplo y copiar')
                        ->icon('heroicon-o-sparkles')
                        ->color('success')
                        ->action(function ($set) {
                            $set(
                                'materiales_basicos',
                                "Cartulinas\n" .
                                    "Marcadores\n" .
                                    "Papel bond\n" .
                                    "Tijeras\n" .
                                    "Goma\n" .
                                    "Lápices"
                            );

                            $set(
                                'recursos',
                                "Plataforma virtual\n" .
                                    "Videos educativos\n" .
                                    "Presentaciones\n" .
                                    "Fichas de trabajo\n" .
                                    "Libro del estudiante\n" .
                                    "Biblioteca del aula"
                            );
                        })
                        ->requiresConfirmation()
                        ->modalHeading('¿Usar este ejemplo?')
                        ->modalDescription('Se copiarán materiales comunes. Puedes editarlos después.')
                        ->modalSubmitActionLabel('Sí, copiar ejemplo')
                        ->modalIcon('heroicon-o-light-bulb'),
                ]),
        ];
    }
}
