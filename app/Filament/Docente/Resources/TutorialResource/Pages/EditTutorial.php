<?php

namespace App\Filament\Docente\Resources\TutorialResource\Pages;

use App\Filament\Docente\Resources\TutorialResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTutorial extends EditRecord
{
    protected static string $resource = TutorialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
