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
        'latitud',    
        'longitud',
        'descripcio',
        'telefon',
        'email_contacte',
        'enllac_web',
        'instagram',
        'imatge_url',
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

    /**
     * Accessor de Laravel: Intercepta quan qualsevol lloc de l'API demana el "email_contacte"
     */
    public function getEmailContacteAttribute($value)
    {
        // 1. Si el camp físic a la base de dades NO està buit, el retornem normalment
        if (!empty($value)) {
            return $value;
        }
        
        // 2. Si està buit, naveguem per la relació i agafem el correu original del registre de l'usuari
        if ($this->usuari) {
            return $this->usuari->correu;
        }

        return null;
    }
}