<?php

namespace App\Livewire;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasSort;

class CustomProfileComponent extends Component implements HasForms
{
    use InteractsWithForms;
    use HasSort;

    public ?array $data = [];

    protected static int $sort = 1; // orden en el perfil

    // Este es el label que se mostrará como título del bloque
    public static function getLabel(): string
    {
        return 'Información Personal';
    }

    public static function getSort(): int
    {
        return self::$sort;
    }

    public function mount(): void
    {
        $persona = Auth::user()?->persona;

        $this->form->fill([
            'nombre' => $persona?->nombre,
            'apellido' => $persona?->apellido,
            'dni' => $persona?->dni,
            'genero' => $persona?->genero,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Personal')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')->label('Nombre')->disabled(),
                        Forms\Components\TextInput::make('apellido')->label('Apellido')->disabled(),
                        Forms\Components\TextInput::make('dni')->label('DNI')->disabled(),
                        Forms\Components\TextInput::make('genero')->label('Género')->disabled(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        // solo lectura, no necesitas guardar
    }

    public function render(): View
    {
        return view('livewire.custom-profile-component');
    }
}
