<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesionMomento extends Model
{
    use HasFactory;
    protected $table = 'sesion_momentos';

    protected $fillable = ['sesion_id', 'inicio', 'desarrollo', 'cierre'];

    public function sesion()
    {
        return $this->belongsTo(Sesion::class);
    }
}
