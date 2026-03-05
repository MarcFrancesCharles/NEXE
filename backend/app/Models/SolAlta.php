<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolAlta extends Model
{
    use HasFactory;

    protected $table = 'sol_altas';
    protected $primaryKey = 'id_solicitud';
    
    protected $fillable = [
        'id_usuari',
        'dades_fiscals',
        'estat',
    ];

    // Pertany a un USUARI 
    public function usuari()
    {
        return $this->belongsTo(Usuari::class, 'id_usuari', 'id_usuari');
    }
}