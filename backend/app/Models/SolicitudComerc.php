<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudComerc extends Model
{
    use HasFactory;

    protected $table = 'solicituds_comerc';

    protected $fillable = [
        'id_usuari',
        'id_categoria',
        'nom_comercial',
        'descripcio',
        'telefon',
        'email_contacte',
        'enllac_web',
        'instagram',
        'cif',
        'latitud',
        'longitud',
        'imatge_url',
        'estat',
    ];

    public function usuari()
    {
        return $this->belongsTo(Usuari::class, 'id_usuari', 'id_usuari');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }
}
