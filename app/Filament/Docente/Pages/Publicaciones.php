<?php

namespace App\Filament\Docente\Pages;

use App\Models\Sesion;
use App\Models\Unidad;
use App\Models\FichaAprendizaje;
use Filament\Pages\Page;

class Publicaciones extends Page
{

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.docente.pages.publicaciones';
    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'sesiones' => Sesion::public()->get(),
            'unidades' => Unidad::public()->get(),
            'fichas' => FichaAprendizaje::where('public', true)->with(['user.persona'])->get(),
        ]);
    }
}
