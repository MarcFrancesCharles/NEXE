<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiquetValidat extends Model
{
    use HasFactory;

    protected $table = 'tiquet_validats';
    protected $primaryKey = 'id_tiquet'; 

    protected $fillable = [
        'codi_qr',
        'import_compra',
        'data_emissio',
    ];

    // Té una TRANSACCIO associada
    public function transaccio()
    {
        return $this->hasOne(Transaccio::class, 'id_tiquet', 'id_tiquet');
    }
}