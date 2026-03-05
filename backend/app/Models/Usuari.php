<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuari extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuaris';
    protected $primaryKey = 'id_usuari'; 

    protected $fillable = [
        'correu',
        'contrasenya',
        'rol',
        'estat',
    ];

    protected $hidden = [
        'contrasenya', // Amaguem la contrasenya per seguretat
    ];

    // Relació 1:1 estricta amb PERFIL 
    public function perfil()
    {
        return $this->hasOne(Perfil::class, 'id_usuari', 'id_usuari');
    }

    // Relació 1:N amb COMERÇ 
    public function comercos()
    {
        return $this->hasMany(Comerc::class, 'id_usuari', 'id_usuari');
    }

    // Relació 1:N amb SOL_ALTA 
    public function solAltas()
    {
        return $this->hasMany(SolAlta::class, 'id_usuari', 'id_usuari');
    }

    // Relació 1:N amb TRANSACCIO 
    public function transaccions()
    {
        return $this->hasMany(Transaccio::class, 'id_usuari', 'id_usuari');
    }
}