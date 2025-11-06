<?php

namespace App\Filament\Resources\CompetenciaTransversalResource\Pages;

use App\Filament\Resources\CompetenciaTransversalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompetenciaTransversal extends EditRecord
{
    protected static string $resource = CompetenciaTransversalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\ViewAction::make(),
           // Actions\DeleteAction::make(),
        ];
    }
}
