<?php

namespace App\Filament\Docente\Resources\ListaCotejoResource\Pages;

use App\Filament\Docente\Resources\ListaCotejoResource;
use App\Models\ListaCotejo;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListListaCotejos extends ListRecords
{
    protected static string $resource = ListaCotejoResource::class;

    public $search = '';
    public $orderBy = 'created_desc';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva lista')
                ->icon('heroicon-o-plus'),
        ];
    }

    /**
     * Oculta el título automático de Filament
     */
    public function getHeading(): string
    {
        return '';
    }

    public function getView(): string
    {
        return 'filament.docente.listas_cotejo.list-listacotejo-cards';
    }

    /**
     * Devuelve las listas de cotejo del docente, con filtros básicos.
     */
    public function getFilteredListas()
    {
        $query = ListaCotejo::query()
            ->whereHas('sesion', fn ($q) => $q->where('docente_id', Auth::id()));

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('titulo', 'like', '%' . $this->search . '%')
                  ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            });
        }

        switch ($this->orderBy) {
            case 'titulo_asc':
                $query->orderBy('titulo', 'asc');
                break;
            case 'titulo_desc':
                $query->orderBy('titulo', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query->with(['sesion'])->simplePaginate(12);
    }
}
