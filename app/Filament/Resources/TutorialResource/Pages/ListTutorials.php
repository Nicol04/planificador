<?php

namespace App\Filament\Resources\TutorialResource\Pages;

use App\Filament\Resources\TutorialResource;
use Filament\Actions;
// CAMBIO CRUCIAL: Ahora extendemos de ListRecords para la funcionalidad de tabla
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;

// CAMBIO CRUCIAL: Extiende de ListRecords
class ListTutorials extends ListRecords
{
    protected static string $resource = TutorialResource::class;

    // Propiedades de Livewire para el filtrado (se mantienen para la vista personalizada)
    #[Url]
    public ?string $search = '';

    #[Url]
    public ?string $selectedCategory = '';

    // ELIMINAMOS la propiedad $view estática, la definimos condicionalmente en getView()
    // protected static string $view = 'filament.admin.tutoriales.list-tutorial'; 

    /**
     * Define dinámicamente la vista según el rol del usuario.
     */
    public function getView(): string
    {
        $user = Auth::user();
        
        // Verifica si el usuario es Super Administrador (asumiendo que el método hasRole existe)
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
             // Si es Super Admin, usamos la vista de tabla estándar de ListRecords
            return 'filament-panels::resources.pages.list-records';
        }

        // Si es Administrativo, usamos la vista personalizada con las tarjetas.
        return 'filament.admin.tutoriales.list-tutorial';
    }

    /**
     * Define el query base para el Super Admin (Tabla) y para los Administrativos (Tarjetas).
     * Este método se usa por defecto como base para todas las consultas.
     */
    protected function getEloquentQuery(): Builder
    {
        // Aplicamos el filtro de base de datos a ambos casos
        return parent::getEloquentQuery()
            ->where('public', false); // Filtrar solo administrativos
    }

    protected function getHeaderActions(): array
    {
        // Al extender ListRecords, la acción de crear funciona por defecto sin URL explícita
        return [
            Actions\CreateAction::make()
                ->label('Crear Nuevo Tutorial'),
        ];
    }
    
    // NOTA: getTableQuery() ya no es necesario si usas getEloquentQuery() para el filtro base.
    
    /**
     * Obtiene la consulta de los tutoriales aplicando filtros (USADA POR LA VISTA PERSONALIZADA).
     */
    protected function getFilteredQuery(): Builder
    {
        // Usamos el query base (con public=false) como punto de partida
        $query = $this->getEloquentQuery(); 

        // Aplicar filtro de búsqueda por título/descripción
        $query->when($this->search, function (Builder $query) {
            $query->where(function (Builder $q) {
                $q->where('titulo', 'like', '%' . $this->search . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            });
        });

        // Aplicar filtro de categoría
        $query->when($this->selectedCategory, function (Builder $query) {
            $query->where('categoria', $this->selectedCategory);
        });
            
        return $query->latest(); // Ordenar por más reciente
    }

    /**
     * Método público llamado desde la vista ($this->getRecords()) para obtener los tutoriales.
     */
    public function getRecords(): Collection|\Illuminate\Contracts\Pagination\Paginator
    {
        // Usado por la vista personalizada para las tarjetas
        return $this->getFilteredQuery()->paginate(12); // Paginación de 12 tutoriales por página
    }

    /**
     * Obtiene todas las categorías disponibles para el filtro.
     */
    public function getAllCategories(): Collection
    {
        // Usamos el query base (getEloquentQuery) para asegurar el filtro 'public=false'
        return $this->getEloquentQuery()
            ->distinct()
            ->pluck('categoria');
    }
}