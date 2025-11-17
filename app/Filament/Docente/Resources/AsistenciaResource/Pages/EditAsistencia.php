<?php

namespace App\Filament\Docente\Resources\AsistenciaResource\Pages;

use App\Filament\Docente\Resources\AsistenciaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsistencia extends EditRecord
{
    protected static string $resource = AsistenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('previsualizar')
                ->label('Previsualizar')
                ->icon('heroicon-o-eye')
                ->url(route('asistencias.previsualizar.show', $this->record->id))
                ->openUrlInNewTab(),
        ];
    }
}
