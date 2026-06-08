<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudTreball extends Model
{
    protected $table = 'solicituds_treball';

    protected $fillable = [
        'nom',
        'correu',
        'posicio',
        'missatge',
        'cv_path',
    ];
}
