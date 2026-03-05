<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    use HasFactory;

    protected $table = 'perfils';
    protected $primaryKey = 'id_perfil'; 

    protected $fillable = [
        'id_usuari',
        'punts_totals',
        'imatge_url',
    ];

    // Pertany a un USUARI
    public function usuari()
    {
        return $this->belongsTo(Usuari::class, 'id_usuari', 'id_usuari');
    }
}