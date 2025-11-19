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

    
}
