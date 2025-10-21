<?php

namespace App\Filament\Resources\CompetenciaTransversalResource\Pages;

use App\Filament\Resources\CompetenciaTransversalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompetenciaTransversals extends ListRecords
{
    protected static string $resource = CompetenciaTransversalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
