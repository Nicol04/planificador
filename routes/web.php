<?php

use App\Http\Controllers\AjaxFichaAprendizajeController;
use App\Http\Controllers\Docente\PlantillaController;
use App\Http\Controllers\Documents\AsistenciaDocumentController;
use App\Http\Controllers\Documents\ListasCotejoDocumentController;
use App\Http\Controllers\Documents\SesionDocumentController;
use App\Http\Controllers\Documents\UnidadDocumentController;
use App\Http\Controllers\EjercicioSessionController;
use App\Http\Controllers\FichaEjercicioController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth'])->group(function () {
    // Rutas protegidas por autenticación

    // RUTAS PARA RECUPERAR GENERACIÓN DE IA
    // Ruta AJAX para guardar valores de SesionMomento en sesión
    Route::post('/sesion-momento/session', [\App\Http\Controllers\SesionMomentoSessionController::class, 'store'])->name('sesion-momento.session.store');

    // Ruta GET para obtener el momento de sesión por id
    Route::get('/sesion-momento/{sesionId}', [\App\Http\Controllers\SesionMomentoSessionController::class, 'showById'])->name('sesion-momento.session.showById');

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

    //Ruta para la previsualización de asistencias

    Route::post('/asistencias/previsualizar', [AsistenciaDocumentController::class, 'previsualizar'])
        ->name('asistencias.previsualizar');

    Route::get('/asistencias/{id}/previsualizar', [AsistenciaDocumentController::class, 'previsualizar'])
        ->whereNumber('id')
        ->name('asistencias.previsualizar.show');

    Route::get('asistencias/{id}/download', [AsistenciaDocumentController::class, 'descargarDocx'])
        ->name('asistencias.download');

    //Rutas para las plantillas
    //Sesiones
    Route::post('/docente/sesion/{id}/plantilla', [PlantillaController::class, 'PlantillaSesion'])
        ->name('docente.sesion.plantilla');

    // Sincronización de fichas de aprendizaje con ejercicios:
    
    Route::prefix('ajax/fichas-aprendizaje')->group(function () {
        Route::get('/', [AjaxFichaAprendizajeController::class, 'index']);
        Route::get('/{id}', [AjaxFichaAprendizajeController::class, 'show']);
        Route::post('/', [AjaxFichaAprendizajeController::class, 'store']);
        Route::put('/{id}', [AjaxFichaAprendizajeController::class, 'update']);
        Route::delete('/{id}', [AjaxFichaAprendizajeController::class, 'destroy']);
    });

    // Rutas para gestión de ejercicios en sesión (NO persiste en BD)
    Route::prefix('session/ejercicios')->name('session.ejercicios.')->group(function () {
        Route::get('/', [EjercicioSessionController::class, 'index'])->name('index');
        Route::post('/', [EjercicioSessionController::class, 'store'])->name('store');
        Route::get('/{id}', [EjercicioSessionController::class, 'show'])->name('show');
        Route::put('/{id}', [EjercicioSessionController::class, 'update'])->name('update');
        Route::patch('/{id}/content', [EjercicioSessionController::class, 'updateContent'])->name('updateContent');
        Route::delete('/{id}', [EjercicioSessionController::class, 'destroy'])->name('destroy');
        Route::delete('/', [EjercicioSessionController::class, 'clear'])->name('clear');
        Route::post('/replace-all', [EjercicioSessionController::class, 'replaceAll'])->name('replaceAll');
    });

    // Ruta para obtener ejercicios de una FichaAprendizaje desde BD
    Route::get('/fichas/{fichaId}/ejercicios', [FichaEjercicioController::class, 'getEjercicios'])->name('fichas.ejercicios');

});
Route::get('/docente/login', [App\Http\Controllers\Auth\CustomLoginController::class, 'showLoginForm'])->name('docente.login');
Route::post('/docente/login', [App\Http\Controllers\Auth\CustomLoginController::class, 'login'])->name('docente.login.submit');
Route::post('/docente/logout', [App\Http\Controllers\Auth\CustomLoginController::class, 'logout'])->name('filament.docente.auth.logout');
Route::get('/filament/docente/auth/login', [App\Http\Controllers\Auth\CustomLoginController::class, 'showLoginForm'])->name('filament.docente.auth.login');
