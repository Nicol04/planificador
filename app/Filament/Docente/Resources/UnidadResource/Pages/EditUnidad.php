<?php

namespace App\Filament\Docente\Resources\UnidadResource\Pages;

use App\Filament\Docente\Resources\UnidadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUnidad extends EditRecord
{
    protected static string $resource = UnidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Elimina la acción de borrar
            // Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $detalle = $this->record->detalles()->first();

        if ($detalle) {
            $data['contenido'] = $detalle->contenido ?? [];
            $data['enfoques'] = $detalle->enfoques ?? [];
            $data['materiales_basicos'] = $detalle->materiales_basicos ?? '';
            $data['recursos'] = $detalle->recursos ?? '';
            $data['cronograma'] = $detalle->cronograma ?? [];
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $unidad = $this->record;

        $unidad->detalles()->updateOrCreate(
            ['unidad_id' => $unidad->id],
            [
                'contenido' => $this->data['contenido'] ?? [],
                'enfoques' => $this->data['enfoques'] ?? [],
                'materiales_basicos' => $this->data['materiales_basicos'] ?? '',
                'recursos' => $this->data['recursos'] ?? '',
                'cronograma' => $this->data['cronograma'] ?? [],
            ]
        );
    }

    protected function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Wizard::make([
                \Filament\Forms\Components\Wizard\Step::make('Datos Generales')
                    ->schema(\App\Filament\Docente\Resources\UnidadResource\Schemas\DatosUnidadSchema::schema())
                    ->description('📋 Información básica de la unidad')
                    ->icon('heroicon-o-document-text')
                    ->completedIcon('heroicon-o-check-circle'),

                \Filament\Forms\Components\Wizard\Step::make('Contenido Curricular')
                    ->schema(\App\Filament\Docente\Resources\UnidadResource\Schemas\ContenidoCurricularSchema::schema())
                    ->description('📚 Cursos, competencias y desempeños')
                    ->icon('heroicon-o-academic-cap')
                    ->completedIcon('heroicon-o-check-circle'),

                \Filament\Forms\Components\Wizard\Step::make('Enfoques Transversales')
                    ->schema(\App\Filament\Docente\Resources\UnidadResource\Schemas\EnfoquesSchema::schema())
                    ->description('🌟 Valores y actitudes a promover')
                    ->icon('heroicon-o-light-bulb')
                    ->completedIcon('heroicon-o-check-circle'),

                \Filament\Forms\Components\Wizard\Step::make('Cronograma de Actividades')
                    ->schema(\App\Filament\Docente\Resources\UnidadResource\Schemas\CronogramaSchema::schema())
                    ->description('📅 Secuencia de sesiones')
                    ->icon('heroicon-o-calendar-days')
                    ->completedIcon('heroicon-o-check-circle'),
                // ---------------------------------------------------------------

                \Filament\Forms\Components\Wizard\Step::make('Materiales y Recursos')
                    ->schema(\App\Filament\Docente\Resources\UnidadResource\Schemas\MaterialesSchema::schema())
                    ->description('🎨 Recursos necesarios para la unidad')
                    ->icon('heroicon-o-cube')
                    ->completedIcon('heroicon-o-check-circle'),
            ])
                ->columnSpanFull()
                ->persistStepInQueryString()
                ->startOnStep(1)
                ->skippable()
        ];
    }
}
