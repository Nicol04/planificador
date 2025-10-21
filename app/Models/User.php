<?php

namespace App\Models;

use Filament\Tables\Columns\Layout\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'estado',
        'password_plano',
        'persona_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }
    public function usuario_aulas()
    {
        return $this->hasMany(usuario_aula::class);
    }
    public function aulas()
    {
        return $this->belongsToMany(Aula::class, 'usuario_aulas')
            ->withTimestamps()
            ->withPivot('año_id');
    }
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('super_admin') || $this->can('panel_user');
    }
}
