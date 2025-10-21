<?php

namespace App\Filament\Resources\CompetenciaTransversalResource\Pages;

use App\Filament\Resources\CompetenciaTransversalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCompetenciaTransversal extends ViewRecord
{
    protected static string $resource = CompetenciaTransversalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
