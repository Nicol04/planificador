<?php

namespace App\Filament\Resources\EnfoqueTransversalResource\Pages;

use App\Filament\Resources\EnfoqueTransversalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnfoqueTransversals extends ListRecords
{
    protected static string $resource = EnfoqueTransversalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
