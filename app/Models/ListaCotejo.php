<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListaCotejo extends Model
{
    use HasFactory;
    protected $table = 'listas_cotejo';
    protected $fillable = [
        'sesion_id',
        'titulo',
        'descripcion',
        'niveles',
    ];

    public function sesion()
    {
        return $this->belongsTo(Sesion::class);
    }

}
