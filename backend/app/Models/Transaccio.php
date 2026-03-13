<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaccio extends Model
{
    use HasFactory;

    protected $table = 'transaccios';
    protected $primaryKey = 'id_transaccio'; 
    protected $fillable = [
        'id_usuari',
        'id_comerc',
        'id_oferta',
        'id_tiquet',
        'tipus',
        'punts_mov',
        'data_hora',
    ];

    // Pertany a un USUARI 
    public function usuari()
    {
        return $this->belongsTo(Usuari::class, 'id_usuari', 'id_usuari');
    }

    // Només els 'COMERC' poden veure les vendes del seu comerç via middleware
    public function comerc()
    {
        return $this->belongsTo(Comerc::class, 'id_comerc', 'id_comerc');
    }

    // Pot pertànyer a una OFERTA (Si és bescanvi)
    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'id_oferta', 'id_oferta');
    }

    // Pot pertànyer a un TIQUET (Si és acumulació) 
    public function tiquet()
    {
        return $this->belongsTo(TiquetValidat::class, 'id_tiquet', 'id_tiquet');
    }
}