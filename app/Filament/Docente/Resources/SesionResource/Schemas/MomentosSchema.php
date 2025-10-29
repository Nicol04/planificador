<?php
namespace App\Filament\Docente\Resources\SesionResource\Schemas;

use Filament\Forms;

class MomentosSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\View::make('filament.docente.sesion.momentos')
                ->columnSpan('full'),
        ];
    }
}