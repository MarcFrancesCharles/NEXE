<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contacte extends Model
{
    protected $table = 'contactes';
    
    protected $fillable = [
        'nom',
        'email',
        'assumpte',
        'missatge',
        'estat',
    ];
}
