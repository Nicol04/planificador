<?php

use App\Http\Controllers\Documents\UnidadDocumentController;
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
    Route::get('/unidades/{id}/vista-previa', [UnidadDocumentController::class, 'vistaPreviaHtml'])
        ->name('unidades.vista.previa');
    Route::get('/unidades/{id}/previsualizar', [UnidadDocumentController::class, 'previsualizar'])
        ->name('unidades.previsualizar');
    // 🔍 RUTA DE DEBUG TEMPORAL
    Route::get('/unidades/{id}/debug', [UnidadDocumentController::class, 'debug'])
        ->name('unidades.debug');
});
