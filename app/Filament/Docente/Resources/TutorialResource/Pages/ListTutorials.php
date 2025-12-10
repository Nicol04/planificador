<?php

namespace App\Filament\Docente\Resources\TutorialResource\Pages;

use App\Filament\Docente\Resources\TutorialResource;
use App\Models\Tutorial;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTutorials extends ListRecords
{
    protected static string $resource = TutorialResource::class;

    public string $search = '';
    public string $filterCategoria = '';

    public function getView(): string
    {
        return 'filament.docente.tutoriales.list-tutoriales';
    }

    public function getHeading(): string
    {
        return '📺 Tutoriales y Guías';
    }

    public function getSubheading(): ?string
    {
        return 'Explora los tutoriales y guías disponibles para mejorar tu experiencia';
    }

    protected function getViewData(): array
    {
        return [
            'tutorials' => $this->getFilteredTutorials(),
            'categorias' => $this->getCategorias(),
        ];
    }

    public function getFilteredTutorials()
    {
        $query = Tutorial::query()->where('public', true);

        // Filtro de búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('titulo', 'like', '%' . $this->search . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            });
        }

        // Filtro de categoría
        if ($this->filterCategoria) {
            $query->where('categoria', $this->filterCategoria);
        }

        return $query->latest()->get();
    }

    public function getCategorias()
    {
        return Tutorial::query()
            ->where('public', true)
            ->distinct()
            ->pluck('categoria')
            ->filter()
            ->sort()
            ->values();
    }

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}