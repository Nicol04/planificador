<?php

namespace App\Filament\Resources\EnfoqueTransversalResource\Pages;

use App\Filament\Resources\EnfoqueTransversalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnfoqueTransversal extends EditRecord
{
    protected static string $resource = EnfoqueTransversalResource::class;

    protected function getHeaderActions(): array
    {
        return [
           // Actions\ViewAction::make(),
           // Actions\DeleteAction::make(),
        ];
    }
}
