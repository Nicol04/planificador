<?php

namespace App\Filament\Resources\TutorialResource\Pages;

use App\Filament\Resources\TutorialResource;
use App\Models\Tutorial;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;

class ListTutorials extends ListRecords
{
    protected static string $resource = TutorialResource::class;

    #[Url]
    public ?string $search = '';

    #[Url]
    public ?string $selectedCategory = '';

    /**
     * Define dinámicamente la vista según el rol del usuario.
     */
    public function getView(): string
    {
        $user = Auth::user();
        
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return 'filament-panels::resources.pages.list-records';
        }

        return 'filament.admin.tutoriales.list-tutorial';
    }

    /**
     * Obtiene todos los tutoriales sin restricción.
     */
    protected function getTableQuery(): ?Builder
    {
        return Tutorial::query();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Nuevo Tutorial'),
        ];
    }
    
    /**
     * Obtiene la consulta de los tutoriales aplicando filtros (USADA POR LA VISTA PERSONALIZADA).
     */
    protected function getFilteredQuery(): Builder
    {
        $query = Tutorial::query();

        $query->when($this->search, function (Builder $query) {
            $query->where(function (Builder $q) {
                $q->where('titulo', 'like', '%' . $this->search . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            });
        });

        $query->when($this->selectedCategory, function (Builder $query) {
            $query->where('categoria', $this->selectedCategory);
        });
            
        return $query->latest();
    }

    /**
     * Método público llamado desde la vista para obtener los tutoriales.
     */
    public function getRecords(): Collection|\Illuminate\Contracts\Pagination\Paginator
    {
        return $this->getFilteredQuery()->paginate(12);
    }

    /**
     * Obtiene todas las categorías disponibles para el filtro.
     */
    public function getAllCategories(): Collection
    {
        return Tutorial::query()
            ->distinct()
            ->pluck('categoria')
            ->filter();
    }
}