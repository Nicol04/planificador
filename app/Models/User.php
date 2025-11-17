<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Filament\Tables\Columns\Layout\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasAvatar
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'estado',
        'password_plano',
        'persona_id',
        'avatar_url',
        'gemini_api_key',
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
            'gemini_api_key' => 'encrypted',
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
    public function plantillas()
    {
        return $this->hasMany(Plantilla::class);
    }
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'docente_id');
    }
    /**
     * Plantillas favoritas del usuario
     */
    public function favoritos()
    {
        return $this->belongsToMany(Plantilla::class, 'plantilla_user', 'user_id', 'plantilla_id')
                    ->withTimestamps();
    }
    public function getFilamentAvatarUrl(): ?string
    {
        $avatarColumn = config('filament-edit-profile.avatar_column', 'avatar_url');
        return $this->$avatarColumn ? Storage::url($this->$avatarColumn) : null;
    }
}
