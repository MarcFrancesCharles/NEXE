<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Comerc;
use Illuminate\Http\Request;

class ComercController extends Controller
{
    // Funció pública per llistar tots els comerços
    public function index()
    {
        // Retornem els comerços i fem un "with" per incloure el nom de la categoria associada
        $comercos = Comerc::with('categoria')->get();
        return response()->json($comercos);
    }
}