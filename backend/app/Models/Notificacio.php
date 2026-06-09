<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacio extends Model
{
    use HasFactory;

    protected $table = 'notificacions';
    protected $primaryKey = 'id_notificacio';

    protected $fillable = [
        'id_usuari',
        'id_comerc',
        'titol',
        'missatge',
        'icona',
        'categoria',
        'llegida'
    ];

    protected $casts = [
        'llegida' => 'boolean'
    ];

    // Pertany a un usuari
    public function usuari()
    {
        return $this->belongsTo(Usuari::class, 'id_usuari', 'id_usuari');
    }

    // Pertany a un comerc
    public function comerc()
    {
        return $this->belongsTo(Comerc::class, 'id_comerc', 'id_comerc');
    }
}
