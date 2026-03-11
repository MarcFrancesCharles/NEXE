<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    use HasFactory;

    protected $table = 'ofertas';
    protected $primaryKey = 'id_oferta';
    
    protected $fillable = [
        'id_comerc',
        'titol',
        'descripcio',
        'cost_punts',
        'estat',
        'data_fi',
        'imatge'
    ];
    // Pertany a un COMERÇ 
    public function comerc()
    {
        return $this->belongsTo(Comerc::class, 'id_comerc', 'id_comerc');
    }

    // Té moltes TRANSACCIONS 
    public function transaccions()
    {
        return $this->hasMany(Transaccio::class, 'id_oferta', 'id_oferta');
    }
}