<?php

namespace App\Filament\Resources\AnoResource\Pages;

use App\Filament\Resources\AnoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAno extends EditRecord
{
    protected static string $resource = AnoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
