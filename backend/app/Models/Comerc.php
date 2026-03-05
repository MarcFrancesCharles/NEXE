<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comerc extends Model
{
    use HasFactory;

    protected $table = 'comercs';
    protected $primaryKey = 'id_comerc'; 

    protected $fillable = [
        'id_usuari',
        'id_categoria',
        'nom_comercial',
        'cif',
        'coord_gps',
    ];

    // Pertany a un USUARI 
    public function usuari()
    {
        return $this->belongsTo(Usuari::class, 'id_usuari', 'id_usuari');
    }

    // Pertany a una CATEGORIA 
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    // Té moltes OFERTES 
    public function ofertes()
    {
        return $this->hasMany(Oferta::class, 'id_comerc', 'id_comerc');
    }

    // Té moltes TRANSACCIONS 
    public function transaccions()
    {
        return $this->hasMany(Transaccio::class, 'id_comerc', 'id_comerc');
    }
}