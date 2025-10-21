<?php

namespace App\Filament\Docente\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginDocente extends Page
{
    protected static string $view = 'filament.docente.login';

    public ?string $name = null;
    public ?string $password = null;
    public bool $remember = false;

    protected array $rules = [
        'name' => ['required', 'string'],
        'password' => ['required', 'string'],
    ];
    public function getAuthUsername(): string
    {
        return 'name';
    }

    public function authenticate(): void
    {
        $this->validate();

        if (!Auth::attempt(['name' => $this->name, 'password' => $this->password], $this->remember)) {
            $this->dispatch('authFailed');
            throw ValidationException::withMessages([
                'name' => 'Credenciales incorrectas.',
            ]);
        }

        session()->regenerate();
        redirect()->intended('/docente');
    }
    
}
