<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria'; 

    protected $fillable = [
        'nom_cat',
    ];

    // Una categoria té molts comerços
    public function comercos()
    {
        return $this->hasMany(Comerc::class, 'id_categoria', 'id_categoria');
    }
}