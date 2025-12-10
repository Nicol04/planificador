<?php

namespace App\Filament\Pages;

use App\Models\FichaAprendizaje;
use App\Models\Sesion;
use App\Models\Unidad;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class Publicaciones extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    
    protected static string $view = 'filament.pages.publicaciones';

    protected static ?string $title = 'Publicaciones';
    
    protected static ?string $navigationLabel = 'Publicaciones';

    protected static ?int $navigationSort = 2;

    public string $activeTab = 'unidades';

    public function mount(): void
    {
        $this->activeTab = 'unidades';
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getUnidadesPublicas(): Collection
    {
        return Unidad::where('public', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSesionesPublicas(): Collection
    {
        return Sesion::where('public', true)
            ->with(['docente.persona'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getFichasPublicas(): Collection
    {
        return FichaAprendizaje::where('public', true)
            ->with(['user.persona'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener el nombre completo del docente desde la relación persona
     */
    public function getNombreDocente($docente): string
    {
        if (!$docente) {
            return 'Sin docente asignado';
        }

        $persona = $docente->persona;
        
        if (!$persona) {
            return $docente->name ?? 'Docente sin nombre';
        }

        $nombreCompleto = trim(($persona->nombre ?? '') . ' ' . ($persona->apellido ?? ''));
        
        return $nombreCompleto ?: 'Docente sin nombre';
    }
}
