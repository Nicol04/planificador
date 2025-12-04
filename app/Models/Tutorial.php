<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    use HasFactory;

    protected $table = 'tutoriales';

    protected $fillable = [
        'titulo',
        'descripcion',
        'categoria',
        'video_url',
        'public',
    ];

    protected $casts = [
        'public' => 'boolean',
    ];
}