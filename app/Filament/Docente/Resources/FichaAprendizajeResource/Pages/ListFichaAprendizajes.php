<?php

namespace App\Filament\Docente\Resources\FichaAprendizajeResource\Pages;

use App\Filament\Docente\Resources\FichaAprendizajeResource;
use App\Models\FichaAprendizaje;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListFichaAprendizajes extends ListRecords
{
    protected static string $resource = FichaAprendizajeResource::class;

    // Propiedades públicas para búsqueda/orden
    public $search = '';
    public $orderBy = 'created_desc';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getView(): string
    {
        return 'filament.docente.ficha_aprendizaje.list-ficha-aprendizaje-cards';
    }

    public function getFilteredFichas()
    {
        $query = FichaAprendizaje::query()
            ->where('user_id', Auth::id());

        // Filtro de búsqueda
        if ($this->search) {
            $query->where('titulo', 'like', '%' . $this->search . '%');
        }

        // Orden
        switch ($this->orderBy) {
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'created_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'titulo_asc':
                $query->orderBy('titulo', 'asc');
                break;
            case 'titulo_desc':
                $query->orderBy('titulo', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        // Solo relaciones existentes en el modelo
        return $query->with(['user'])->simplePaginate(12);
    }
}
