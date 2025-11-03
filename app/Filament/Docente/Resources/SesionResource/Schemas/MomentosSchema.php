<?php
namespace App\Filament\Docente\Resources\SesionResource\Schemas;

use Filament\Forms;
use Filament\Forms\Components\TextInput;

class MomentosSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\View::make('filament.docente.sesion.momentos')
                ->columnSpanFull(),            
        ];
    }
}