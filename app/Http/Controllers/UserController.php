<?php

namespace App\Http\Controllers;

use App\Exports\ExportCalificacionesEstudiante;
use App\Exports\ExportUser;
use App\Models\Avatar_usuarios;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportUserAula;

class UserController extends Controller
{
    public function exportarUsuarios()
    {
        return Excel::download(new ExportUser, 'usuarios.xlsx');
    }
    public function exportarUsuariosPorAula($aulaId)
    {
        return Excel::download(new ExportUserAula($aulaId), 'usuarios_por_aula.xlsx');
    }
}
