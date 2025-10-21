<?php

namespace App\Filament\Resources\EnfoqueTransversalResource\Pages;

use App\Filament\Resources\EnfoqueTransversalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEnfoqueTransversal extends ViewRecord
{
    protected static string $resource = EnfoqueTransversalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
