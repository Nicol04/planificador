<?php

use App\Filament\Docente\Pages\LoginDocente;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth'])->group(function () {
    // Rutas protegidas por autenticación
    Route::get('/users/exportar', [UserController::class, 'exportarUsuarios'])->name('users.exportarUsuarios');
    //Reporte de usuarios por aula
    Route::get('/aulas/{aulaId}/exportar-usuarios', [UserController::class, 'exportarUsuariosPorAula'])->name('aulas.exportarUsuarios');
});
