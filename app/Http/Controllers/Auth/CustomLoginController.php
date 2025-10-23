<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('filament.auth.pages.login');
    }
    public function login(Request $request)
    {
        $credentials = $request->only('name', 'password');
        
        // Buscar el usuario por nombre
        $user = User::where('name', $credentials['name'])->first();
        
        if (!$user) {
            return back()->with([
                'mensaje' => 'Las credenciales proporcionadas no son correctas.',
                'icono' => 'error'
            ]);
        }
        
        // PRIMERO verificar si la contraseña es correcta
        if (!Hash::check($credentials['password'], $user->password)) {
            return back()->with([
                'mensaje' => 'Las credenciales proporcionadas no son correctas.',
                'icono' => 'error'
            ]);
        }
        
        // Verificar si tiene el rol de docente
        if (!$user->hasRole('docente')) {
            return back()->with([
                'mensaje' => 'No tienes permisos para acceder a esta área.',
                'icono' => 'error'
            ]);
        }
        // DESPUÉS verificar si el usuario está activo
        if ($user->estado !== 'Activo') {
            return back()->with([
                'mensaje' => 'Tu cuenta está inactiva. Contacta al administrador.',
                'icono' => 'warning'
            ]);
        }
        
        // Verificar si tiene el rol de docente
        if (!$user->hasRole('docente')) {
            return back()->with([
                'mensaje' => 'No tienes permisos para acceder a esta área.',
                'icono' => 'error'
            ]);
        }
        
        // Si llegamos aquí, todo está correcto, autenticar
        Auth::login($user);
        $request->session()->regenerate();
        
        return redirect()->intended('/docente')->with([
            'mensaje' => '¡Bienvenido! Has iniciado sesión correctamente.',
            'icono' => 'success'
        ]);
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('docente.login')->with([
            'mensaje' => 'Has cerrado sesión correctamente.',
            'icono' => 'success'
        ]);
    }
}
