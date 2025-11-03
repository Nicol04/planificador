<?php

use App\Http\Controllers\Documents\AsistenciaDocumentController;
use App\Http\Controllers\Documents\ListasCotejoDocumentController;
use App\Http\Controllers\Documents\SesionDocumentController;
use App\Http\Controllers\Documents\UnidadDocumentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth'])->group(function () {
    // Rutas protegidas por autenticación
    Route::get('/users/exportar', [UserController::class, 'exportarUsuarios'])->name('users.exportarUsuarios');
    //Reporte de usuarios por aula
    Route::get('/aulas/{aulaId}/exportar-usuarios', [UserController::class, 'exportarUsuariosPorAula'])->name('aulas.exportarUsuarios');
    // Rutas para la generación de documentos de unidades
    Route::get('/unidades/{id}/vista-previa', [UnidadDocumentController::class, 'vistaPreviaHtml'])
        ->name('unidades.vista.previa');
    Route::get('/unidades/{id}/previsualizar', [UnidadDocumentController::class, 'previsualizar'])
        ->name('unidades.previsualizar');
    // 🔍 RUTA DE DEBUG TEMPORAL
    Route::get('/unidades/{id}/debug', [UnidadDocumentController::class, 'debug'])
        ->name('unidades.debug');
    // Rutas para la generación de documentos de sesiones
    Route::get('/sesiones/{id}/vista-previa', [SesionDocumentController::class, 'vistaPreviaHtml'])
        ->name('sesiones.vista.previa');
    Route::get('/sesiones/{id}/previsualizar', [SesionDocumentController::class, 'previsualizar'])
        ->name('sesiones.previsualizar');

    //Ruta para listas de cotejo

    Route::get('/listas-cotejo/{id}/vista-previa', [\App\Http\Controllers\Documents\ListasCotejoDocumentController::class, 'vistaPreviaHtml'])
        ->name('listas-cotejo.vista.previa');

    Route::get('/listas-cotejo/{id}/previsualizar', [\App\Http\Controllers\Documents\ListasCotejoDocumentController::class, 'previsualizar'])
        ->name('listas-cotejo.previsualizar');
        
        
Route::get('/documentos/asistencias/vista-previa-html', [AsistenciaDocumentController::class, 'vistaPreviaHtml'])
    ->name('asistencias.vista.previa');

Route::get('/documentos/asistencias/previsualizar/{id?}', [AsistenciaDocumentController::class, 'previsualizar'])
    ->name('asistencias.previsualizar');
});
Route::get('/docente/login', [App\Http\Controllers\Auth\CustomLoginController::class, 'showLoginForm'])->name('docente.login');
Route::post('/docente/login', [App\Http\Controllers\Auth\CustomLoginController::class, 'login'])->name('docente.login.submit');
Route::post('/docente/logout', [App\Http\Controllers\Auth\CustomLoginController::class, 'logout'])->name('filament.docente.auth.logout');
Route::get('/filament/docente/auth/login', [App\Http\Controllers\Auth\CustomLoginController::class, 'showLoginForm'])->name('filament.docente.auth.login');