<?php

namespace App\Livewire;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
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
        $user = Auth::user();
        $persona = Auth::user()?->persona;

        $this->form->fill([
            'nombre' => $persona?->nombre,
            'apellido' => $persona?->apellido,
            'dni' => $persona?->dni,
            'genero' => $persona?->genero,
            'gemini_api_key' => $user?->gemini_api_key,
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

                Section::make('Configuración de Inteligencia Artificial')
                    ->description('Aquí puedes ingresar tu clave API de Gemini para habilitar las funciones de IA en tus planificaciones.')
                    ->schema([
                        Forms\Components\TextInput::make('gemini_api_key')
                            ->label('Clave API de Gemini')
                            ->password()
                            ->revealable() // 👁️ permite mostrar/ocultar
                            ->helperText('Tu clave se guardará de forma segura y encriptada.')
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn($state) => trim($state)), // Limpia espacios
                    ])
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = Auth::user();

        // Guardamos solo la clave de la API
        if (!empty($this->data['gemini_api_key'])) {
            $user->update([
                'gemini_api_key' => $this->data['gemini_api_key'],
            ]);

            Notification::make()
                ->title('✅ Clave API guardada correctamente')
                ->success()
                ->send();
        }
    }

    public function render(): View
    {
        return view('livewire.custom-profile-component');
    }
}
