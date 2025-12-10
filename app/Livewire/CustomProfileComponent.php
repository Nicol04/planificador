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
            'search_api_key' => $user?->search_api_key,
            'id_search' => $user?->id_search,
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
                        Forms\Components\Placeholder::make('tutorial')
                            ->label('¿Cómo obtener tu clave API?')
                            ->content('1. Haz clic en el botón de abajo para ir a Google AI Studio.
                                2. Inicia sesión con tu cuenta de Google.
                                3. Haz clic en "Create API Key" o "Crear clave API".
                                4. Copia la clave generada y pégala en el campo de abajo.'),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('obtener_api_key')
                                ->label('🔑 Obtener clave API de Gemini')
                                ->url('https://aistudio.google.com/apikey')
                                ->openUrlInNewTab()
                                ->color('primary')
                                ->icon('heroicon-o-arrow-top-right-on-square'),

                            Forms\Components\Actions\Action::make('ver_video_tutorial')
                                ->label('📺 Ver video tutorial')
                                ->url('https://www.youtube.com/watch?v=TU_VIDEO_ID')
                                ->openUrlInNewTab()
                                ->color('danger')
                                ->icon('heroicon-o-play-circle'),
                        ]),

                        Forms\Components\TextInput::make('gemini_api_key')
                            ->label('Clave API de Gemini')
                            ->password()
                            ->revealable() // 👁️ permite mostrar/ocultar
                            ->helperText('Tu clave se guardará de forma segura y encriptada.')
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn($state) => trim($state)), // Limpia espacios

                        Forms\Components\TextInput::make('search_api_key')
                            ->label('Clave API de Búsqueda')
                            ->password()
                            ->revealable() // 👁️ permite mostrar/ocultar
                            ->helperText('Tu clave se guardará de forma segura y encriptada.')
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn($state) => trim($state)), // Limpia espacios
                        Forms\Components\TextInput::make('id_search')
                            ->label('ID de Búsqueda')
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn($state) => trim($state)), // Limpia espacios
                    ])

            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = Auth::user();

        // Guardamos las claves de la API
        $dataToUpdate = [];

        if (!empty($this->data['gemini_api_key'])) {
            $dataToUpdate['gemini_api_key'] = $this->data['gemini_api_key'];
        }

        if (!empty($this->data['search_api_key'])) {
            $dataToUpdate['search_api_key'] = $this->data['search_api_key'];
        }

        if (!empty($this->data['id_search'])) {
            $dataToUpdate['id_search'] = $this->data['id_search'];
        }

        if (!empty($dataToUpdate)) {
            $user->update($dataToUpdate);

            Notification::make()
                ->title('✅ Configuración guardada correctamente')
                ->success()
                ->send();
        }
    }

    public function render(): View
    {
        return view('livewire.custom-profile-component');
    }
}
