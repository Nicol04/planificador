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
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 1;

    public $aulas;

    public function mount(): void
    {
        $user = Auth::user();
        $this->aulas = $user->aulas()->get();
    }
}
