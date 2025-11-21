<?php

namespace App\Filament\Docente\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use App\Models\Aula;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.docente.pages.dashboard';
    protected static ?string $navigationLabel = 'Inicio';
    protected static ?string $slug = '';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public $aulas;

    public function mount(): void
    {
        $user = Auth::user();
        
        // Obtener IDs de aulas a través de usuario_aulas
        $aulaIds = $user->usuario_aulas()->pluck('aula_id')->toArray();
        
        // Obtener aulas con conteo de estudiantes
        $this->aulas = Aula::whereIn('id', $aulaIds)
            ->withCount('estudiantes')
            ->get();
    }
}
