<?php

namespace App\Filament\Docente\Resources\TutorialResource\Pages;

use App\Filament\Docente\Resources\TutorialResource;
use App\Models\Tutorial;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTutorials extends ListRecords
{
    protected static string $resource = TutorialResource::class;

    public function getView(): string
    {
        return 'filament.docente.tutoriales.list-tutoriales';
    }

    protected function getViewData(): array
    {
        return [
            'tutorials' => Tutorial::query()
                ->where('public', true)
                ->latest()
                ->get(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}